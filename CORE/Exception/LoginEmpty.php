<?php

/**
 * Exception para quando o login estiver vazio.
 */
class CORE_Exception_LoginEmpty extends Zend_Exception
{
    public function __construct(
	    $msg = null, 
	    $code = 0, 
	    Exception $previous = null)
    {
    	if (is_null($msg)) {
    		$msg = 'Não é possível consultar as informações do usuário. ';
    		$msg .= 'Login não informado.';
    	}

	    parent::__construct($msg, (int) $code, $previous);
    }
}