<?php

	/*
	* Classe para envio de SMS
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage Sms
	* @name CORE_Sms
	* @version 0.1
	*/
	class CORE_Sms 
	{
		private $adapter = null;
		
		public function __construct( $options, CORE_Sms_Adapter_Abstract $adapter = null )
		{
			if( !is_null( $adapter ) )
				$this->setAdapter( $adapter );
			else
				$this->setAdapter( new CORE_Sms_Adapter_RouteSMS($options) );
		}
		
		// Inicia o adapter
		public function setAdapter( CORE_Sms_Adapter_Abstract $adapter )
		{
			if( is_null( $adapter ) )
				throw new Exception( 'Adapter não informado' );
		
			$this->adapter = $adapter;
			
			return $this;
		}
		
		// Retorna o adapter
		public function getAdapter()
		{
			return $this->adapter;
		}
		
		// Faz a busca usando o adapter
		public function envia( $mensagem, $numero, $source = null )
		{
			return $this->getAdapter()->envia( $mensagem, $numero, $source );
		}
	}
