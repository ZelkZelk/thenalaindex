<?php
/**
 * AppShell file
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Shell', 'Console');

/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 *
 * @package       app.Console.Commando
 */

if( !defined('SHELL_CURRENT_PATH')){
    $path = dirname(__FILE__);
    define('SHELL_CURRENT_PATH',$path);
}


class AppShell extends Shell {
    public $uses = [ 'Target' ];
    
    /* Funcion que configura el logging para debug */
    
    protected function setupLog($file,$scopes,$types = [ 'info' ]){
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => $types,
            'scopes' => $scopes,
            'file' => $file
        ));
    }
    
    /* Funcion que loguea un mensaje segun scope especificado */
    
    protected function logcat($message,$scope,$type = 'info'){
        $pid = posix_getpid();
        $date = date('H:i:s d/m/Y');
        $str = " ***** $date @ $pid ***** <"
                . $message
                . '>'; 
        
        CakeLog::write($type, $str, $scope);
    }
    
    /* Mutex general, si se adquiere el semaforo ejecuta $callback() */
    
    protected function mutex($key,$callback,$timeout = 10){
        if($this->Target->block($key,$timeout)){
            $callback();
            $this->unmutex($key);
        }
    }
    
    /* Cancela el Mutex, libera el semaforo. */
    
    protected function unmutex($key){
        $this->Target->unblock($key);
    }
}
