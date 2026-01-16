<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'type',
        'color',
        'description',
        'user_id',
        'is_bill',
    ];

    protected $casts = [
        'is_bill' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
