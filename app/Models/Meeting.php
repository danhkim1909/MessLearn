<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'host_id',
        'room_code',
        'type',
        'status',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    // --- Accessors ---

    public function getDurationSecondsAttribute(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        $endTime = $this->ended_at ?? now();
        return max(0, $this->started_at->diffInSeconds($endTime));
    }

    public function getDurationFormattedAttribute(): string
    {
        $seconds = $this->duration_seconds;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}
