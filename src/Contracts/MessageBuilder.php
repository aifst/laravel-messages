<?php

namespace Aifst\Messages\Contracts;

use Aifst\Messages\Support\MessageMembersCollection;

interface MessageBuilder
{
    public function reset(?MessageModel $message = null): MessageBuilder;

    public function setMain(int $main_id): MessageBuilder;

    public function setSubject(string $subject): MessageBuilder;

    public function setMessage(string $message): MessageBuilder;

    public function setOwner(MessageOwner $owner): MessageBuilder;

    public function setFromMembers(MessageMembersCollection $members): MessageBuilder;

    public function setToMembers(MessageMembersCollection $members): MessageBuilder;

    public function setReadMembers(MessageMembersCollection $members): MessageBuilder;

    public function setData(array $data): MessageBuilder;

    public function getMessage(): MessageModel;
}
