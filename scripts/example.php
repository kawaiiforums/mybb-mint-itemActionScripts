<?php

return [
    [
        'name' => 'eat',
        'acceptedItemTypeNames' => ['pie'],
        'itemTypeNamesToRemove' => ['pie'],
        'itemTypeNamesToCreate' => ['half-pie'],
    ],
    [
        'name' => 'eat',
        'acceptedItemTypeNames' => ['half-pie'],
        'itemTypeNamesToRemove' => ['half-pie'],
    ],
    [
        'name' => 'open_chest',
        'acceptedItemTypeNames' => ['chest', 'chest_key'],
        'itemTypeNamesToRemove' => ['chest', 'chest_key'],
        'itemTypeNamesToCreateWithProbability' => [
            'rare-coin' => 0.1,
            'litter' => 0.5,
        ],
        'itemsToCreateMin' => 1,
        'itemsToCreateMax' => 3,
    ],
    [
        'name' => 'tooth_fairy',
        'acceptedItemTypeNames' => ['tooth', 'pillow'],
        'itemTypeNamesToRemove' => ['tooth'],
        'registerCurrencyTerminationPoint' => true,
        'userBalanceOperation' => 5,
    ],
    [
        'name' => 'join_wit_club',
        'acceptedItemTypeNames' => ['riddle-answer'],
        'handler' => function (array $action, array $actionItemsDetails, string $terminationPointName): bool {
            $groupId = 8;
            return join_usergroup($action['user_id'], $groupId);
        },
    ],
];
