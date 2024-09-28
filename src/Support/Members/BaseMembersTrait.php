<?php

namespace Aifst\Messages\Support\Members;

use Aifst\Messages\Contracts\MessageContract;
use Aifst\Messages\Support\MessageManager;

trait BaseMembersTrait
{
    /**
     * @param MessageContract $message
     * @throws \Exception
     */
    public function getMessageMembers(MessageContract $message)
    {
        if (!($groupHandler = MessageManager::getInstance()::getMessageGroupHandler($event->message))) {
            throw new \Exception('Message group handler not found.');
        }
        
        return $groupHandler->getMessageMembers($event->message);
    }
}
