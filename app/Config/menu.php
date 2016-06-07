<?php

$config = [];

/* Establece la configuracion del menu lateral, agrupado por bloques de acciones. */

/* ABM Administradores*/

$config['menu']['admin'] = [
    'icon' => 'user',
    'title' => 'Administradores'
];

$config['menu']['admin']['actions'] = [
    [ 'administrators', 'add' ],
    [ 'administrators', 'show' ],
];

$config['menu']['sites'] = [
    'icon' => 'link',
    'title' => 'Sitios de Exploracion'
];

$config['menu']['sites']['actions'] = [
    [ 'targets', 'add' ],
    [ 'targets', 'show' ],
    [ 'targets', 'archive' ],
];

$config['menu']['notif'] = [
    'icon' => 'bullhorn',
    'title' => 'Notificaciones'
];

$config['menu']['notif']['actions'] = [
    [ 'notifications', 'email' ],
];