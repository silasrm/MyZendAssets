<?php

    class CORE_Form_Signup extends CORE_Form_Decorator_MeuForm
    {
        public function init()
        {
            $this->setName('signup');

            $nome = new Zend_Form_Element_Text('name');
            $nome->setLabel( $this->getTranslator()->_('default.register.name.label') )
                        ->setRequired(true)
                        ->addFilter('StripTags')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->setAttrib('class', 'text-input');
            $this->addElement($nome);
            
            $email = new Zend_Form_Element_Text('email');
            $email->setLabel( $this->getTranslator()->_('default.register.email.label') )
                        ->setRequired(true)
                        ->addFilter('StringToLower')
                        ->addFilter('StripTags')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->addValidator('EmailAddress')
                        ->addValidator(new Zend_Validate_Db_NoRecordExists('users', 'email'))
                        ->setAttrib('class', 'text-input');
            $this->addElement($email);

            $password = new Zend_Form_Element_Password('password');
            $password->setLabel( $this->getTranslator()->_('default.register.password.label') )
                                ->setRequired(true)
                                ->addFilter('StripTags')
                                ->addFilter('StringTrim')
                                ->addValidator('NotEmpty')
                                ->setAttrib('class', 'text-input');
            $this->addElement($password);

            $passwordConfirmacao = new Zend_Form_Element_Password('password_confirmacao');
            $passwordConfirmacao->setLabel( $this->getTranslator()->_('default.register.password_confirmation.label') )
                                                    ->setRequired(true)
                                                    ->addFilter('StripTags')
                                                    ->addFilter('StringTrim')
                                                    ->addValidator('NotEmpty')
                                                    ->setAttrib('class', 'text-input');
            $this->addElement($passwordConfirmacao);

            $submit = $this->createElement('submit', 'submit', array('label' => $this->getTranslator()->_('default.register.submit.label'), 'class' => 'botao'));
            $this->addElement($submit);
        }

        public function isValid($data)
        {
            $this->getElement('password_confirmacao')
                    ->addValidator(new Zend_Validate_Identical($data['password']));

            return parent::isValid($data);
        }
    }