<?php

    class CORE_Form_Login extends CORE_Form_Decorator_MeuForm
    {
        public function init()
        {  
            $this->setName('login');

            $login = new Zend_Form_Element_Text('login');
            $login->setLabel( $this->getTranslator()->_('admin.auth.login.label') )
                      ->setRequired(true)
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setAttrib('class', 'text-input')
                      ->addErrorMessage('Pleaser enter a username');
            $this->addElement($login);

            $senha = new Zend_Form_Element_Password('password');
            $senha->setLabel( $this->getTranslator()->_('admin.auth.password.label') )
                        ->setRequired(true)
                        ->addFilter('StripTags')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->setAttrib('class', 'text-input')
                        ->addErrorMessage('Pleaser enter your password');
            $this->addElement($senha);

            $submit = $this->createElement('submit', 'submit', array('label' => $this->getTranslator()->_('admin.auth.submit.label'), 'class' => 'botao'));
            $this->addElement($submit);
        }
    }