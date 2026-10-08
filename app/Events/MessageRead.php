<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $userId;
    public $userName;
    public $userAvatar;
    public $lastReadMessageId;
    public $lastReadAt;

    /**
     * Khoi tao su kien danh dau da xem tin nhan
     */
    public function __construct(int $conversationId, int $userId, string $userName, ?string $userAvatar, int $lastReadMessageId, string $lastReadAt)
    {
        $this->conversationId = $conversationId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->userAvatar = $userAvatar;
        $this->lastReadMessageId = $lastReadMessageId;
        $this->lastReadAt = $lastReadAt;
    }

    /**
     * Kenh phat song su kien
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    /**
     * Ten su kien phat tren kenh
     */
    public function broadcastAs(): string
    {
        return 'MessageRead';
    }
}
