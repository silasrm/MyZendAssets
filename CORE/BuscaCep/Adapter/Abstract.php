<?php

	/*
	* Classe abstrata para a busca de cep
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage BuscaCep
	* @name CORE_BuscaCepAbstract
	* @version 0.1
	*/
	abstract class CORE_BuscaCep_Adapter_Abstract
	{
		protected $cep = null;
		protected $url = null;
		
		// Valida o CEP informado
		public function valida( $cep )
		{
			if( !is_numeric( $cep ) 
				|| strlen( $cep ) != 8 )
				throw new InvalidArgumentException('CEP inválido');
			
			$this->cep = $cep;
		}
		
		// Chama o webservice
		public function gateway( $cep = null ) 
		{
			$this->valida( $cep );
			
			return file_get_contents( str_ireplace( '{{cep}}', $this->cep, $this->url ) );
		}
		
		// Faz a busca, trata o resultado e retorna
		public function busca( $cep )
		{
			$retornoCep = utf8_encode( urldecode( $this->gateway( $cep ) ) );
			
			$_dados = array();
			
			parse_str( $retornoCep, $_dados );
			
			if( array_key_exists( 'resultado', $_dados ) 
				&& ( $_dados['resultado'] == -1 || 
					$_dados['resultado'] == 0 ) )
				throw new Exception( 'CEP Inexistente!' );
			
			return $_dados;
		}
	}
