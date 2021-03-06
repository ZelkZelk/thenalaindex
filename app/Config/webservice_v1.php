<?php

$config = [];
$config['Webservice.targets'] = [ 
    'get' => true, 
    'post' => false 
];

$config['Webservice.histories'] = [
    'get' => true, 
    'post' => true, 
    'data' => [ 
        'target_id' => [ 'required' => true, 'type' => 'int'  ], 
        'page' => [ 'default' => 1, 'type' => 'int', 'range' => [ 1, null ] ], 
        'limit' => [ 'default' => 5, 'type' => 'int', 'readonly' => true ]
    ],
];

$config['Webservice.exploration'] = [
    'get' => true, 
    'post' => true, 
    'data' => [ 
        'target_id' => [ 'required' => true, 'type' => 'int', 'range' => [ 1, null ]  ], 
        'hash' => [ 'required' => true, 'type' => 'string' ], 
    ],
];

$config['Webservice.search'] = [
    'get' => true, 
    'post' => true, 
    'data' => [ 
        'q' => [ 'required' => true, 'type' => 'string' ], 
        'page' => [ 'default' => 1, 'type' => 'int', 'range' => [ 1, null ] ], 
    ],
];
