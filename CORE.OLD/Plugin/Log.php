<?php

class CORE_Plugin_Log extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch( Zend_Controller_Request_Abstract $request ){}

    public function postDispatch( Zend_Controller_Request_Abstract $request ) 
    {
    	$user = 'guest';

    	if( Zend_Auth::getInstance()
                        ->setStorage(  new Zend_Auth_Storage_Session('admin')  )
                        ->hasIdentity() ) 
        {
        	$user_id = Zend_Auth::getInstance()
                        		->setStorage(  new Zend_Auth_Storage_Session('admin')  )
                        		->getStorage()
                        		->read()
                        		->id;

            $user_nome = Zend_Auth::getInstance()
                        		->setStorage(  new Zend_Auth_Storage_Session('admin')  )
                        		->getStorage()
                        		->read()
                        		->nome;

            $user = $user_id . '/' . $user_nome;
        }
        else if( Zend_Auth::getInstance()
                        ->setStorage(  new Zend_Auth_Storage_Session('user')  )
                        ->hasIdentity() ) 
        {
            $user_id = Zend_Auth::getInstance()
                                ->setStorage(  new Zend_Auth_Storage_Session('user')  )
                                ->getStorage()
                                ->read()
                                ->id;

            $user_nome = Zend_Auth::getInstance()
                                ->setStorage(  new Zend_Auth_Storage_Session('user')  )
                                ->getStorage()
                                ->read()
                                ->nome;

            $user = $user_id . '/' . $user_nome;
        }
        
		$mensagem = "Usuário: {$user} | URI: {$request->getRequestUri()}";

		self::log( $mensagem, Zend_Log::INFO );
    }

    public static function log( $mensagem, $prioridade = Zend_Log::NOTICE, $destino = null )
    {
        if( is_null( $destino ) )
            $destino = Zend_Registry::get('log_stream');

        $logWriter = new Zend_Log_Writer_Stream( realpath( DATA_PATH . $destino ) );
        $log = new Zend_Log( $logWriter );
        $log->log( $mensagem , $prioridade );
    }

    public static function logErro( $mensagem )
    {
        $request = new Zend_Controller_Request_Http();
        self::log( $mensagem . ' | URI: ' . $request->getRequestUri(), Zend_Log::ERR, '/logs/erros.log' );
    }
}
?>
