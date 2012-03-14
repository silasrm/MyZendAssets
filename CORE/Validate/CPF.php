<?php

require_once 'Zend/Validate/Abstract.php';

class CORE_Validate_CPF extends Zend_Validate_Abstract
{
    const INVALID_CPF = 'invalidcpf';

    protected $_messageTemplates = array(
        self::INVALID_CPF => 'CPF inválido'
    );

    public function isValid( $value )
    {
        $cpf = preg_replace("/[-\.]/", '', $value);

        if( !is_numeric($cpf) )
        {
            $status = false;
        }
        else
        {
            //VERIFICA
            if( ( $cpf == '11111111111' ) 
                || ( $cpf == '22222222222' ) 
                || ( $cpf == '33333333333' ) 
                || ( $cpf == '44444444444' ) 
                || ( $cpf == '55555555555' ) 
                || ( $cpf == '66666666666' ) 
                || ( $cpf == '77777777777' ) 
                || ( $cpf == '88888888888' ) 
                || ( $cpf == '99999999999' ) 
                || ( $cpf == '00000000000' ) )
            {
                $status = false;
            }
            else
            {
                //PEGA O DIGITO VERIFIACADOR
                $dv_informado = substr($cpf, 9, 2);

                for( $i = 0; $i <= 8; $i++ )
                {
                    $digito[$i] = substr( $cpf, $i, 1 );
                }

                //CALCULA O VALOR DO 10º DIGITO DE VERIFICAÇÂO
                $posicao = 10;
                $soma = 0;

                for( $i = 0; $i <= 8; $i++ )
                {
                    $soma = $soma + $digito[$i] * $posicao;
                    $posicao = $posicao - 1;
                }

                $digito[9] = $soma % 11;

                if( $digito[9] < 2 )
                {
                    $digito[9] = 0;
                }
                else
                {
                    $digito[9] = 11 - $digito[9];
                }

                //CALCULA O VALOR DO 11º DIGITO DE VERIFICAÇÃO
                $posicao = 11;
                $soma = 0;

                for( $i = 0; $i <= 9; $i++ )
                {
                    $soma = $soma + $digito [$i] * $posicao;
                    $posicao = $posicao - 1;
                }

                $digito[10] = $soma % 11;

                if( $digito[10] < 2 )
                {
                    $digito[10] = 0;
                }
                else
                {
                    $digito[10] = 11 - $digito[10];
                }

                //VERIFICA SE O DV CALCULADO É IGUAL AO INFORMADO
                $dv = $digito [9] * 10 + $digito [10];

                if( $dv != $dv_informado )
                {
                    $status = false;
                }
                else
                {
                    $status = true;
                }
            } //FECHA ELSE
        } //FECHA ELSE(is_numeric)

        if( $status )
        {
            return $cpf;
        }
        else
        {
            $this->_error(self::INVALID_CPF);
        }
    }

}
