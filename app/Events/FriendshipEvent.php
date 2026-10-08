<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendshipEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $receiverId;
    public string $action;
    public array $sender;
    public ?int $friendshipId;
    public ?int $conversationId;
    public ?string $message;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $receiverId,
        string $action,
        array $sender,
        ?int $friendshipId = null,
        ?int $conversationId = null,
        ?string $message = null
    ) {
        $this->receiverId = $receiverId;
        $this->action = $action;
        $this->sender = $sender;
        $this->friendshipId = $friendshipId;
        $this->conversationId = $conversationId;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'FriendshipEvent';
    }
}
