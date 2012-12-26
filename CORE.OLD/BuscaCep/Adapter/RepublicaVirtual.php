<?php

	/*
	* Adapter do BuscaCep para o webservice da RepublicaVirtual http://www.republicavirtual.com.br/
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage BuscaCep
	* @name CORE_BuscaCep_Adapter_RepublicaVirtual
	* @version 0.1
	*/
	class CORE_BuscaCep_Adapter_RepublicaVirtual extends CORE_BuscaCepAbstract
	{
		protected $url = 'http://cep.republicavirtual.com.br/web_cep.php?cep={{cep}}&formato=query_string';
	}
