<?php


class CORE_Plugin_Translate extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request )
    {
    	$translate = new Model_Translate();
		$translate->change();
        
        return true;
   }
}