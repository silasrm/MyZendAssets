<?php

	/*
	* Adapter do BuscaCep para o webservice da BuscarCep http://www.buscarcep.com.br/
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage BuscaCep
	* @name CORE_BuscaCep_Adapter_BuscarCep
	* @version 0.1
	*/
	class CORE_BuscaCep_Adapter_BuscarCep extends CORE_BuscaCepAbstract
	{
		protected $url = 'http://www.buscarcep.com.br/?cep={{cep}}&formato=string&chave=1G3v4ZSmI1.o9nigvSlHDlsdbulvEy/';
	}
