<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];

        if ($this->message->conversation) {
            $otherParticipantIds = $this->message->conversation
                ->participants()
                ->where('user_id', '!=', $this->message->user_id)
                ->pluck('user_id');

            foreach ($otherParticipantIds as $uId) {
                $channels[] = new PrivateChannel('App.Models.User.' . $uId);
            }
        }

        return $channels;
    }


    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
