<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Contracts\MessageModel;
use Aifst\Messages\Contracts\MessageOwner;
use Aifst\Messages\Events\CreatedMessage;
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
        array $from_members,
        array $read_members = [],
        ?array $data = null,
        ?string $subject = null
    ): MessageModel {
        $main = config('messages.models.message')::where('id', $message_id)
            ->whereNull('main_id')
            ->first();

        if (!$main) {
            throw new \Exception('Message for reply not found!');
        }

        $this->builder
            ->setMain($message_id)
            ->setMessage($message)
            ->setOwner(new \Aifst\Messages\Support\Reply\MessageOwner($main->owner_model_type, $main->owner_model_id))
            ->setToMembers(
                new MessageMembersCollection(
                    $main->members->map(fn($item) => new MessageMember($item->model_type, $item->model_id))->all()
                )
            )
            ->setFromMembers(new MessageMembersCollection($from_members))
            ->setReadMembers(new MessageMembersCollection($read_members));

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
