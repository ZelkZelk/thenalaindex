<?php

/* Este componente ayuda a setear un controller con React.
 * 
 * Configuracion default se lee desde app/Config/react.php
 * 
 * react_js_path : ruta completa al archivo react.js
 * react_dom_js_path : ruta completa al archivo react-dom.js
 * babel_js_path : ruta completa al archivo babel.js
 * react [
 *      controller [
 *          action [
 *              script : ruta completa al script que tomara control de la vista
 *          ]
 *      ]
 * ]
 * 
 * La configuracion puede cambiarse en runtime usando los setters correspondientes.
 * 
 *  */


class ReactComponent extends Component{
    private $Controller;
    private $reactPath;
    private $reactDomPath;
    private $babelPath;
    private $scriptPath;
        
    public function initialize(\Controller $controller) {
        $this->Controller = $controller;
        $this->Controller->set('React',$this);
        
        Configure::load('react');
        $this->setReactPath(Configure::read('react_js_path'));
        $this->setReactDomPath(Configure::read('react_dom_js_path'));
        $this->setBabelPath(Configure::read('babel_js_path'));
        
        $ctrl = $this->Controller->params['controller'];
        $actn = $this->Controller->params['action'];
        $this->setScriptPath(Configure::read("react.{$ctrl}.{$actn}"));
        
        return parent::initialize($controller);
    }
    
    function getReactPath() {
        return $this->reactPath;
    }

    function getReactDomPath() {
        return $this->reactDomPath;
    }

    function getScriptPath() {
        return $this->scriptPath;
    }

    function setReactPath($reactPath) {
        $this->reactPath = $reactPath;
    }

    function setReactDomPath($reactDomPath) {
        $this->reactDomPath = $reactDomPath;
    }

    function setScriptPath($scriptPath) {
        $this->scriptPath = $scriptPath;
    }
    
    function getBabelPath() {
        return $this->babelPath;
    }

    function setBabelPath($babelPath) {
        $this->babelPath = $babelPath;
    }

        
    /* Inyecta los archivos JS necesarios, ver HeaderHeleper. 
     * 
     * Notese que antes de cargas scriptPath se inicializa viewVars como una variable
     * global con su contenido en formato JSON. De la forma
     * 
     * var $ReactData = {};
     * 
     */
    
    public function load(){
        $this->injectScripts();
        $this->renderBlank();
    }
    
    /* Inyecta los scripts necesarios al HEAD del HTML */

    private function injectScripts() {
        $this->Controller->js($this->getReactPath());
        $this->Controller->js($this->getReactDomPath());
        $this->Controller->js($this->getBabelPath());        
        $this->Controller->js($this->getScriptPath(),[ 'type' => 'text/babel' ]);
        $this->Controller->jsVar('$ReactData', $this->Controller->viewVars);
    }
    
    /* Se asegura que la vista este vacia para darle total control al React,
     * exceptuando el layout.
     * 
     * Crea un DIV con el id "react-root" para utilizar como HOOK en el DOM.
     */
    
    private function renderBlank(){
        $this->Controller->render('../React/blank');
    }    
}