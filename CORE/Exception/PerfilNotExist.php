<?php

/**
 * Exception para quando, na checagem do perfil do usuário logado, o mesmo não existe na lista de perfis.
 */
class CORE_Exception_PerfilNotExist extends Zend_Exception
{
    public function __construct(
	    $msg = null, 
	    $code = 0, 
	    Exception $previous = null)
    {
    	if (is_null($msg)) {
    		$msg = 'O perfil buscado não existe.';
    	}

	    parent::__construct($msg, (int) $code, $previous);
    }
}