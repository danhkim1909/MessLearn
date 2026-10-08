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
    public ?array $targetUserIds;
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
        ?array $payload = null,
        ?array $targetUserIds = null
    ) {
        $this->conversationId = $conversationId;
        $this->action = $action;
        $this->senderId = $senderId;
        $this->senderName = $senderName;
        $this->senderAvatar = $senderAvatar;
        $this->targetUserId = $targetUserId;
        $this->targetUserIds = $targetUserIds;
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
        // 1. Tin hieu gui dich danh cho 1 nguoi dung cu the (WebRTC Offer, Answer, ICE, Host mute)
        if ($this->targetUserId) {
            return [
                new PrivateChannel('App.Models.User.' . $this->targetUserId),
            ];
        }

        // 2. Tin hieu do chuong cuoc goi den (incoming_call): Phat toi kenh ca nhan tung thanh vien
        if ($this->action === 'incoming_call' && !empty($this->targetUserIds)) {
            $channels = [];
            foreach ($this->targetUserIds as $uid) {
                if ($uid && (int)$uid !== $this->senderId) {
                    $channels[] = new PrivateChannel('App.Models.User.' . (int)$uid);
                }
            }
            return !empty($channels) ? $channels : [new PrivateChannel('conversation.' . $this->conversationId)];
        }

        // 3. Cac tin hieu chung trong phong hop: Phat tren kenh phong hoi thoai
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CallSignalEvent';
    }
}
