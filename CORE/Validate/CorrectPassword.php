<?php

require_once 'Zend/Validate/Abstract.php';

class CORE_Validate_CorrectPassword extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
 
    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Senha incorreta.'
    );
 
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);
        
        if( !empty($context['id']) )
        {
            $db = new Model_Usuario();
            $data = $db->find($context['id'])->toArray();
            if(sha256($value) == $data['senha'])
                return true;
        } 

        $this->_error(self::NOT_MATCH);

        return false;
    }
}
