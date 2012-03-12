<?php

	/*
	* Classe BuscaCep para a busca de CEP em webservices.
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage BuscaCep
	* @name CORE_BuscaCep
	* @version 0.1
	*/
	class CORE_BuscaCep 
	{
		private $adapter = null;
		
		public function __construct( $adapter = null )
		{
			if( !is_null( $adapter ) )
				$this->setAdapter( $adapter );
			else
				$this->setAdapter( 'CORE_BuscaCep_Adapter_BuscarCep' );
		}
		
		// Inicia o adapter
		public function setAdapter( $adapter )
		{
			if( is_null( $adapter ) )
				throw new Exception( 'Adapter não informado' );
		
			$this->adapter = new $adapter;
			
			return $this;
		}
		
		// Retorna o adapter
		public function getAdapter()
		{
			return $this->adapter;
		}
		
		// Faz a busca usando o adapter
		public function busca( $cep )
		{
			return $this->getAdapter()->busca( $cep );
		}
	}
