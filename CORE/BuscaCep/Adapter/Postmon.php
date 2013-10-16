<?php

	/*
	* Adapter do BuscaCep para o webservice da Postmon http://www.postmon.com.br/
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage BuscaCep
	* @name CORE_BuscaCep_Adapter_RepublicaVirtual
	* @version 0.1
	*/
	class CORE_BuscaCep_Adapter_Postmon extends CORE_BuscaCep_Adapter_Abstract
	{
		protected $url = 'http://api.postmon.com.br/cep/{{cep}}';
	}
