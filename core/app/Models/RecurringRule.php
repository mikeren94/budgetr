<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RecurringRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'frequency',
        'interval',
        'months',
        'start_date',
        'next_occurrence',
        'active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_occurrence' => 'date',
        'custom_months' => 'array',
        'months' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function calculateNextOccurrence()
    {
        $date = Carbon::parse($this->next_occurrence);

        return match ($this->frequency) {
            'monthly' => $date->copy()->addMonths($this->interval),
            'yearly'  => $date->copy()->addYears($this->interval),
            'custom'  => $this->calculateNextCustomMonth($date),
            default   => $date,
        };
    }

    protected function calculateNextCustomMonth(Carbon $date)
    {
        $allowedMonths = $this->months ?? [];

        $next = $date->copy()->addMonth();

        while (!in_array($next->month, $allowedMonths)) {
            $next->addMonth();
        }

        return $next;
    }

    public function generateTransaction()
    {
        if (!$this->active) {
            return null;
        }

        $next = Carbon::parse($this->next_occurrence);

        if ($next->isAfter(Carbon::now())) {
            return null;
        }

        $transaction = new Transaction([
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'date' => $this->next_occurrence,
            'recurring_rule_id' => $this->id,
        ]);

        $this->update([
            'next_occurrence' => $this->calculateNextOccurrence()->toDateString(),
        ]);

        return $transaction;
    }

    /**
     * Check if this rule fires inside the given month.
     */
    public function occursInMonth(Carbon $month): ?Carbon
    {
        $start = $this->start_date->copy()->startOfDay();

        // If the rule starts after the month, it cannot occur
        if ($start->greaterThan($month->copy()->endOfMonth())) {
            return null;
        }

        // DAILY
        if ($this->frequency === 'daily') {
            return $month->copy()->startOfMonth();
        }

        // WEEKLY
        if ($this->frequency === 'weekly') {
            $occurrence = $start->copy();

            while ($occurrence->lessThanOrEqualTo($month->copy()->endOfMonth())) {
                if ($occurrence->isSameMonth($month)) {
                    return $occurrence;
                }
                $occurrence->addWeeks($this->interval);
            }

            return null;
        }

        // MONTHLY
        if ($this->frequency === 'monthly') {
            $occurrence = $start->copy();

            while ($occurrence->lessThanOrEqualTo($month->copy()->endOfMonth())) {
                if ($occurrence->isSameMonth($month)) {
                    return $occurrence;
                }

                // CRITICAL FIX: no overflow
                $occurrence = $occurrence->addMonthsNoOverflow($this->interval);
            }

            return null;
        }

        // YEARLY
        if ($this->frequency === 'yearly') {
            $occurrence = $start->copy();

            while ($occurrence->lessThanOrEqualTo($month->copy()->endOfMonth())) {
                if ($occurrence->isSameMonth($month)) {
                    return $occurrence;
                }
                $occurrence->addYears($this->interval);
            }

            return null;
        }

        // CUSTOM MONTHS (e.g., Jan, Apr, Jul, Oct)
        if ($this->frequency === 'custom') {
            $day = $start->day;
            $year = $month->year;

            if (!in_array($month->month, $this->custom_months)) {
                return null;
            }

            // Use no-overflow to handle 29/30/31
            return Carbon::create($year, $month->month, 1)
                ->startOfMonth()
                ->addDays($day - 1)
                ->startOfDay()
                ->addMonthsNoOverflow(0);
        }

        return null;
    }

    /**
     * Create a virtual (unsaved) transaction for forecasting.
     */
    public function generateVirtualTransaction(Carbon $date)
    {
        // Determine coverage end date based on frequency
        $coverageEnd = match ($this->frequency) {
            'monthly' => $date->copy()->addMonth()->subDay(),
            'weekly'  => $date->copy()->addWeek()->subDay(),
            'yearly'  => $date->copy()->addYear()->subDay(),
            default   => $date->copy(), // fallback
        };

        return new Transaction([
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'date' => $date->copy(),
            'coverage_end_date' => $coverageEnd,
            'recurring_rule_id' => $this->id,
            'paid' => false,
        ]);
    }

    /**
     * Return all virtual occurrences for the given month.
     */
    public function virtualOccurrencesForMonth(Carbon $month)
    {
        $occurrence = $this->occursInMonth($month);

        if (!$occurrence) {
            return collect();
        }

        // Prevent duplicates
        $exists = Transaction::where('recurring_rule_id', $this->id)
            ->whereDate('date', $occurrence->toDateString())
            ->exists();

        if ($exists) {
            return collect();
        }

        return collect([
            $this->generateVirtualTransaction($occurrence)
        ]);
    }

    /**
     * Return virtual occurrences that should be included in the monthly summary.
     */
    public function virtualOccurrencesForMonthlySummary(Carbon $start, Carbon $end)
    {
        $occurrence = $this->occursInMonth($start);

        if (!$occurrence) {
            return collect();
        }

        // Prevent duplicates
        $exists = Transaction::where('recurring_rule_id', $this->id)
            ->whereDate('date', $occurrence->toDateString())
            ->exists();

        if ($exists) {
            return collect();
        }

        // NEW: Check if a real transaction already covers this month
        $realCovering = Transaction::where('recurring_rule_id', $this->id)
            ->whereHas('category', fn($c) => $c->where('type', 'income'))
            ->whereDate('date', '<=', $end)
            ->whereDate('coverage_end_date', '>=', $start)
            ->exists();

        if ($realCovering) {
            // A real transaction already covers this month → skip virtual
            return collect();
        }

        // Generate virtual
        $virtual = $this->generateVirtualTransaction($occurrence);
        $virtual->setRelation('category', $this->category);

        // Apply summary logic
        if ($this->category->type === 'income') {
            $include = $virtual->date->lte($end)
                && ($virtual->coverage_end_date?->gte($start) ?? true);
        } else {
            $include = $virtual->date->between($start, $end);
        }

        return $include ? collect([$virtual]) : collect();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getCarbonUnitAttribute()
    {
        return match ($this->frequency) {
            'monthly' => 'month',
            'weekly' => 'week',
            'biweekly' => 'week',
            default => $this->frequency,
        };
    }

    public function initializeNextOccurrence()
    {
        $next = Carbon::parse($this->start_date);

        // Move forward until next occurrence is in the future
        while ($next->isPast()) {
            $next = match ($this->frequency) {
                'monthly' => $next->copy()->addMonths($this->interval),
                'yearly'  => $next->copy()->addYears($this->interval),
                'custom'  => $this->calculateNextCustomMonth($next),
                default   => $next,
            };
        }

        $this->next_occurrence = $next->toDateString();
    }
}
