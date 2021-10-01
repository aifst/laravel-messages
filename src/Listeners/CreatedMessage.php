<?php

namespace Aifst\Messages\Listeners;

use Aifst\Messages\Events\CreatedMessage as CreatedMessageEvent;
use Illuminate\Support\Facades\DB;

class CreatedMessage
{
    public function handle(CreatedMessageEvent $createdEvent)
    {
        $message_id = $createdEvent->message->id;
        $main_id = $createdEvent->message->main_id ?? $message_id;
        $message_class = config('messages.models.message');
        $message_statistic_class = config('messages.models.message_statistic');
        $count = $message_class::whereInThread($main_id)->count();
        $member_statistic_class = config('messages.models.message_member_statistic');
        $attributes = ['main_id' => $main_id];
        $message_statistic_class::updateOrCreate($attributes, $attributes + [
                'count' => $count,
                'last_id' => $message_id,
                'last_at' => $createdEvent->message->created_at,
            ]);
        foreach ($createdEvent->message->members as $member) {
            $countData = (new $message_class)
                ->whereMemberModel($member->model_type, $member->model_id)
                ->joinReadMembers($member->model_type, $member->model_id)
                ->select(DB::Raw('count(*) as count, count(' . config('messages.table_names.message_read_members') . '.message_id) as count_read'))
                ->first()
                ->toArray();

            $attributes = [
                'model_type' => $member->model_type,
                'model_id' => $member->model_id,
            ];
            $member_statistic_class::updateOrCreate($attributes, $attributes + $countData);
        }
    }
}
