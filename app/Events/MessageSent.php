<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel; // تأكد من استيراد Channel
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        // تم تغيير PrivateChannel إلى Channel (عام) للتمكن من الاختبار
        return new Channel('chat.' . $this->message->receiver_id);
    }
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
