<?php

namespace Aifst\Messages\Contracts;

interface MessageMember
{
    public function getMessageMemberModelType(): string;
    public function getMessageMemberModelId(): int;
}
