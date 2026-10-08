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
        'description',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
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

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }
        return asset('storage/' . $this->avatar);
    }

    // Permission Helpers
    public function isReadOnly(): bool
    {
        return (bool)($this->settings['read_only'] ?? false);
    }

    public function canMemberStartCall(): bool
    {
        return (bool)($this->settings['allow_member_start_call'] ?? true);
    }

    public function canMemberInvite(): bool
    {
        return (bool)($this->settings['allow_member_invite'] ?? true);
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

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
