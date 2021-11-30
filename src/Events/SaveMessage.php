<?php

namespace Aifst\Messages\Events;

use Aifst\Messages\Contracts\MessageModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaveMessage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var MessageModel
     */
    public MessageModel $message;

    /**
     * SaveMessage constructor.
     * @param MessageModel $message
     */
    public function __construct(MessageModel $message)
    {
        $this->message = $message;
    }
}
