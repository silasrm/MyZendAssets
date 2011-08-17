<?php


class CORE_Plugin_Translate extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $sessionLanguage = new Zend_Session_Namespace('language');
        $sessionLanguage->lang = $request->getParam('language','en');
        
        $translate = Zend_Registry::get("translate");
        $translate->setLocale($sessionLanguage->lang);

        Zend_Form::setDefaultTranslator($translate);
        
        return true;
   }
}