<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    // Relationships
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }

    public function message(): HasOne
    {
        return $this->hasOne(Message::class);
    }
}
