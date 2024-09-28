<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Contracts\MessageBuilder as MessageBuilderContract;
use Aifst\Messages\Contracts\MessageCreator;
use Aifst\Messages\Contracts\MessageMember;
use Aifst\Messages\Contracts\MessageContract;
use Aifst\Messages\Contracts\MessageOwner;
use Aifst\Messages\Contracts\MessageModelContract;

/**
 * Class MessageBuilder
 * @package Aifst\Messages
 */
class MessageBuilder implements MessageBuilderContract
{
    /**
     * @var ?MessageContract
     */
    private ?MessageContract $message = null;

    /**
     * MessageBuilder constructor.
     */
    public function __construct(?MessageContract $message = null)
    {
        $this->reset($message);
    }

    /**
     * @param MessageContract|null $message
     * @return MessageBuilderContract
     */
    public function reset(?MessageContract $message = null): MessageBuilderContract
    {
        if ($message) {
            $this->message = $message;
        } else {
            $model = config('messages.models.message');
            $this->message = new $model;
        }

        return $this;
    }

    /**
     * @param MessageOwner $owner
     * @return MessageBuilderContract
     */
    public function setOwner(MessageOwner $owner): MessageBuilderContract
    {
        $this->message->owner_model_type = $owner->getMessageOwnerModelType();
        $this->message->owner_model_id = $owner->getMessageOwnerModelId();

        return $this;
    }

    /**
     * @param int $main_id
     * @return MessageBuilderContract
     */
    public function setMain(int $main_id): MessageBuilderContract
    {
        $this->message->main_id = $main_id;

        return $this;
    }

    /**
     * @param int $group_id
     * @return MessageBuilderContract
     */
    public function setGroup(int $group_id): MessageBuilderContract
    {
        $this->message->group_id = $group_id;

        return $this;
    }

    /**
     * @param int $reply_message_id
     * @return MessageBuilderContract
     */
    public function setReply(int $reply_message_id): MessageBuilderContract
    {
        $this->message->reply_id = $reply_message_id;

        return $this;
    }

    /**
     * @param bool $is_main
     * @return MessageBuilderContract
     */
    public function setIsMain(bool $is_main): MessageBuilderContract
    {
        $this->message->is_main = $is_main;

        return $this;
    }

    /**
     * @param string $subject
     * @return MessageBuilderContract
     */
    public function setSubject(string $subject): MessageBuilderContract
    {
        $this->message->subject = $subject;

        return $this;
    }

    /**
     * @param string $message
     * @return MessageBuilderContract
     */
    public function setMessage(string $message): MessageBuilderContract
    {
        $this->message->message = $message;

        return $this;
    }

    /**
     * @param MessageMember $member
     * @return MessageBuilderContract
     */
    public function setFromMembers(MessageMember $member): MessageBuilderContract
    {
        $this->message->from_model_type = $member->getMessageMemberModelType();
        $this->message->from_model_id = $member->getMessageMemberModelId();

        return $this;
    }

    /**
     * @param MessageMembersCollection|null $members
     * @return MessageBuilderContract
     */
    public function setToMembers(MessageMembersCollection $members): MessageBuilderContract {
        $this->message->assignMembers($members->all());

        return $this;
    }

    /**
     * @param MessageToModel $model
     * @return MessageBuilderContract
     */
    public function setInitiatorModel(MessageModelContract $model): MessageBuilderContract {

        $this->message::saved(function ($message) use ($model) {
            $attr = [
                'main_id' => $message->getMainId(),
                'model_type' => get_class($model),
                'model_id' => $model->id
            ];

            config('messages.models.message_model')::firstOrCreate($attr);
        });

        return $this;
    }

    /**
     * @param array $data
     * @return MessageBuilderContract
     */
    public function setData(array $data): MessageBuilderContract
    {
        $this->message->data = $data;

        return $this;
    }

    /**
     * @return MessageContract
     */
    public function getMessage(): MessageContract
    {
        $result = $this->message;
        $this->reset();

        return $result;
    }
}
