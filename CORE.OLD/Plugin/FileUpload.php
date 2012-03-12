<?php

class CORE_Plugin_FileUpload extends Zend_Controller_Plugin_Abstract 
{
    public function preDispatch( Zend_Controller_Request_Abstract $request )
    {
        require_once 'FileUpload/Uploader.php';
        require_once 'FileUpload/Xhr.php';
        require_once 'FileUpload/Form.php';

        return $request;
    }
}