<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'last_read_at',
        'is_pinned',
        'nickname',
        'muted_until',
        'last_read_message_id',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'is_pinned' => 'boolean',
            'muted_until' => 'datetime',
            'last_read_message_id' => 'integer',
        ];
    }

    // Helper methods
    public function isMuted(): bool
    {
        return !is_null($this->muted_until) && $this->muted_until->isFuture();
    }

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
