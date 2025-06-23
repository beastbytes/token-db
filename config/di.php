<?php

declare(strict_types=1);

use BeastBytes\Token\Db\TokenStorage;
use BeastBytes\Token\TokenStorageInterface;

/** @var array $params */

return [
    TokentorageInterface::class => [
        'class' => TokenStorage::class,
        '__construct()' => [
            'tableName' => array_key_exists('beastbytes/token', $params)
                && array_key_exists('tableName', $params['beastbytes/token'])
                ? $params['beastbytes/token']['tableName']
                : TokenStorage::TABLE_NAME
            ,
        ],
    ]
];
