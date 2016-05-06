<?php

App::uses('AppController', 'Controller');

class TargetsController extends AppController {
    private static $addButtonString = 'Agregar Sitio';
    private static $addFailString = 'Error al agregar Sitio';
    private static $addInvalidString = 'Error en los datos proporcionados';
    private static $addOkString = 'Sitio agregado correctamente';
    private static $editButtonString = 'Editar Sitio';
    private static $editFailString = 'Error al editar Sitio';
    private static $editInvalidString = 'Error en los datos proporcionados';
    private static $editOkString = 'Sitio editado correctamente';
    private static $deleteButtonString = 'Archivar Sitio';
    private static $deleteFailString = 'Error al archivar Sitio';
    private static $deleteOkString = 'Sitio archivado correctamente';
    private static $undeleteButtonString = 'Desarchivar Sitio';
    private static $undeleteFailString = 'Error al desarchivar Sitio';
    private static $undeleteOkString = 'Sitio desarchivado correctamente';
    private static $emptyString = 'No hay Sitios';
    private static $emptyArchiveString = 'No hay Sitios Archivados';
    private static $detailAction = 'detail';
    private static $archiveAction = 'archive';
    private static $showAction = 'show';

    public $uses = array('Target');
    public $components = array('Scaffold');
    public $helpers = array('Scaffold');
    
    /* Controladora de la funcion de creacion de Sitios. */
    
    public function add(){
        $this->Scaffold->setModel($this->Target);
        $this->Scaffold->setInput($this->data);
        $this->Scaffold->setIcon($this->Conf['icon']);
        $this->Scaffold->setButton(self::$addButtonString);
        
        switch($this->Scaffold->add()){
            case SCAFFOLD_FAIL:
                $this->fail(self::$addFailString);
                break;
            case SCAFFOLD_NOT_VALID:
                $this->fail(self::$addInvalidString);
                break;
            case SCAFFOLD_OK:
                $this->done(self::$addOkString);
                $this->gotoDetail();
        }
        
        $this->Scaffold->render();        
    }
    
    /* Controladora de la funcion de edicion de Sitios. */
    
    public function edit(){        
        $this->Scaffold->setModel($this->Target);
        $this->Scaffold->setInput($this->data);
        $this->Scaffold->setIcon($this->Conf['icon']);
        $this->Scaffold->setButton(self::$editButtonString);
        
        switch($this->Scaffold->edit()){
            case SCAFFOLD_FAIL:
                $this->fail(self::$editFailString);
                break;
            case SCAFFOLD_NOT_VALID:
                $this->fail(self::$editInvalidString);
                break;
            case SCAFFOLD_OK:
                $this->done(self::$editOkString);
                $this->gotoDetail();
        }
        
        $this->Scaffold->render();        
    }
    
    /* Controladora de la funcion de eliminacion de Sitios. */
    
    public function delete(){        
        $this->Scaffold->setModel($this->Target);
        $this->Scaffold->setIcon($this->Conf['icon']);
        $this->Scaffold->setButton(self::$deleteButtonString);
        
        switch($this->Scaffold->delete()){
            case SCAFFOLD_FAIL:
                $this->fail(self::$deleteFailString);
                break;
            case SCAFFOLD_OK:
                $this->done(self::$deleteOkString);
                $this->gotoArchive();
        }
        
        $this->Scaffold->render();        
    }
    
    /* Controladora de la funcion de deseliminacion de Sitios. */
    
    public function undelete(){        
        $this->Scaffold->setModel($this->Target);
        $this->Scaffold->setIcon($this->Conf['icon']);
        $this->Scaffold->setButton(self::$undeleteButtonString);
        
        switch($this->Scaffold->undelete()){
            case SCAFFOLD_FAIL:
                $this->fail(self::$undeleteFailString);
                break;
            case SCAFFOLD_OK:
                $this->done(self::$undeleteOkString);
                $this->gotoDetail();
        }
        
        $this->Scaffold->render();        
    }
    
    /* Controladora que crea una lista paginada de los Sitios. */
    
    public function show(){
        $this->Scaffold->setModel($this->Target);
        $this->Scaffold->setEmptyMessage(self::$emptyString);
        $this->Scaffold->show();
        $this->Scaffold->render();
    }
    
    /* Controladora que crea una lista paginada de los Sitios Archivados. */
    
    public function archive(){
        $this->Scaffold->setModel($this->Target);
        $this->Scaffold->setEmptyMessage(self::$emptyArchiveString);
        $this->Scaffold->archive();
        $this->Scaffold->render();
    }
    
    /* Controladora que permite desplegar los detalles del Sitio. Adicionalmente
     * muestra links a otras acciones disponibles. */
    
    public function detail(){
        $this->Scaffold->setModel($this->Target);
        $this->Scaffold->detail();
        $this->Scaffold->render();
    }
    
    /* Redirecciona al detalle del sitio */
    
    private function gotoDetail(){
        $ctrl = $this->params['controller'];
        $actn = self::$detailAction;
        
        $url = $this->Target->getUrl($ctrl,$actn);
        $this->redirect($url);
    }
    
    /* Redirecciona a la lista de sitios */
    
    private function gotoList(){
        $ctrl = $this->params['controller'];
        $actn = self::$showAction;
        
        $url = $this->Target->getUrl($ctrl,$actn);
        $this->redirect($url);
    }
    
    /* Redirecciona al archivo de sitios */
    
    private function gotoArchive(){
        $ctrl = $this->params['controller'];
        $actn = self::$archiveAction;
        
        $url = $this->Target->getUrl($ctrl,$actn);
        $this->redirect($url);
    }
}