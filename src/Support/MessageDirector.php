<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Contracts\MessageModel;
use Aifst\Messages\Contracts\MessageOwner;
use Aifst\Messages\Events\CreatedMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use \Aifst\Messages\Contracts\MessageBuilder as MessageBuilderInterface;
use Illuminate\Support\Facades\Event;

/**
 * Class MessageDirector
 * @package Aifst\Messages
 */
class MessageDirector
{
    /**
     * @var MessageBuilderInterface
     */
    private $builder;

    /**
     * MessageBuilderInterface constructor.
     */
    public function __construct(?MessageBuilderInterface $builder = null)
    {
        $this->builder = $builder ?? new MessageBuilder();
    }

    public function send(
        string $subject,
        string $message,
        array $from_members,
        array $to_members,
        array $read_members = [],
        ?MessageOwner $owner = null,
        ?array $data = null
    ): MessageModel {
        $this->builder
            ->setSubject($subject)
            ->setMessage($message)
            ->setOwner($owner)
            ->setFromMembers(new MessageMembersCollection($from_members))
            ->setReadMembers(new MessageMembersCollection($read_members));

        if ($to_members) {
            $this->builder
                ->setToMembers(new MessageMembersCollection($to_members));
        }

        if ($data) {
            $this->builder->setData($data);
        }

        return $this->save($this->builder->getMessage());
    }

    /**
     * @param MessageModel $message
     * @return MessageModel
     */
    protected function save(MessageModel $message): MessageModel
    {
        DB::transaction(function () use ($message) {
            $message->save();
        });

        CreatedMessage::dispatch($message);

        return $message;
    }


}
