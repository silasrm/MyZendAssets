<?php

/**
 * Adapter do BuscaCep para o webservice da Postmon http://www.postmon.com.br/
 * @author Silas Ribas Martins <silasrm@gmail.com>
 * @package CORE
 * @subpackage BuscaCep
 * @name CORE_BuscaCep_Adapter_RepublicaVirtual
 * @version 0.1
 */
class CORE_BuscaCep_Adapter_Postmon extends CORE_BuscaCep_Adapter_Abstract
{
    protected $url = 'http://api.postmon.com.br/v1/cep/{{cep}}';

    // Chama o webservice
    public function gateway($cep = null)
    {
        $this->valida($cep);

        return @file_get_contents(str_ireplace('{{cep}}', $this->cep, $this->url));
    }

    // Faz a busca, trata o resultado e retorna
    public function busca($cep)
    {
        $retornoCep = $this->gateway($cep);

        if (empty($retornoCep))
            throw new Exception('CEP Inexistente!');

        return (array)json_decode($retornoCep);
    }
}