<?php

namespace Aifst\Messages\Contracts;

interface MessageCreator extends MessageMember
{
    public function getMessageCreatorModelType(): string;
    public function getMessageCreatorModelId(): int;
}
