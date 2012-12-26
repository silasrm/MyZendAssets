<?php

class CORE_Plugin_Log extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch( Zend_Controller_Request_Abstract $request ){}

    public function postDispatch( Zend_Controller_Request_Abstract $request )
    {
        $user = 'guest';

        if( Zend_Auth::getInstance()->hasIdentity() )
        {
            $user_id = Zend_Auth::getInstance()
                                ->getStorage()
                                ->read()
                                ->id;

            $user_nome = Zend_Auth::getInstance()
                                ->getStorage()
                                ->read()
                                ->nome;

            $user = $user_id . '/' . $user_nome;
        }

        $mensagem = "Usuário: {$user} | URI: {$request->getRequestUri()}";

        Zend_Registry::get('log')->log( $mensagem, Zend_Log::DEBUG );
    }
}