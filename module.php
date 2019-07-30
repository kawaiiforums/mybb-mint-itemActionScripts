<?php

namespace mint\modules\itemActionScripts;

use mint\DbRepository\ItemTypes;

// init
$moduleName = basename(__DIR__);

\mint\loadModuleLanguageFile($moduleName, $moduleName);

$itemActionScripts = [
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
];

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

        if (!empty($itemActionScript['itemTypeNamesToCreate'])) {
            $itemTypeIdsByName = array_column(
                ItemTypes::with($db)->getByColumn('name', $itemActionScript['itemTypeNamesToCreate']),
                'id',
                'name'
            );

            foreach ($itemActionScript['itemTypeNamesToCreate'] as $itemTypeName) {
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
