<?php

namespace Aifst\Messages\Events;

use Aifst\Messages\Contracts\MessageMember as MessageMemberContract;
use Aifst\Messages\Contracts\MessageContract;
use Aifst\Messages\Support\Reply\MessageMember;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddMemberToMessageMessage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var MessageContract
     */
    public MessageContract $message;
    public MessageMemberContract $member;
    public ?array $data;

    /**
     * @param MessageContract $message
     * @param MessageMember $member
     * @param array|null $data
     */
    public function __construct(MessageContract $message, MessageMemberContract $member, ?array $data = null)
    {
        $this->message = $message;
        $this->member = $member;
        $this->data = $data;
    }
}
