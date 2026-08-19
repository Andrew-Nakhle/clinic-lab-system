<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $userId;
    public $title;
    public $message;
    public $type;
    public $data;

    public function __construct(
        int $userId,
        string $title,
        string $message,
        string $type,
        array $data = []
    ) {
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->data = $data;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(
            'notifications.' . $this->userId
        );
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
