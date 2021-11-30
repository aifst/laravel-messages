<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Contracts\MessageModel;
use Aifst\Messages\Contracts\MessageOwner;
use Aifst\Messages\Support\Reply\MessageMember;
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

    /**
     * @param string $subject
     * @param string $message
     * @param array $from_members
     * @param array $to_members
     * @param array $read_members
     * @param MessageOwner|null $owner
     * @param array|null $data
     * @return MessageModel
     */
    public function send(
        string $subject,
        string $message,
        \Aifst\Messages\Contracts\MessageMember $from_member,
        array $to_members,
        array $read_members = [],
        ?MessageOwner $owner = null,
        ?array $data = null
    ): MessageModel {
        $this->builder
            ->setSubject($subject)
            ->setMessage($message)
            ->setIsMain(true)
            ->setOwner($owner)
            ->setFromMembers($from_member)
            ->setReadMembers(new MessageMembersCollection($read_members));

        if ($to_members) {
            $this->builder
                ->setToMembers(new MessageMembersCollection($to_members));
        }

        if ($data) {
            $this->builder->setData($data);
        }

        $message = $this->builder
            ->getMessage();

        $message->save();

        return $message;
    }

    /**
     * @param int $message_id
     * @param string $message
     * @param array $from_members
     * @param array $read_members
     * @param array|null $data
     * @return MessageModel
     * @throws \Exception
     */
    public function reply(
        int $message_id,
        string $message,
        \Aifst\Messages\Contracts\MessageMember $from_member,
        array $read_members = [],
        ?array $data = null,
        ?string $subject = null,
        ?int $reply_message_id = null
    ): MessageModel {
        $main = config('messages.models.message')::where('id', $message_id)
            ->whereOnlyParent()
            ->first();

        if (!$main) {
            throw new \Exception('Message for reply not found!');
        }

        $this->builder
            ->setMain($message_id)
            ->setIsMain(false)
            ->setMessage($message)
            ->setOwner(new \Aifst\Messages\Support\Reply\MessageOwner($main->owner_model_type, $main->owner_model_id))
            ->setToMembers(
                new MessageMembersCollection(
                    $main->members->map(fn($item) => new MessageMember($item->model_type, $item->model_id))->all()
                )
            )
            ->setFromMembers($from_member)
            ->setReadMembers(new MessageMembersCollection($read_members));

        if ($data) {
            $this->builder->setData($data);
        }

        if ($reply_message_id) {
            $this->builder->setReply($reply_message_id);
        }

        if ($subject) {
            $this->builder->setSubject($subject);
        }

        $message = $this->builder
            ->getMessage();

        $message->save();

        return $message;
    }
}
