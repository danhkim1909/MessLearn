<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignalEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public string $action;
    public int $senderId;
    public string $senderName;
    public ?string $senderAvatar;
    public ?int $targetUserId;
    public ?string $roomCode;
    public string $callType;
    public ?array $payload;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $conversationId,
        string $action,
        int $senderId,
        string $senderName,
        ?string $senderAvatar = null,
        ?int $targetUserId = null,
        ?string $roomCode = null,
        string $callType = 'video',
        ?array $payload = null
    ) {
        $this->conversationId = $conversationId;
        $this->action = $action;
        $this->senderId = $senderId;
        $this->senderName = $senderName;
        $this->senderAvatar = $senderAvatar;
        $this->targetUserId = $targetUserId;
        $this->roomCode = $roomCode;
        $this->callType = $callType;
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CallSignalEvent';
    }
}
