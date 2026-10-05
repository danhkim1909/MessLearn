<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'avatar',
    ];

    // Accessors
    public function getIsGroupAttribute(): bool
    {
        return $this->type === 'group';
    }

    public function getNameAttribute(): ?string
    {
        return $this->title;
    }

    // Relationships
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function pinnedMessages(): HasMany
    {
        return $this->hasMany(Message::class)->where('is_pinned', true);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            ConversationParticipant::class,
            'conversation_id',
            'id',
            'id',
            'user_id'
        );
    }
}
