<?php

	/*
	* Adapter do BuscaCep para o webservice da BuscarCep http://www.buscarcep.com.br/
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage Sms
	* @name CORE_Sms_Adapter_RouteSMS
	* @version 0.1
	*/
	class CORE_Sms_Adapter_RouteSMS extends CORE_Sms_Adapter_Abstract
	{
		protected $_urlBase = 'http://smsplus.routesms.com:8080/bulksms/bulksms';
		protected $_urlQueries = 'username={{usuario}}&password={{senha}}&type=0&dlr=1&source={{source}}&destination={{numero}}&message={{mensagem}}';
	}
