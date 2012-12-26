<?php

class CORE_Plugin_PHPExcel extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        require_once 'PHPExcel/PHPExcel/IOFactory.php';
        
        return $request;
    }
}