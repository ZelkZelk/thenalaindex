<?php

App::uses('AppHelper', 'View/Helper');

/* Helper que ayuda a inyectar CSS y JS desde el controlador al HEAD del 
 * layout HTML. */

class HeadHelper extends AppHelper{
    /* Genera los tags <link> segun configuracion, llamar la funcion dentro
     * del HEAD del layout.
     * 
     * La configuracion es un array hash cuyos keys son el path al archivo y
     * su valor es otro array hash cuyos keys son atributos y sus valores son
     * los valores de dichos atributos.
     */
    public function dumpCss(array $css = []){
        foreach($css as $path => $params){
            echo $this->_View->element('../Head/css',[
                'path' => $path,
                'params' => $params
            ]);
        }
    }
    
    /* Genera los tags <script> segun configuracion, llamar la funcion dentro
     * del HEAD del layout.
     * 
     * Configuracion idem a CSS.
     */
    
    public function dumpJs(array $js = []){
        foreach($js as $path => $params){            
            echo $this->_View->element('../Head/js',[
                'path' => $path,
                'params' => $params
            ]);
        }     
    }
    
    /* Genera un script JS con las variables globales configuradas,
     * 
     * La configuracion viene en el array $vars, cuyos keys son el nombre de las
     * variables y su valor el dato que se transformará a JSON.
     */
    
    public function dumpJsVars(array $vars = []){
        foreach($vars as $name => $data){
            echo $this->_View->element('../Head/js_vars',[
                'json' => json_encode($data),
                'name' => $name
            ]);
        }     
    }
    
    /* Dado un array hash genera la secuencia<STRING> de atributos HTML de la forma
     * key="val".
     */
    
    public function getParams(array $params = []){
        $stringParams = '';
        
        foreach($params as $attr => $val){
            $stringParams .= "{$attr}=\"{$val}\" ";
        }
        
        return trim($stringParams);
    }
}