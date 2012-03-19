<?php

    class CORE_Form_Decorator_Form extends Zend_Form
    {
        protected $_cssClassGroup = null;

        public function createElement($type, $name, $options = null)
        {
            $this->setDecorators(array(
                'FormElements',
                array(
                    array('data'=>'HtmlTag'),
                    array('tag'=>'fieldset')
                ),
                'Form'
            ));
            
            $this->_cssClassGroup = str_replace(
                array('[', ']', '{', '}', '(', ')'),
                '',
                $this->_cssClassGroup
            );

            $this->setDisplayGroupDecorators(array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag'=>'div', 'class' => 'grupo ' . $this->_cssClassGroup)
                )
            ));

            if( !array_key_exists('decorators', $options)) {
                switch($type) {
                    case 'button':
                        $options['decorators'] = array(
                            'ViewHelper',
                            array('Description',array('tag'=>'','escape'=>false)),
                            #'Errors', 
                            array(
                                array('data'=>'HtmlTag'), 
                                array('tag' => 'dd')
                            ),
                            array(
                                array('row'=>'HtmlTag'),
                                array('tag'=>'dl', 'class'=>'button')
                            )
                        );
                    break;
                    case 'file':
                        $options['decorators'] = array(
                            'File',
                            array('Description',array('tag'=>'','escape'=>false)),
                            #'Errors',
                            array(array('data'=>'HtmlTag'), array('tag' => 'dd','escape'=>false)),
                            array('Label', array('tag' => 'dt','escape'=>false, 'requiredSuffix' => '<span class="required">*</span>')),
                            array(array('row'=>'HtmlTag'), array('tag'=>'dl'))
                        );
                    break;
                    case 'text':
                    case 'textarea':
                    case 'select':
                    case 'password':
                        $nameClassElement = str_replace(
                            array('[', ']', '{', '}', '(', ')'),
                            '',
                            $name
                        );

                        $options['decorators'] = array(
                            'ViewHelper',
                            array('Description',array('tag'=>'','escape'=>false)),
                            #'Errors',
                            array(array('data'=>'HtmlTag'), array('tag' => 'dd','escape'=>false)),
                            array('Label', array('tag' => 'dt','escape'=>false, 'requiredSuffix' => '<span class="required">*</span>')),
                            array(array('row'=>'HtmlTag'),array('tag'=>'dl', 'class' => 'element-' . $nameClassElement))
                        );
                    break;
                }
            }

            return parent::createElement($type, $name, $options);
        }

        public function addElement($element, $name = null, $options = null)
        {
            if( $element instanceOf CORE_Form_Element_PlainText )
            {
                $element->clearDecorators()
                        ->addDecorator('ViewHelper');
            }

            parent::addElement($element, $name, $options);
            return $this;
        }
    }