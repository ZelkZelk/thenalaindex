<?php

/* Armazon basico del backend */

Router::connect('/backend.html', array('controller' => 'backend', 'action' => 'index'));
Router::connect('/backend/login.html', array('controller' => 'backend', 'action' => 'login'));
Router::connect('/backend/logout.html', array('controller' => 'backend', 'action' => 'logout'));

/* ABM Administradores */

Router::connect('/administradores/agregar.html', array('controller' => 'administrators', 'action' => 'add'));
Router::connect('/administradores/:slug/:oid/detallar.html', array('controller' => 'administrators', 'action' => 'detail'), array('slug','oid'));
Router::connect('/administradores/:slug/:oid/editar.html', array('controller' => 'administrators', 'action' => 'edit'), array('slug','oid'));
Router::connect('/administradores/:slug/:oid/eliminar.html', array('controller' => 'administrators', 'action' => 'delete'), array('slug','oid'));
Router::connect('/administradores/lista.html', array('controller' => 'administrators', 'action' => 'show'));
Router::connect('/administradores/:page/lista.html', array('controller' => 'administrators', 'action' => 'show'),['page']);

/* ABM Sitios */

Router::connect('/sitios/agregar.html', array('controller' => 'targets', 'action' => 'add'));
Router::connect('/sitios/:slug/:oid/detallar.html', array('controller' => 'targets', 'action' => 'detail'), array('slug','oid'));
Router::connect('/sitios/:slug/:oid/editar.html', array('controller' => 'targets', 'action' => 'edit'), array('slug','oid'));
Router::connect('/sitios/:slug/:oid/archivar.html', array('controller' => 'targets', 'action' => 'delete'), array('slug','oid'));
Router::connect('/sitios/:slug/:oid/desarchivar.html', array('controller' => 'targets', 'action' => 'undelete'), array('slug','oid'));
Router::connect('/sitios/lista.html', array('controller' => 'targets', 'action' => 'show'));
Router::connect('/sitios/:page/lista.html', array('controller' => 'targets', 'action' => 'show'),['page']);
Router::connect('/sitios/archivados.html', array('controller' => 'targets', 'action' => 'archive'));
Router::connect('/sitios/:page/archivados.html', array('controller' => 'targets', 'action' => 'archive'),['page']);

/* ABM Sitios */

Router::connect('/notificaciones/email.html', array('controller' => 'notifications', 'action' => 'email'));

/* API para ReactComponents */

Router::connect('/api/:ctrl/:actn/feed.js', array('controller' => 'api', 'action' => 'feed'), ['ctrl','actn']);
Router::connect('/api/:ctrl/:actn/push.js', array('controller' => 'api', 'action' => 'push'), ['ctrl','actn']);
Router::connect('/api/:ctrl/:actn/edit.js', array('controller' => 'api', 'action' => 'edit'), ['ctrl','actn']);
Router::connect('/api/:ctrl/:actn/drop.js', array('controller' => 'api', 'action' => 'drop'), ['ctrl','actn']);

/* Webservices */

Router::connect('/v:version/:webservice.json', array('controller' => 'webservice', 'action' => 'index'), ['version','webservice']);

/* Rutas del Portal */

Router::connect('/', array('controller' => 'frontend', 'action' => 'index'));
Router::connect('/historico/id::id/pag::page/:target.html', array('controller' => 'frontend', 'action' => 'index','module' => 'histories'));
Router::connect('/exploracion/id::id/hash::hash/:target.html', array('controller' => 'frontend', 'action' => 'index','module' => 'exploration'));

/* Asset Server */

Router::connect('/asset/:hash', array('controller' => 'assets', 'action' => 'index'));
