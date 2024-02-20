<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $notification;
    private $target;
    /**
     * Create a new event instance.
     */
    public function __construct($notification, $target)
    {
        $this->notification = $notification;
        $this->target = $target;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('store-notification-'.$this->target->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'store-notification';
    }

    public function broadcastWith(): array
    {
        return ['notification' => $this->notification];
    }
}
