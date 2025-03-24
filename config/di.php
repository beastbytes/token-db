<?php

declare(strict_types=1);

use BeastBytes\Token\Db\TokenStorage;
use BeastBytes\Token\TokenStorageInterface;

/** @var array $params */

return [
    TokentorageInterface::class => [
        'class' => TokenStorage::class,
        '__construct()' => [
            'tableName' => $params['beastbytes/token']['tableName'],
        ],
    ]
];
