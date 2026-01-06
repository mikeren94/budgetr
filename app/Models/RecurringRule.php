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
        'months' => 'array'
    ];

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

        // Move forward month-by-month until we hit an allowed month
        $next = $date->copy()->addMonth();

        while (!in_array($next->month, $allowedMonths)) {
            $next->addMonth();
        }

        return $next;
    }

    public function generateTransaction()
    {
        // If the rule is inactive, do nothing
        if(!$this->active) {
            return null;
        }

        $next = Carbon::parse($this->next_occurrence);

        // If the next occurrence is in the future, do nothing
        if ($next->isAfter(Carbon::now())) {
            return null;
        }

        // Create the transaction
        $transaction = $this->transactions()->create([
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'date' => $this->next_occurrence,
            'recurring_rule_id' => $this->id
        ]);

        // Advance the rule
        $this->update([
            'next_occurrence' => $this->calculateNextOccurrence()->toDateString()
        ]);

        return $transaction;
    }
}
