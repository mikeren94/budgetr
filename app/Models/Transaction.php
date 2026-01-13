<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'date',
        'category_id',
        'description',
        'user_id',
        'recurring_rule_id',
        'coverage_end_date',
        'paid',
        'paid_at'
    ];

    protected $casts = [
        'date' => 'date',
        'coverage_end_date' => 'date',
        'paid_at' => 'datetime',
        'paid' => 'boolean'
    ];
        
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function scopeForMonth($query, $month)
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = Carbon::parse($month . '-01')->endOfMonth();

        return $query->whereBetween('date', [$start, $end]);
    }

    public function recurringRule()
    {
        return $this->belongsTo(RecurringRule::class);
    }

    public function isIncome()
    {
        return $this->category && $this->category->type === 'income';
    }
}
