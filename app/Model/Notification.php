<?php

App::uses('Model','CrawlerLog');

class Notification extends AppModel implements ReactApiModel {    
    public $useDbConfig = 'backend';
    
    private $schema = 'default';    
    private $defaultSchema = [
        'notification_event_id' => array(
            'type' => 'int',
            'required' => true,
            'unique' => true,
            'label' => 'Evento',
            'writable' => false,
            'readable' => true,
        ),
        'emails' => array(
            'required' => true,
            'type' => 'email',
            'csv' => true,
            'label' => 'Emails',
            'writable' => true,
            'readable' => true,
            'maxlength' => 60,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'NotificationEvent';
                return $schema;
        }
        
        return array();
    }
    
    private $labelField;

    public function getLabelField(){
        return $this->labelField;
    }
    
    public function getIcon(){
        return 'envelope';
    }
    
    public function getLogicalField() {
        return null;
    }

    public function isPhysicalDeletor() {
        return true;
    }
    
    /* Obtiene los links para mostrar en el menu del modulo de notificaciones via email */
    
    public function fetchLinks(){
        $raw = $this->fetchRawEvents();
        $links = [];
        
        foreach($raw as $blob){
            $id = $blob['NotificationEvent']['id'];
            $code = $blob['NotificationEvent']['code'];
            $desc = $blob['NotificationEvent']['description'];
            
            $links[] = [
                'label' => $code,
                'url' => '',
                'icon' => 'fa fa-group',
                'sublabel' => $desc,
                'id' => $id
            ];
        }
        
        return $links;
    }

    public function fetchRawEvents() {        
        $opts = [];
        $joins = [];
        $joins[] = 
                    "INNER JOIN \"backend\".\"notification_events\" "
                        . "AS \"NotificationEvent\" "
                        . "ON \"NotificationEvent\".\"id\" = \"Notification\".\"notification_event_id\"";
        
        $opts['joins'] = $joins;
        $opts['fields'] = 
                "\"NotificationEvent\".\"id\", "
                    . "\"NotificationEvent\".\"code\", "
                    . "\"NotificationEvent\".\"description\" ";
        
        $raw = $this->find('all',$opts);
        return $raw;
    }
    
    /* Obtiene los Emails de una Lista por su Codigo*/
    
    public function fetchEmails($code) {        
        $opts = [];
        $joins = [];
        $cnd = [];
        
        $cnd['NotificationEvent.code'] = $code;        
        $joins[] = 
                    "INNER JOIN \"backend\".\"notification_events\" "
                        . "AS \"NotificationEvent\" "
                        . "ON \"NotificationEvent\".\"id\" = \"Notification\".\"notification_event_id\"";
        
        $opts['joins'] = $joins;
        $opts['fields'] = 
                "\"Notification\".\"emails\"";
        
        $opts['conditions'] = $cnd;
        
        $raw = $this->find('first',$opts);
        $alias = $this->alias;
        $blob = $raw[$alias];
        $this->loadArray($blob);
        
        $emails = $this->readEmails();
        return $emails;
    }
    
    /* Implementacion ReactApi para eliminar un email de la lista */

    public function drop($api,$post) {
        $id = $post['env']['id'];
        $index = $post['id'];
        
        if( ! $this->loadFromId($id)){        
            return $api->setError('Lista inválida');
        }
        
        $emails = $this->readEmails();

        if( ! isset($emails[$index])){
            return $api->setError('E-mail no existentes');              
        }      
        
        unset($emails[$index]);    
        $newEmails = array_values($emails);
        $this->setEmails($newEmails);    
        $this->upsert($api,$newEmails);        
    }
    
    /* Implementacion ReactApi para editar un email de la lista */

    public function edit($api,$post) {
        $id = $post['env']['id'];
        $index = $post['id'];
        $email = $post['value'];
        
        if( ! $this->validate($email)){
            return $api->setError('Email inválido');   
        }
        
        if( ! $this->loadFromId($id)){        
            return $api->setError('Lista inválida');
        }
        
        $emails = $this->readEmails();

        if( ! isset($emails[$index])){
            return $api->setError('E-mail no existente');              
        }      
                    
        $emails[$index] = $email;
        $this->setEmails($emails);      

        $this->upsert($api,[
            $index => $email
        ]);        
    }
    
    /* Implementacion ReactApi para devolver la lista de emails especificada */

    public function feed($api,$post) {
        $id = $post['env']['id'];
        
        if($this->loadFromId($id)){         
            $emails = $this->readEmails();            
            $api->setData($emails);
        }
        else{
            $api->setError('Lista inválida');
        }        
    }

    /* Implementacion ReactApi para agregar un email a la lista */
    
    public function push($api,$post) {
        $id = $post['env']['id'];
        $newEmail = $post['value'];  
        
        if( ! $this->loadFromId($id)){   
            return $api->setError('Lista inválida');         
        }          
            
        if( ! $this->validate($newEmail)){
            return $api->setError('Email inválido');   
        }
        
        $emails = $this->readEmails();            
        $index = count($emails);
        $emails[$index] = $newEmail;            
        $this->setEmails($emails);   

        $this->upsert($api,[
            $index => $newEmail
        ]);
    }
    
    /* Valida que el email sea valido */
    
    public function validate($email){
        return Validation::email($email);
    }
    
    /* Ejecuta el UPSERT */
    
    public function upsert($api,$response){    
        $this->set($this->Data()->dump());

        if($this->save()){
            $api->setData($response);
        }
        else{
            error_log(serialize($this->validationErrors));
            $api->setError('Error al guardar Lista');                
        }        
    }
    
    /*  Obtiene la lista de emails como un array simple */
    
    protected function readEmails(){
        $rawEmails = $this->Data()->read('emails');
        $csvEmails = preg_replace('/{|}/','',$rawEmails);
        
        if($csvEmails === ''){
            return [];
        }
        
        return explode(',', $csvEmails);
    }
    
    /* Setea la lista de emails desde un array simple */
    
    protected function setEmails($emails = []){        
        if(empty($emails)){
            $rawEmails = '{}';
        }
        else{            
            $csvEmails = implode(',', $emails);  
            $fixEmails = preg_replace('/{|}/','',$csvEmails);   
            $trueCsvEmails = preg_replace('/^,/','',$fixEmails);   
            $rawEmails = '{' . $trueCsvEmails . '}';
        }
        
        $this->Data()->write('emails',$rawEmails);
    }
    
    /* Envia las notificaciones para la Lista Adecuada */
    
    public static $startCode = 'START';
    public static $doneCode = 'DONE';
    public static $failCode = 'FAIL';
    
    public function start(CrawlerLog $log){
        $this->initSend(self::$startCode, $log);
    }
    
    public function done(CrawlerLog $log){
        $this->initSend(self::$doneCode, $log);
    }
    
    public function fail(CrawlerLog $log){
        $this->initSend(self::$failCode, $log);
    }
    
    private function initSend($code,CrawlerLog $log){        
        $this->setupLog();
        $this->logcat('SEND:' . $code);
        $dst = $this->fetchEmails($code);
        
        if($this->send($code,$log,$dst)){            
            $this->logcat('SENT:' . $code);
        }
        else{
            $this->logcat('FAIL:' . $code);            
        }
    }
    
    /* Realiza el envio, a partir de un array de email destinatatios y un
     * objeto CrawlerLog de donde sacar la info.
    */
    
    private function send($code,CrawlerLog $log,array $dst = []){
        $this->logcat('DST:' . implode(',', $dst));
            
        if(empty($dst)){
            return false;
        }
        
        if( ! Configure::read('Notification.enabled')){
            return false;
        }
        
        // El CakeEmail puede tirar Excepciones
        // Sin embargo no es un feature vital para detener el proceso de crawler
        
        try{
            return $this->sendmail($code,$log,$dst);
        } 
        catch (Exception $ex) {
            $this->logcat('Exception:' . $ex->getMessage());
        }
        
        return false;
    }
    
    /* Realiza el envio via email */
    
    private function sendmail($code,CrawlerLog $log,array $dst = []){
        App::uses('CakeEmail', 'Network/Email');                            
        $email = new CakeEmail();
        $email->addTo($dst);
        $subject = $this->getSubject($log,$code);
        
        $vars = [];
        $vars['CrawlerLog'] = $log;
        $vars['title_for_layout'] = $subject;
        
        $email->viewVars($vars);
        $email->template($this->getTemplate($code));
        $email->emailFormat('html');  
        $email->from([
            Configure::read('Notification.rcpt') => Configure::read('Notification.from')
        ]);
        
        $file = $this->attachTraceLog($log,$email);
        
        $email->subject($subject);
        $contents = $email->send();
        
        $this->logcat('SEND-HEADERS:' . $contents['headers']);
        $this->logcat('SEND-MESSAGE:' . $contents['message']);
        
        if($file){
            $log->deleteTempTrace($file);
        }
        
        return true;
    }
    
    /* Agrega la traza del log como archivo adjunto al CakeEmail */

    private function attachTraceLog(CrawlerLog $log, CakeEmail $email) {
        $file = $log->getTempTrace();
        
        if($file){
            $email->attachments([ 'log.txt' => [
                'file' => $file,
                'mimetype' => 'text/plain',
                'contentId' => uniqid()          
            ]]);
        }
        
        return $file;
    }
    
    /* Obtiene el Subject dependiente del Codigo de envio */
    
    private function getSubject(CrawlerLog $log,$code){
        switch($code){
            case self::$doneCode:
                return "[DONE] Crawler @ {$log->Data()->read('Target')}";
            case self::$failCode:
                return "[FAIL] Crawler @ {$log->Data()->read('Target')}";
            case self::$startCode:
                return "[START] Crawler @ {$log->Data()->read('Target')}";
        }
    }
    
    /* Obtiene el Template de envio dependiente del Codigo */
    
    private function getTemplate($code){
        switch($code){
            case self::$doneCode:
                return 'done_notification';
            case self::$failCode:
                return 'fail_notification';
            case self::$startCode:
                return 'start_notification';
        }        
    }
    
    /* Notifications tiene su propio archivo, notif.log */
    
    private function setupLog(){
        CakeLog::config('notifications', array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'notifications' ],
            'file' => 'notif.log'
        ));
    }
    
    private function logcat($message){
        $pid = posix_getpid();
        $date = date('H:i:s d/m/Y');
        $str = " ***** $date @ $pid ***** <"
                . $message
                . '>'; 
        
        CakeLog::write('info', $str, 'notifications');
    }

}