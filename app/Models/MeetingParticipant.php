<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'status',
        'is_muted_audio',
        'is_muted_video',
        'is_sharing_screen',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'is_muted_audio' => 'boolean',
            'is_muted_video' => 'boolean',
            'is_sharing_screen' => 'boolean',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
