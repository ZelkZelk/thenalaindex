<?php

$config = [];

/* 
 * Configura el ReactComponent SimpleAbmTable, el cual representa un ABM
 * de un solo campo con interacciones AJAX segun API configurada.
 */

        
/* NotificationsEmail */
        
$abm01 = [];
$abm01['field'] = 'email';
$abm01['fieldIcon'] = 'fa fa-envelope';
$abm01['fieldLabel'] = 'Lista de  E-mails';
$abm01['emptyText'] = 'No hay E-mails configurados en esta Lista';
$abm01['model'] = 'Notification';
$abm01['feedApi'] = Router::url([ 'controller' => 'api','action' => 'feed', 'ctrl' => 'notifications' , 'actn' => 'email' ],true);
$abm01['pushApi'] = Router::url([ 'controller' => 'api','action' => 'push', 'ctrl' => 'notifications' , 'actn' => 'email' ],true);
$abm01['editApi'] = Router::url([ 'controller' => 'api','action' => 'edit', 'ctrl' => 'notifications' , 'actn' => 'email' ],true);
$abm01['dropApi'] = Router::url([ 'controller' => 'api','action' => 'drop', 'ctrl' => 'notifications' , 'actn' => 'email' ],true);

// LOAD

$config['ReactApi']['notifications']['email'] = $abm01;
