<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Contracts\MessageBuilder as MessageBuilderContract;
use Aifst\Messages\Contracts\MessageCreator;
use Aifst\Messages\Contracts\MessageModel;
use Aifst\Messages\Contracts\MessageOwner;

/**
 * Class MessageBuilder
 * @package Aifst\Messages
 */
class MessageBuilder implements MessageBuilderContract
{
    /**
     * @var ?MessageModel
     */
    private ?MessageModel $message = null;

    /**
     * MessageBuilder constructor.
     */
    public function __construct(?MessageModel $message = null)
    {
        $this->reset($message);
    }

    /**
     * @param MessageModel|null $message
     * @return MessageBuilderContract
     */
    public function reset(?MessageModel $message = null): MessageBuilderContract
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

    public function setMain(int $main_id): MessageBuilderContract
    {
        $this->message->main_id = $main_id;

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
     * @param MessageMembersCollection $members
     * @return MessageBuilderContract
     */
    public function setFromMembers(MessageMembersCollection $members): MessageBuilderContract
    {
        $this->message->assignFromMembers($members->all());

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
     * @param MessageMembersCollection $members
     * @return MessageBuilderContract
     */
    public function setReadMembers(MessageMembersCollection $members): MessageBuilderContract
    {
        $this->message->assignReadMembers($members->all());

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
     * @return MessageModel
     */
    public function getMessage(): MessageModel
    {
        $result = $this->message;
        $this->reset();

        return $result;
    }
}
