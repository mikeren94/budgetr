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
        'months' => 'array',
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
        if (!$this->active) {
            return null;
        }

        $start = Carbon::parse($this->start_date);
        $targetStart = $month->copy()->startOfMonth();
        $targetEnd = $month->copy()->endOfMonth();

        // If the rule starts after the month, skip
        if ($start->isAfter($targetEnd)) {
            return null;
        }

        $occurrence = $start->copy();

        // Advance until we reach the target month
        while ($occurrence->isBefore($targetStart)) {
            $occurrence = match ($this->frequency) {
                'monthly' => $occurrence->copy()->addMonths($this->interval),
                'yearly'  => $occurrence->copy()->addYears($this->interval),
                'custom'  => $this->calculateNextCustomMonth($occurrence),
                default   => $occurrence,
            };
        }

        return $occurrence->between($targetStart, $targetEnd)
            ? $occurrence
            : null;
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
