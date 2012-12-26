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
            $config = new Zend_Config_Xml(
                APPLICATION_PATH . '/configs/' . strtolower($module) . 'Navigation.xml', 'nav'
            );

            $navigation = new Zend_Navigation($config);

            $params = $request->setParamSources(array('_GET'))->getParams();

            unset($params['controller']);
            unset($params['action']);
            unset($params['module']);

            $rootPage = $navigation->findOneBy(
                'resource', $request->getParam('module') . ':' . $request->getParam('controller')
            );

            if( $rootPage )
            {
                $rootPage = $rootPage->findOneBy('action', $request->getParam('action'));

                if( $rootPage )
                {
                    if( count($rootPage->getParent()->getParams()) > 0 )
                    {
                        $parentParams = $rootPage->getParent()->getParams();

                        foreach( $parentParams as $paKey => $paParam )
                        {
                            if( array_key_exists($paKey, $params) )
                            {
                                $parentParams[$paKey] = $params[$paKey];
                            }
                        }

                        $rootPage->getParent()->setParams($parentParams);
                    }

                    if( count($rootPage->getParams()) > 0 )
                    {
                        $sonParams = $rootPage->getParams();

                        foreach( $sonParams as $sonKey => $sonParam )
                        {
                            if( array_key_exists($sonKey, $params) )
                            {
                                $sonParams[$sonKey] = $params[$sonKey];
                            }
                        }

                        $rootPage->setParams($sonParams);
                    }
                }
            }

            Zend_Registry::get( 'view' )
                            ->navigation($navigation)
                            ->setAcl( Zend_Registry::get('acl') )
                            ->setRole( Zend_Registry::get('role') );

        }
    }
}