<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function sentFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    public function receivedFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['role', 'is_pinned', 'nickname', 'muted_until', 'last_read_message_id', 'last_read_at']);
    }

    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'host_id');
    }

    public function meetingParticipants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function blockedByUsers(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_id');
    }

    public function isBlocking(int $userId): bool
    {
        return $this->blockedUsers()->where('blocked_id', $userId)->exists();
    }

    public function isBlockedBy(int $userId): bool
    {
        return $this->blockedByUsers()->where('blocker_id', $userId)->exists();
    }
}
