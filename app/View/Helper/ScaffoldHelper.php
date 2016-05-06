<?php

App::uses('AppHelper', 'View/Helper');

/* Helper que ayuda a renderear los distintos componentes del Scaffold. */

class ScaffoldHelper extends AppHelper{
    private $Scaffold;
    private $Model;
    private $Schema;
    private $Data;
    private $Blob;
    private $Field;
    private $Value;
    
    /* Genera la URL de la pagina. */
    
    public function pageLink($page = 1){        
        $routedParam = $this->Scaffold->getPagedParam();
        $route = $this->Scaffold->getCurrentRoute();
        $route[$routedParam] = $page;
        
        return Router::url($route, true);
    }
    
    /* Imprime los numeros de pagina disponibles de una paginacion. */
    
    public function numbers(){        
        $count = $this->Scaffold->getCount();
        $limit = $this->Scaffold->getLimit();
        $pageCount = floor($count / $limit) + 1;
        $currentPage = $this->Scaffold->getPage();
        $prev = $currentPage - 1;
        $next = $currentPage + 1;
        $colspan = count($this->Schema) + 1;
        
        echo $this->_View->element('../Scaffold/elements/numbers',[
            'current' => $currentPage,
            'max' => $pageCount,
            'prev' => $prev,
            'next' => $next,
            'colspan' => $colspan
        ]);         
    }
    
    /* Dado un Modelo configurado en el Scaffold.
     * Realiza una paginacion.
     */
    
    public function pagination(ScaffoldComponent $Scaffold){
        $this->Scaffold = $Scaffold;
        $this->Model = $this->Scaffold->Model();
        $this->Schema = $this->Model->getSchema();
        $this->Data = $this->Scaffold->getOutput();
        
        if(! empty($this->Data)){
            echo $this->_View->element('../Scaffold/elements/pagination');
        }
        else{
            echo $this->_View->element('../Scaffold/elements/empty');            
        }
    }
    
    /* Itera el esquema del modelo e imprime cabeceras de tabla, basado en la
     * etiqueta de cada campo.
     */
    
    public function headers(){
        foreach($this->Schema as $field => $meta){
            if(@$meta['readable']){
                echo $this->_View->element('../Scaffold/elements/header',array('field' => $field,'label' => $meta['label'])); 
            }
        }
        
        if($this->Scaffold->getRenderActions()){          
            echo $this->_View->element('../Scaffold/elements/header_actions'); 
        }
    }
    
    /* Itera los datos para imprimir una fila compuesta por celdas correspondientes
     * a los campos del modelo.
     */
    
    public function rows(){        
        foreach($this->Data as $data){
            $class = $this->Model->alias;
            $this->Blob = $data[$class];
            $this->Model->loadArray($this->Blob);
            echo $this->_View->element('../Scaffold/elements/row'); 
        }
    }
    
    /* Itera los campos del esquema para imprimir los datos en una celda por campo.
     */
    
    public function row(){
        foreach($this->Schema as $this->Field => $meta){
            $value = null;
            
            if(isset($this->Blob[$this->Field])){
                $value = $this->Blob[$this->Field];
            }
            
            $this->Value = $value;
            
            if(isset($this->Schema[$this->Field])){
                $meta = $this->Schema[$this->Field];
            }
            
            if(@$meta['readable']){
                echo $this->_View->element('../Scaffold/elements/cell'); 
            }
        }
        
        if($this->Scaffold->getRenderActions()){
            echo $this->_View->element('../Scaffold/elements/cell_actions');             
        }
    }

    /* Despliega el valor del campo, adaptado a una celda de tabla.
     */
    
    public function cell(){
        $field = $this->Field;
        $metadata = $this->Schema[$field];
        $this->validation($field);

        $type = $metadata['type'];
        $view = '../Scaffold/cells/' . $type;
        echo $this->_View->element($view, [ 'value' => $this->Model->getViewValue($field), 'field' => $field]);   
    }
    
    /* Renderea links de las acciones relacionadas en la celda de acciones. */
    
    public function cellActions(){
        $links = $this->Model->getLinks();
        $view = '../Scaffold/elements/cell_link';
        
        foreach($links as $link){
            echo $this->_View->element($view, $link);   
        }
    }
    
    /* Renderea links de las acciones relacionadas al Modelo actual. */
    
    public function singleActions(){
        $links = $this->Model->getLinks();
        $view = '../Scaffold/elements/single_link';
        
        foreach($links as $link){
            if( ! $this->isCurrentURL($link['url'])){
                echo $this->_View->element($view, $link);                       
            }
        }
    }
    
    /* Determina<BOOL> si la URL especificada coincide con la URL actual. */
    
    public function isCurrentURL($url){
        $current = $this->Scaffold->getCurrentURL();
        return $current === $url;
    }
    
    /* Dado un Modelo configurado en el Scaffold, despliega un formulario con
     * todos los controles y etiquetas por campo, mas un boton de envio.
     */
    
    public function form(ScaffoldComponent $Scaffold){
        $this->Scaffold = $Scaffold;
        $this->Model = $this->Scaffold->Model();
        $this->Schema = $this->Model->getSchema();
        
        echo $this->_View->element('../Scaffold/elements/form');
    }
    
    /* Dado el modelo configurado genera una vista de detalles basado en su 
     * Esquema. */
    
    public function detail(ScaffoldComponent $Scaffold){
        $this->Scaffold = $Scaffold;
        $this->Model = $this->Scaffold->Model();
        $this->Schema = $this->Model->getSchema();
        echo $this->_View->element('../Scaffold/elements/detail');        
    }
    
    /* Dado el modelo configurado genera una vista previa del modelo a ser
     * eliminado junto a un boton que genera un POST a la accion. */
    
    public function delete(ScaffoldComponent $Scaffold){
        $this->Scaffold = $Scaffold;
        $this->Model = $this->Scaffold->Model();
        $this->Schema = $this->Model->getSchema();
        echo $this->_View->element('../Scaffold/elements/delete');        
    }
    
    /* Dado el modelo configurado genera una vista previa del modelo a ser
     * deseliminado junto a un boton que genera un POST a la accion. */
    
    public function undelete(ScaffoldComponent $Scaffold){
        $this->Scaffold = $Scaffold;
        $this->Model = $this->Scaffold->Model();
        $this->Schema = $this->Model->getSchema();
        echo $this->_View->element('../Scaffold/elements/undelete');        
    }
    
    /* Imprime la información relacionada al Modelo para la vista de Detalle.
     * Solo los campos READABLE pueden ser visualizados. */
    
    public function dumpDetailData(){
        foreach($this->Schema as $field => $meta){
            if($this->Model->isReadable($field)){
                $this->Field = $field;
                
                echo $this->_View->element('../Scaffold/elements/detail_row',[
                    'label' => $this->Model->getLabel($field)
                ]);
            }
        }
    }
    
    /* Devuelve la data en formato READABLE<String> desde el Modelo, dependiendo
     * del FIELD actual. */
    
    public function getReadableData(){
        $data =  $this->Model->getViewValue($this->Field);        
        return $data;
    }
    
    /* Dado un modelo obtiene el Label del mismo, inferiendolo del esquema para 
     * el campo slug. */
    
    public function getLabelData(){
        $field = $this->Model->getLabelField();
        return $this->Model->Data()->read($field);
    }
    
    /* Obtiene el ícono del Modelo */
    
    public function getIconData(){
        return $this->Model->getIcon();
    }
    
    /* Itera y despliega los campos del Modelo dependiendo de la configuracion
     * de su esquema.
     */
    
    public function fields(){
        foreach($this->Schema as $field => $meta){
            if(@$meta['writable']){
                echo $this->_View->element('../Scaffold/elements/field',array('field' => $field)); 
            }
        }
    }
    
    /* Despliega la etiqueta del campo, dependiendo de las configuraciones del 
     * esquema del modelo
     */
        
    public function label($field){
        $metadata = $this->Schema[$field];
        echo $this->_View->element('../Scaffold/elements/label',array('label' => $metadata['label']));     
    }

    /* Despliega un campo de formulario, dependiendo de las configuraciones del 
     * esquema del modelo.
     */
    
    public function input($field){
        $metadata = $this->Schema[$field];
        $this->validation($field);

        $type = $metadata['type'];
        $view = '../Scaffold/fields/' . $type;
        echo $this->_View->element($view, [ 'value' => $this->Model->Data()->read($field), 'field' => $field]);   
    }
    
    /* Despliega los posibles mensajes de errores de validacion del formulario 
     * enviado
     */
    
    public function validation($field){
        if(isset($this->Model->validationErrors[$field])){
            $validationMessage = '';
            
            foreach($this->Model->validationErrors[$field] as $msg){
                if(trim($msg) !== ''){
                    $validationMessage .= trim($msg) . '. ';
                }
            }
            
            if(trim($validationMessage) !== ''){            
                echo $this->_View->element('../Scaffold/elements/validation', [ 'errors' => trim($validationMessage), 'field' => $field ]);   
            }
        }
    }
    
    /* Despliega el boton de submit
     */
    
    public function submit(){
        echo $this->_View->element('../Scaffold/elements/submit', [
            'icon' => $this->Scaffold->getIcon(),
            'button' => $this->Scaffold->getButton() 
        ]);   
    }
    
    /* Despliega el boton de envio de formulario, desde las configuraciones del
     * Scaffold
     */
    
    public function formSubmit(){
        echo $this->_View->element('../Scaffold/elements/form_submit', [
            'icon' => $this->Scaffold->getIcon(),
            'button' => $this->Scaffold->getButton() 
        ]);   
    }

    /* Renderea el listado de links relacionados al MODELO */
    
    public function links(){
        $links = $this->Model->getLinks();
    }
}