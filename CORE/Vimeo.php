<?php

class CORE_Vimeo
{
	protected $_httpClient = null;
	protected $_apiUrl = 'http://vimeo.com/api/v2/';
	protected $_data = null;
	protected $_dataRaw = null;
	protected $_apiReturnFormat = 'json';

	public function __construct($httpClient = null)
	{
		if ( is_null($httpClient) ) {
			$this->_httpClient = $this->getDefaultHttpClient();
		}
	}

	public function getDefaultHttpClient()
	{
		$adapter = new Zend_Http_Client_Adapter_Curl(
			array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => false,
			)
		);

		$client = new Zend_Http_Client;
		$client->setAdapter($adapter);

		return $client;
	}

	public function getHttpClient()
	{
		if ( is_null($this->_httpClient) ) {
			throw new Exception('Cliente HTTP inválido.');
		}

		return $this->_httpClient;
	}

	public function setHttpClient($httpClient)
	{
		$this->_httpClient = $httpClient;

		return $this;
	}

	public function getApiReturnFormat()
	{
		return $this->_apiReturnFormat;
	}

	public function setApiReturnFormat($apiReturnFormat)
	{
		$this->_apiReturnFormat = $apiReturnFormat;

		return $this;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function setData($data)
	{
		$this->_data = $data;

		return $this;
	}

	public function getDataRaw()
	{
		return $this->_dataRaw;
	}

	public function setDataRaw($dataRaw)
	{
		$this->_dataRaw = $dataRaw;

		return $this;
	}

	public function getVideoDetailsById($videoId, $apiReturnFormat = null)
	{
		$tempUrl = $this->_apiUrl . 'video/' . $videoId;

		$this->_apiCall($tempUrl, $apiReturnFormat);

		return $this->getData();
	}

	protected function _apiCall($uri, $apiReturnFormat = null)
	{
		if ( is_null($apiReturnFormat) ) {
			$apiReturnFormat = $this->_apiReturnFormat;
		}

		$tempUrl = $uri . '.' . $apiReturnFormat;

		$response = $this->getHttpClient()->setUri($tempUrl)->request();

		$this->setDataRaw($response);
		$this->setData(json_decode($response->getBody()));

		return $this;
	}
}
