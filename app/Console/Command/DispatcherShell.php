<?php

/* Se encarga de determinar cual Target debe ser Crawleado. 
 * 
 * Analiza cada Target revisando su fecha de ultimo crawl, comparandola con
 * la fecha actual más la frecuencia en días.
 * 
 * Si es factible de ejecución se envia la señal a CrawlerShell
 */

class DispatcherShell extends AppShell{    
    private static $mutexKey = 'Nala::Dispatcher::mainMutex()';
    
    /* Esta funcion llama al mutex, asegurandonos que solo una instancia
     * de este proceso se ejecuta en un instante determinado.
     */
    
    function main(){
        $this->setupLog('dispatcher.log', [ 'disp' ]);
        
        $this->mutex(self::$mutexKey, function(){
            $this->logcat('MUTEX','disp');
            $this->mainImpl();
            $this->logcat('UNMUTEX','disp');
        });
    }
    
    /* Busca los targets del sistema los cuales esten dentro de su fecha
     * de crawling. */
    
    private function mainImpl(){
        $availableTargets = $this->Target->findAvailableTargets();
        $alias = $this->Target->alias;
        
        foreach ($availableTargets as $target){
            $array = $target[$alias];
            $this->Target->loadArray($array);
            $id = $this->Target->id;
            
            if($id){
                $this->logcat("CRAWLER@$id",'disp');
                $this->parallelCrawler($id);
            }
        }
    }
    
    /* Se encarga de ejecutar el crawler en otro proceso o thread.
     * 
     * Recibe como parametro el ID del Target requerido. 
     * 
     * NOTA: el script ejecutado hace Mutex por Target nuevamente para
     * asegurar que un solo proceso esta alimentando al sistema por Target.
     */
    
    private function parallelCrawler($target_id){
        $path = dirname(__FILE__);
        $app = $path . '/../..';
        
        $file = "/tmp/nala.t{$target_id}." . date('dmY') . '.log';
        exec("($app/Console/cake crawler -t $target_id) > $file &");
    }
}
