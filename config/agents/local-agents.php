<?php

return [
    'providers' => [
        'default' => 'llm-speak',
        'drivers' => [],
        'add-ons' => []
    ],
    'tool-management' => [
        'default' => 'superconductor',
        'drivers' => [],
        'add-ons' => []
    ],
    'chat-history' => [
        'default' => 'cached',
        'history_class' => \Agents\GoodBuddy\ChatHistory\SessionChat::class,
        'drivers' => [],
        'add-ons' => []
    ]
];
