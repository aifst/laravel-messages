<?php

namespace Aifst\Messages\Contracts;

use Aifst\Messages\Contracts\MessageBuilder as MessageBuilderContract;
use Aifst\Messages\Support\MessageMembersCollection;
use \Aifst\Messages\Contracts\MessageMember;
use Aifst\Messages\Contracts\MessageModelContract;

interface MessageBuilder
{
    public function reset(?MessageContract $message = null): MessageBuilder;

    public function setGroup(int $group_id): MessageBuilder;

    public function setMain(int $main_id): MessageBuilder;

    public function setReply(int $reply_message_id): MessageBuilder;

    public function setIsMain(bool $is_main): MessageBuilder;

    public function setSubject(string $subject): MessageBuilder;

    public function setMessage(string $message): MessageBuilder;

    public function setOwner(MessageOwner $owner): MessageBuilder;

    public function setFromMembers(MessageMember $member): MessageBuilder;

    public function setToMembers(MessageMembersCollection $members): MessageBuilder;

    public function setInitiatorModel(MessageModelContract   $model): MessageBuilder;

    public function setData(array $data): MessageBuilder;

    public function getMessage(): MessageContract;
}
