<?php

namespace Aifst\Messages\Listeners;

use Aifst\Messages\Events\RemoveMemberFromMessageMessage as RemoveMemberFromMessageMessageEvent;
use Aifst\Messages\Models\Message;
use Illuminate\Support\Facades\DB;
use Aifst\Messages\Support\MessageStatistic;

class AddMemberToMessageMessage
{
    /**
     * @param Message $model
     */
    public function handle(RemoveMemberFromMessageMessageEvent $event)
    {
    }
}
