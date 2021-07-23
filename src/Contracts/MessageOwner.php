<?php

namespace Aifst\Messages\Contracts;

interface MessageOwner
{
    public function getMessageOwnerModelType(): string;
    public function getMessageOwnerModelId(): int;
}
