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
        'start_date'       => 'date',
        'next_occurrence'  => 'date',
        'custom_months'    => 'array',
        'months'           => 'array',
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

    /**
     * Calculate the next occurrence date based on frequency and interval.
     */
    public function calculateNextOccurrence()
    {
        $date = Carbon::parse($this->next_occurrence);

        return match ($this->frequency) {
            'daily'   => $date->copy()->addDays($this->interval),
            'weekly'  => $date->copy()->addWeeks($this->interval),
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

    /**
     * Generate a real transaction if the rule is due.
     */
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
            'user_id'           => $this->user_id,
            'category_id'       => $this->category_id,
            'amount'            => $this->amount,
            'date'              => $this->next_occurrence,
            'recurring_rule_id' => $this->id,
        ]);

        $this->update([
            'next_occurrence' => $this->calculateNextOccurrence()->toDateString(),
        ]);

        return $transaction;
    }

    /**
     * Determine if the rule produces an occurrence within the given month.
     */
    public function occursInMonth(Carbon $month): ?Carbon
    {
        $start = $this->start_date->copy()->startOfDay();

        if ($start->greaterThan($month->copy()->endOfMonth())) {
            return null;
        }

        if ($this->frequency === 'daily') {
            return $month->copy()->startOfMonth();
        }

        if ($this->frequency === 'weekly') {
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth   = $month->copy()->endOfMonth();

            if ($start->greaterThan($endOfMonth)) {
                return null;
            }

            if ($start->isSameMonth($month)) {
                return $start;
            }

            $daysBetween = $start->diffInDays($startOfMonth, false);
            $weeksBetween = floor($daysBetween / 7);
            $steps = max(0, ceil($weeksBetween / $this->interval));

            $occurrence = $start->copy()->addWeeks($steps * $this->interval);

            return $occurrence->isSameMonth($month) ? $occurrence : null;
        }

        if ($this->frequency === 'monthly') {
            $occurrence = $start->copy();

            while ($occurrence->lessThanOrEqualTo($month->copy()->endOfMonth())) {
                if ($occurrence->isSameMonth($month)) {
                    return $occurrence;
                }

                $occurrence = $occurrence->addMonthsNoOverflow($this->interval);
            }

            return null;
        }

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

        if ($this->frequency === 'custom') {
            if (!in_array($month->month, $this->custom_months ?? [])) {
                return null;
            }

            $day  = $start->day;
            $year = $month->year;

            return Carbon::create($year, $month->month, 1)
                ->startOfMonth()
                ->addDays($day - 1)
                ->startOfDay();
        }

        return null;
    }

    /**
     * Create a virtual (forecasted) transaction.
     */
    public function generateVirtualTransaction(Carbon $date)
    {
        $coverageEnd = match ($this->frequency) {
            'daily'   => $date->copy(),
            'weekly'  => $date->copy()->addWeek()->subDay(),
            'monthly' => $date->copy()->addMonth()->subDay(),
            'yearly'  => $date->copy()->addYear()->subDay(),
            default   => $date->copy(),
        };

        return new Transaction([
            'user_id'            => $this->user_id,
            'category_id'        => $this->category_id,
            'amount'             => $this->amount,
            'date'               => $date->copy(),
            'coverage_end_date'  => $coverageEnd,
            'recurring_rule_id'  => $this->id,
            'paid'               => false,
        ]);
    }

    public function virtualOccurrencesForMonth(Carbon $month)
    {
        $occurrence = $this->occursInMonth($month);

        if (!$occurrence) {
            return collect();
        }

        $exists = Transaction::where('recurring_rule_id', $this->id)
            ->whereDate('date', $occurrence->toDateString())
            ->exists();

        if ($exists) {
            return collect();
        }

        return collect([$this->generateVirtualTransaction($occurrence)]);
    }

    public function virtualOccurrencesForMonthlySummary(Carbon $start, Carbon $end)
    {
        $occurrence = $this->occursInMonth($start);

        if (!$occurrence) {
            return collect();
        }

        $exists = Transaction::where('recurring_rule_id', $this->id)
            ->whereDate('date', $occurrence->toDateString())
            ->exists();

        if ($exists) {
            return collect();
        }

        $realCovering = Transaction::where('recurring_rule_id', $this->id)
            ->whereHas('category', fn ($c) => $c->where('type', 'income'))
            ->whereDate('date', '<=', $end)
            ->whereDate('coverage_end_date', '>=', $start)
            ->exists();

        if ($realCovering) {
            return collect();
        }

        $virtual = $this->generateVirtualTransaction($occurrence);
        $virtual->setRelation('category', $this->category);

        $include = $this->category->type === 'income'
            ? $virtual->date->lte($end) && ($virtual->coverage_end_date?->gte($start) ?? true)
            : $virtual->date->between($start, $end);

        return $include ? collect([$virtual]) : collect();
    }

    /**
     * Return the Carbon unit name for the rule's frequency.
     */
    public function getCarbonUnitAttribute()
    {
        return match ($this->frequency) {
            'daily'   => 'day',
            'weekly'  => 'week',
            'monthly' => 'month',
            'yearly'  => 'year',
            default   => $this->frequency,
        };
    }

    /**
     * Initialize the next occurrence based on the start date.
     */
    public function initializeNextOccurrence()
    {
        $next = Carbon::parse($this->start_date);

        while ($next->isPast()) {
            $next = match ($this->frequency) {
                'daily'   => $next->copy()->addDays($this->interval),
                'weekly'  => $next->copy()->addWeeks($this->interval),
                'monthly' => $next->copy()->addMonths($this->interval),
                'yearly'  => $next->copy()->addYears($this->interval),
                'custom'  => $this->calculateNextCustomMonth($next),
                default   => $next,
            };
        }

        $this->next_occurrence = $next->toDateString();
    }
}