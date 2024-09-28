<?php

namespace Aifst\Messages\Events;

use Aifst\Messages\Contracts\MessageMember as MessageMemberContract;
use Aifst\Messages\Contracts\MessageContract;
use Aifst\Messages\Support\Reply\MessageMember;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreatedMessage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var MessageContract
     */
    public MessageContract $message;
    public ?array $data;

    /**
     * @param MessageContract $message
     * @param array|null $data
     */
    public function __construct(MessageContract $message, ?array $data = null)
    {
        $this->message = $message;
        $this->data = $data;
    }
}
