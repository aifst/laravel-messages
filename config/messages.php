<?php

return [
    'owner' => Aifst\Messages\Contracts\MessageOwner::class,
    'models' => [
        'message' => Aifst\Messages\Models\Message::class,
        'message_member' => Aifst\Messages\Models\MessageMember::class,
        'message_member_statistic' => Aifst\Messages\Models\MessageMemberStatistic::class,
        'message_member_message_statistic' => Aifst\Messages\Models\MessageMemberMessageStatistic::class,
        'message_model' => Aifst\Messages\Models\MessageModel::class,
        'message_read_member' => Aifst\Messages\Models\MessageReadMember::class,
        'message_statistic' => Aifst\Messages\Models\MessageStatistic::class,
    ],
    'table_names' => [
        'messages' => 'messages',
        'message_members' => 'message_members',
        'message_member_statistics' => 'message_member_statistics',
        'message_member_message_statistics' => 'message_member_message_statistics',
        'message_models' => 'message_models',
        'message_read_members' => 'message_read_members',
        'message_statistics' => 'message_statistics',
    ],
    'events' => [
        'message' => [
            'saved' => Aifst\Messages\Events\SaveMessage::class
        ]
    ],
    'listeners' => [
        'message' => [
            'saved' => Aifst\Messages\Listeners\SaveMessage::class
        ]
    ]
];
