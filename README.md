# Mint/itemActionScripts

A [Mint](https://github.com/kawaiiforums/mybb-mint) module registering Item Termination Points, Item Actions and creating handlers to support Item Action scenarios related to Item creation and removal using Item Type names.

Custom scenarios can be defined as `$itemActionScripts` sub-arrays in `module.php`.

Each scenario array contains:
- the action identifier (`string $name`),
- name of required Item Type (`string[] $acceptedItemTypeNames`; currently single Item actions are supported),
- optionally: names of used Item Types to remove (consume) on Action execution (`string[] $itemTypeNamesToRemove`),
- optionally: names of Item Types to create and assign to user on Action execution (`string[] $itemTypeNamesToCreate`).

The included example allows executing an `eat` action on items `pie` and `half-pie`: the former is swapped for the latter, which then can be removed completely:
```php
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
```

As Item Actions are identified by their signature consisting of action name and names of accepted Item Types, it's possible to create actions with the same name for multiple different item sets.

Actions involving nonexistent Item Type names may be rejected.

Action names can be localized by adding action names to `languages/*/itemActionScripts.lang.php` file:
```php
$l['mint_item_action_eat'] = 'Eat';
```
