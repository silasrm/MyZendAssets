<?php

class CORE_Plugin_Navigation extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch( Zend_Controller_Request_Abstract $request )
    {
        $module = $request->getModuleName();

        if( empty( $module ) )
            $module = "default";

        if( file_exists(APPLICATION_PATH . '/configs/' . strtolower($module) . 'Navigation.xml') )
        {
            $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/' . strtolower($module) . 'Navigation.xml', 'nav');
            
            $navigation = new Zend_Navigation($config);
            
            Zend_Registry::get( 'view' )->navigation($navigation);

        }
    }
}
?>
