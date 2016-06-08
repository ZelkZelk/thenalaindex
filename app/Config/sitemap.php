<?php

$config = [];

/* 
 * Metadata para cada una de las URL disponibles en el Backend.
 * Es compone de una matriz de 2 dimensiones, cuyo primer indice corresponde al
 * nombre del controlador, y el segundo indice corresponde al nombre de la accion.
 * 
 * Campos disponibles.
 * 
 * title (string) : el titulo de la pagina
 * private (boolean) : determina si la accion requiere login
 */

$config['backend']['index'] = [
    'title' => 'The Nala Index',
    'private' => true
];

$config['backend']['login'] = [
    'title' => 'The Nala Index',
    'private' => false
];

$config['backend']['logout'] = [
    'title' => 'Cerrar Sesion',
    'icon' => 'lock',
    'private' => false
];

/* ABM Administradores. */

$config['administrators']['add'] = [
    'title' => 'Agregar Administrador',
    'icon' => 'plus-circle',
    'private' => true
];

$config['administrators']['show'] = [
    'title' => 'Lista de Administradores',
    'icon' => 'list',
    'private' => true,
    'routemap' => [
        'current' => 'page',
    ]
];

$config['administrators']['detail'] = [
    'title' => 'Detallar Administrador',
    'icon' => 'th-list',
    'private' => true,
    'single' => true,
    'routemap' => [
        'oid' => 'id',
        'slug' => 'user_name'
    ]
];

$config['administrators']['edit'] = [
    'title' => 'Editar Administrador',
    'icon' => 'edit',
    'private' => true,
    'single' => true,
    'routemap' => [
        'oid' => 'id',
        'slug' => 'user_name'
    ]
];

$config['administrators']['delete'] = [
    'title' => 'Eliminar Administrador',
    'icon' => 'ban',
    'private' => true,
    'single' => true,
    'routemap' => [
        'oid' => 'id',
        'slug' => 'user_name'
    ]
];


/* ABM Sitios. */

$config['targets']['add'] = [
    'title' => 'Agregar Sitio',
    'icon' => 'plus-circle',
    'private' => true
];

$config['targets']['show'] = [
    'title' => 'Lista de Sitios',
    'icon' => 'list',
    'private' => true,
    'routemap' => [
        'current' => 'page',
    ]
];

$config['targets']['archive'] = [
    'title' => 'Archivo de Sitios',
    'icon' => 'archive',
    'private' => true,
    'status' => false,
    'routemap' => [
        'current' => 'page',
    ]
];

$config['targets']['detail'] = [
    'title' => 'Detallar Sitio',
    'icon' => 'th-list',
    'private' => true,
    'single' => true,
    'routemap' => [
        'oid' => 'id',
        'slug' => 'name'
    ]
];

$config['targets']['edit'] = [
    'title' => 'Editar Sitio',
    'icon' => 'edit',
    'private' => true,
    'single' => true,
    'routemap' => [
        'oid' => 'id',
        'slug' => 'name'
    ]
];

$config['targets']['delete'] = [
    'title' => 'Archivar Sitio',
    'icon' => 'ban',
    'private' => true,
    'single' => true,
    'routemap' => [
        'oid' => 'id',
        'slug' => 'name'
    ]
];

$config['targets']['undelete'] = [
    'title' => 'Desarchivar Sitio',
    'icon' => 'undo',
    'private' => true,
    'single' => true,
    'status' => false,
    'routemap' => [
        'oid' => 'id',
        'slug' => 'name'
    ]
];

$config['notifications']['email'] = [
    'title' => 'Listas de Email',
    'icon' => 'envelope',
    'private' => true,
];

# Frontend 

$config['frontend']['index'] = [
    'title' => 'The Nala Index',
    'icon' => 'web',
    'private' => false,
];
