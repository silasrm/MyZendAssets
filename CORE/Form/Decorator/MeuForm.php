<?php

    class CORE_Form_Decorator_MeuForm extends Zend_Form
    {
        public function addElement( $element, $name = null, $options = null )
        {
            $element->removeDecorator('label');
            $this->removeDecorator('label');
            $element->removeDecorator('DtDdWrapper');
            $element->removeDecorator('HtmlTag');
            $element->removeDecorator('Errors');

            parent::addElement($element);
        }
    }