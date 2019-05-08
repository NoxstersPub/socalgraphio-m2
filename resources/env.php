<?php
return [
    'backend' => [
        'frontName' => 'admin_so8iay'
    ],
    'crypt' => [
        'key' => '30068c1fae5e8994d12d0534cbfa4e28'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'socalgraph-m2',
                'username' => 'root',
                'password' => 'mysqlroot',
                'active' => '1'
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'default',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => 'ec6_'
            ],
            'page_cache' => [
                'id_prefix' => 'ec6_'
            ]
        ]
    ]
];
