<?php

    class CORE_Form_Decorator_MeuFormComErro extends Zend_Form
    {
        public function addElement( $element, $name = null, $options = null )
        {
            $element->removeDecorator('label');
            $this->removeDecorator('label');
            $element->removeDecorator('DtDdWrapper');
            $element->removeDecorator('HtmlTag');

            parent::addElement($element);
        }
    }