<?php

namespace mint\modules\itemActionScripts;

use mint\DbRepository\ItemTypes;

// init
$moduleName = basename(__DIR__);

\mint\loadModuleLanguageFile($moduleName, $moduleName);

$itemActionScripts = [];

// autoload
foreach (glob(__DIR__ . '/scripts/*.php') as $path) {
    $itemActionScripts = array_merge($itemActionScripts, include $path);
}

// register Item Termination Points, Item Actions and create handlers basing on provided scripts
foreach ($itemActionScripts as $itemActionScript) {
    $terminationPointName = 'action.' . $itemActionScript['name'];

    $handler = function (array $action, array $actionItemsDetails) use ($itemActionScript, $terminationPointName): array {
        global $db;

        $result = true;

        if (!empty($itemActionScript['itemTypeNamesToRemove'])) {
            $itemTypeNamesToRemove = $itemActionScript['itemTypeNamesToRemove'];

            foreach ($actionItemsDetails as $actionItemDetails) {
                $key = array_search($actionItemDetails['item_type_name'], $itemTypeNamesToRemove);

                if ($key !== false) {
                    unset($itemTypeNamesToRemove[$key]);

                    $result &= \mint\removeItemsWithTerminationPoint(
                        $actionItemDetails['item_ownership_id'],
                        1,
                        $terminationPointName,
                        false
                    );
                }
            }
        }

        $itemTypeNamesToCreate = [];

        if (!empty($itemActionScript['itemTypeNamesToCreate'])) {
            $itemTypeNamesToCreate = $itemActionScript['itemTypeNamesToCreate'];
        }

        if (!empty($itemActionScript['itemTypeNamesToCreateWithProbability'])) {
            $minItems = $itemActionScript['itemsToCreateMin'] ?? 0;
            $maxItems = $itemActionScript['itemsToCreateMax'] ?? 1;

            $probabilitiesSum = array_sum($itemActionScript['itemTypeNamesToCreateWithProbability']);

            for ($i = 1; $i <= $maxItems; $i++) {
                if ($probabilitiesSum < 1 && $i <= $minItems) {
                    $randomMax = $probabilitiesSum * 100;
                } else {
                    $randomMax = 100;
                }

                $randomNumber = \my_rand(0, $randomMax) / 100;

                $progressiveSum = 0;

                foreach ($itemActionScript['itemTypeNamesToCreateWithProbability'] as $name => $probability) {
                    $progressiveSum += $probability;

                    if ($randomNumber <= $progressiveSum) {
                        $itemTypeNamesToCreate[] = $name;

                        break;
                    }
                }
            }
        }

        $createdItemTypeIds = [];

        if ($itemTypeNamesToCreate) {
            $itemTypeIdsByName = array_column(
                ItemTypes::with($db)->getByColumn('name', $itemTypeNamesToCreate),
                'id',
                'name'
            );

            foreach ($itemTypeNamesToCreate as $itemTypeName) {
                if (isset($itemTypeIdsByName[$itemTypeName])) {
                    $createdItemTypeIds[] = $itemTypeIdsByName[$itemTypeName];

                    $result &= \mint\createItemsWithTerminationPoint(
                        $itemTypeIdsByName[$itemTypeName],
                        1,
                        $action['user_id'],
                        $terminationPointName,
                        false
                    );
                }
            }
        }

        if (!empty($itemActionScript['userBalanceOperation'])) {
            \mint\userBalanceOperationWithTerminationPoint($action['user_id'], (int)$itemActionScript['userBalanceOperation'], $terminationPointName);
        }

        if (isset($itemActionScript['handler']) && is_callable($itemActionScript['handler'])) {
            $result &= $itemActionScript['handler']($action, $actionItemsDetails, $terminationPointName);
        }

        return [
            'success' => $result,
            'createdItemTypeIds' => $createdItemTypeIds,
        ];
    };

    \mint\registerItemAction($itemActionScript['name'], $itemActionScript['acceptedItemTypeNames'], $handler, [
        'module' => $moduleName,
    ]);

    \mint\registerItemTerminationPoints([
        $terminationPointName,
    ]);

    if (!empty($itemActionScript['registerCurrencyTerminationPoint'])) {
        \mint\registerCurrencyTerminationPoints([
            $terminationPointName,
        ]);
    }
}
