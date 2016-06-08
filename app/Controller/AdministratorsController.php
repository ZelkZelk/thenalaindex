<?php

App::uses('BackendAppController', 'Controller');

class AdministratorsController extends BackendAppController {
    private static $addButtonString = 'Agregar Administrador';
    private static $addFailString = 'Error al agregar Administrador';
    private static $addInvalidString = 'Error en los datos proporcionados';
    private static $addOkString = 'Administrador agregado correctamente';
    private static $editButtonString = 'Editar Administrador';
    private static $editFailString = 'Error al editar Administrador';
    private static $editInvalidString = 'Error en los datos proporcionados';
    private static $editOkString = 'Administrador editado correctamente';
    private static $deleteButtonString = 'Eliminar Administrador';
    private static $deleteFailString = 'Error al eliminar Administrador';
    private static $deleteOkString = 'Administrador eliminado correctamente';
    private static $deleteWarningString = '<b>ATENCION</b> Esta accion es irreversible';
    private static $emptyString = 'No hay Administradores';
    private static $detailAction = 'detail';
    private static $showAction = 'show';

    public $uses = array('Administrator');
    public $components = [ 'React', 'Login','Session', 'Scaffold' ];
    public $helpers = array('Scaffold');
    
    /* Controladora de la funcion de creacion de Administradores. */
    
    public function add(){
        $this->Scaffold->setModel($this->Administrator);
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
    
    /* Controladora de la funcion de edicion de Administradores. */
    
    public function edit(){        
        $this->Scaffold->setModel($this->Administrator);
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
    
    /* Controladora de la funcion de eliminacion de Administradores. */
    
    public function delete(){        
        $this->Scaffold->setModel($this->Administrator);
        $this->Scaffold->setIcon($this->Conf['icon']);
        $this->Scaffold->setButton(self::$deleteButtonString);
        
        switch($this->Scaffold->delete()){
            case SCAFFOLD_NO_INPUT:
                $this->warning(self::$deleteWarningString);
                break;
            case SCAFFOLD_FAIL:
                $this->fail(self::$deleteFailString);
                break;
            case SCAFFOLD_OK:
                $this->done(self::$deleteOkString);
                $this->gotoList();
        }
        
        $this->Scaffold->render();        
    }
    
    /* Controladora que crea una lista paginada de los Administradores. */
    
    public function show(){
        $this->Scaffold->setModel($this->Administrator);
        $this->Scaffold->setEmptyMessage(self::$emptyString);
        $this->Scaffold->show();
        $this->Scaffold->render();
    }
    
    /* Controladora que permite desplegar los detalles del Administrador. Adicionalmente
     * muestra links a otras acciones disponibles. */
    
    public function detail(){
        $this->Scaffold->setModel($this->Administrator);
        $this->Scaffold->detail();
        $this->Scaffold->render();
    }
    
    /* Redirecciona al detalle del administrador */
    
    private function gotoDetail(){
        $ctrl = $this->params['controller'];
        $actn = self::$detailAction;
        
        $url = $this->Administrator->getUrl($ctrl,$actn);
        $this->redirect($url);
    }
    
    /* Redirecciona a la lista de administradores */
    
    private function gotoList(){
        $ctrl = $this->params['controller'];
        $actn = self::$showAction;
        
        $url = $this->Administrator->getUrl($ctrl,$actn);
        $this->redirect($url);
    }
}