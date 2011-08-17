<?php

class CORE_Plugin_Log extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch( Zend_Controller_Request_Abstract $request ){}

    public static function log( $mensagem, $prioridade = Zend_Log::NOTICE )
    {
        $logConfig = Zend_Registry::get('log_stream');
        $logWriter = new Zend_Log_Writer_Stream( $logConfig->writerParams->stream );
        $log = new Zend_Log( $logWriter );
        $log->log( $mensagem , $prioridade );
    }
}
?>
