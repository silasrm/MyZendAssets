<?php

	/*
	* Classe abstrata para envio de SMS
	* @author Silas Ribas Martins <silasrm@gmail.com>
	* @package CORE
	* @subpackage Sms
	* @name CORE_Sms_Adapter_Abstract
	* @version 0.1
	*/
	abstract class CORE_Sms_Adapter_Abstract
	{
		protected $_options = array();
		protected $_urlBase = null;
		protected $_urlQueries = null;
		protected $_urlFinal = null;

		public function __construct($options)
		{
			$this->_options = $options;
		}

		// Faz a busca, trata o resultado e retorna
		public function envia( $mensagem, $numero, $source = null )
		{
			return $this->_gateway( $mensagem, $numero, $source );
		}

		// Chama o webservice
		protected function _gateway( $mensagem, $numero, $source = null )
		{
			$dados = $this->_options;

			$dados['mensagem'] = urlencode($mensagem);
			$dados['numero'] = $numero;

			if( !is_null($source) )
			{
				$dados['source'] = $source;
			}

			$this->_urlFinal = $this->_urlBase . '?' . $this->_stringf( $this->_urlQueries, $dados );

			Zend_Registry::get('log')->log( 'SMSGateway: ' . get_class($this), Zend_Log::INFO );
			Zend_Registry::get('log')->log( 'URL: ' . $this->_urlBase . ' URL Query: ' . $this->_urlQueries, Zend_Log::INFO );
			Zend_Registry::get('log')->log( 'URL-Final: ' . $this->_urlFinal, Zend_Log::INFO );
			Zend_Registry::get('log')->log( 'Dados: ' . var_export($dados, true), Zend_Log::INFO );

			try
			{
				$retorno = $this->executa();

				Zend_Registry::get('log')->log(
					"\n\n\n" . $retorno . "\n\n",
					Zend_Log::INFO,
					'/logs/curl.log'
				);

			} catch (Exception $e) {
				$retorno = $e->getMessage();

				Zend_Registry::get('log')->log(
					"\n\n\n" . $e->getTraceAsString() . "\n\n",
					Zend_Log::CRIT,
					'/logs/curl.log'
				);
			}

			Zend_Registry::get('log')->log( 'Retorno envio: ' . $retorno, Zend_Log::INFO );

			return $retorno;
		}

		protected function _stringf( $template, array $vars = array() )
		{
			if( substr( PHP_VERSION, 0, 3 ) >= 5.3 )
			{
				return preg_replace_callback( '/{{(\w+)}}/'
											, function( $match ) use( &$vars ) {
													return $vars[ $match[ 1 ] ];
												}
											, $template );
			}
			else
			{
				return preg_replace_callback( '/{{(\w+)}}/'
							, create_function( '$match', 'return &$vars[$match[1]];' )
							, $template );
			}
		}

		/**
		 * Set cUrl default config for Zend Http Client adapter cUrl
		 */
		public function getDefaultCurlConfig()
		{
			$fp = fopen(APPLICATION_PATH . '/../data/logs/curl.log', 'a');

			$this->curlConfig = array(
				CURLOPT_VERBOSE => 1,
				CURLOPT_TIMEOUT => 300,
				CURLOPT_CONNECTTIMEOUT => 300,
				CURLOPT_FILE => $fp,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => false,
				CURLOPT_POSTFIELDS => $this->_urlQueries
			);
		}

		/**
		 *Execute de request to API
		 *
		 * @return <string>
		 */
		private function executa()
		{
			$httpClientAdapter = new Zend_Http_Client_Adapter_Curl();
			$this->getDefaultCurlConfig();

			$httpClient = new Zend_Http_Client();
			$httpClient->setAdapter( $httpClientAdapter );
			$httpClient->setUri( $this->_urlFinal );

			if( is_array( $this->curlConfig ) )
			{
				$httpClient->setConfig( array( 'curloptions' => $this->curlConfig ) );
			}

			return $httpClient->request( Zend_Http_Client::GET );
		}
	}