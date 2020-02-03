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
        'itemTypesToCreate' => [
            'rare_coin' => 0.1,
            'litter' => 0.5,
        ],
        'itemsToCreateMin' => 1,
        'itemsToCreateMax' => 3,
    ],
];
