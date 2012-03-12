<?php

class CORE_Form_Element_PlainText extends Zend_Form_Element_Xhtml {

    public $helper = 'PlainTextElement';

    public function isValid($value){

        return true;
    }
}