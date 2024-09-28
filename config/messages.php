<?php

return [
    'owner' => Aifst\Messages\Contracts\MessageOwner::class,
    'is_set_all_messages_unread_for_added_member' => false,
    'manager' => Aifst\Messages\Support\MessageManager::class,
    'models' => [
        'message' => Aifst\Messages\Models\Message::class,
        'message_member' => Aifst\Messages\Models\MessageMember::class,
        'message_member_statistic' => Aifst\Messages\Models\MessageMemberStatistic::class,
        'message_member_message_statistic' => Aifst\Messages\Models\MessageMemberMessageStatistic::class,
        'message_model' => Aifst\Messages\Models\MessageModel::class,
        'message_statistic' => Aifst\Messages\Models\MessageStatistic::class,
        'message_group' => Aifst\Messages\Models\MessageGroup::class,
        'message_group_member' => Aifst\Messages\Models\MessageGroupMember::class,
    ],
    'table_names' => [
        'messages' => 'messages',
        'message_members' => 'message_members',
        'message_member_statistics' => 'message_member_statistics',
        'message_member_message_statistics' => 'message_member_message_statistics',
        'message_models' => 'message_models',
        'message_read_members' => 'message_read_members',
        'message_statistics' => 'message_statistics',
        'message_groups' => 'message_groups',
        'message_group_members' => 'message_group_members',
    ],
    'groups' => [
        'private' => [
            'code' => 'private',
            'handler' => \Aifst\Messages\Support\Statistic\Handlers\PrivateHandler::class
        ]
    ],
    'events' => [
        'message' => [
            'created' => Aifst\Messages\Events\CreatedMessage::class,
            'delete' => Aifst\Messages\Events\DeleteMessage::class,
            'read' => Aifst\Messages\Events\ReadMessage::class,
            'remove_member_from_message' => Aifst\Messages\Events\RemoveMemberFromMessageMessage::class,
            'add_member_to_message' => Aifst\Messages\Events\AddMemberToMessageMessage::class,
        ]
    ],
    'listeners' => [
        'message' => [
            'created' => Aifst\Messages\Listeners\CreatedMessage::class,
            'delete' => Aifst\Messages\Listeners\DeleteMessage::class,
            'read' => Aifst\Messages\Listeners\ReadMessage::class,
            'remove_member_from_message' => Aifst\Messages\Listeners\RemoveMemberFromMessageMessage::class,
            'add_member_to_message' => Aifst\Messages\Listeners\AddMemberToMessageMessage::class,
        ]
    ]
];
