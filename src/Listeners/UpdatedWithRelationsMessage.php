<?php

namespace Aifst\Messages\Listeners;

use Aifst\Messages\Events\CreatedWithRelationsMessage as CreatedWithRelationsMessageEvent;
use Aifst\Messages\Models\Message;
use Illuminate\Support\Facades\DB;

class UpdatedWithRelationsMessage
{
    /**
     * @param Message $model
     */
    public function handle(CreatedWithRelationsMessageEvent $event)
    {
        
    }
}
