<?php

class CORE_Plugin_Title extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch( Zend_Controller_Request_Abstract $request )
    {
        if( $request->getModuleName() == 'admin' )
            Zend_Registry::get( 'view' )->headTitle( Zend_Registry::get( 'siteConfiguration' )->site->admin->name );
        else
            Zend_Registry::get( 'view' )->headTitle( Zend_Registry::get( 'siteConfiguration' )->site->name );
    }
}
?>
