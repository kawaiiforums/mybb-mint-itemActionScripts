# Mint/itemActionScripts

A [Mint](https://github.com/kawaiiforums/mybb-mint) module registering Item & Currency Termination Points, Item Actions and creating handlers to support Item Action scenarios related to Item creation and removal using Item Type names.

Custom scenarios can be defined through arrays returned by files located in the  `scripts/` directory:
```
<?php

return [
    [
        scenario 1 parameters...
    ],
    [
        scenario 2 parameters...
    ],
];
```

Each scenario array contains:
- `name`: the action identifier,
- `acceptedItemTypeNames`: names (`string`) of required Item Types.

Optional parameters:
- `itemTypeNamesToRemove`: names (`string`) of used Item Types to remove (consume) on Action execution,
- `itemTypeNamesToCreate`: names (`string`) of Item Types to create and assign to user on Action execution,
- - `itemTypeNamesToCreateWithProbability`: names (`string` key) of Item Types to create and assign to user on Action execution with specified probability (`float` value),
  - `itemsToCreateMin`, `itemsToCreateMax`: minimum and maximum number of Items (`int`) to create when using probability.
  - `registerCurrencyTerminationPoint`: whether or not (`bool`) to create a Current Termination Point, required for any balance operations,
  - `userBalanceOperation` (`int`) currency units to add or remove from the user's balance,
  - `handler`, a function (`callable`) to call when the action is executed:
    ```
    function (array $action, array $actionItemsDetails, string $terminationPointName): bool
    ```

As Item Actions are identified by their signature consisting of action name and names of accepted Item Types, it's possible to create actions with the same name for multiple different item sets.

Actions involving nonexistent Item Type names may be rejected.

Action names can be localized by adding action names and success messages to `languages/*/itemActionScripts.lang.php` file:
```php
$l['mint_item_action_eat'] = 'Eat';
$l['mint_item_action_eat_success'] = 'Consumed {1} items.';
```
