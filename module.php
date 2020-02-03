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

    $handler = function (array $action, array $actionItemsDetails) use ($itemActionScript, $terminationPointName): bool {
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
            $minItems = $itemActionScript['itemsToCreateMin'];
            $maxItems = $itemActionScript['itemsToCreateMax'];

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

        $itemTypeIdsByName = array_column(
            ItemTypes::with($db)->getByColumn('name', $itemTypeNamesToCreate),
            'id',
            'name'
        );

        foreach ($itemTypeNamesToCreate as $itemTypeName) {
            if (isset($itemTypeIdsByName[$itemTypeName])) {
                $result &= \mint\createItemsWithTerminationPoint(
                    $itemTypeIdsByName[$itemTypeName],
                    1,
                    $action['user_id'],
                    $terminationPointName,
                    false
                );
            }
        }

        return $result;
    };

    \mint\registerItemAction($itemActionScript['name'], $itemActionScript['acceptedItemTypeNames'], $handler, [
        'module' => $moduleName,
    ]);

    \mint\registerItemTerminationPoints([
        $terminationPointName,
    ]);
}
