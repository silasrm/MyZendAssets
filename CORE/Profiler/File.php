<?php

/**
 * Faz Profiler para arquivo de log
 *
 * @usage Adicionar no application.ini
 * resources.db.params.profiler.class = "CORE_Profiler_File"
 * resources.db.params.profiler.path = APPLICATION_PATH "/../data/logs/"
 * @package CORE
 * @subpackage CORE_Profiler
 */
class CORE_Profiler_File extends Zend_Db_Profiler
{
	/**
	 * Filename
	 * @var string
	 */
	protected $_filename = 'zend-db-profiler-queries.log';

	/**
	 * Log folder path
	 * @var string
	 */
	protected $_folderPath = null;

	/**
	 * Zend_Log instance
	 * @var Zend_Log
	 */
	protected $_log;

	/**
	 * counter of the total elapsed time
	 * @var double
	 */
	protected $_totalElapsedTime;


	public function __construct($enabled = false) {
		parent::__construct($enabled);

		$this->_folderPath = sys_get_temp_dir();
		if( Zend_Registry::get('config')
			&& Zend_Registry::get('config')->resources->db->params->profiler
			&& Zend_Registry::get('config')->resources->db->params->profiler->path )
		{
			if( !is_dir(Zend_Registry::get('config')->resources->db->params->profiler->path) )
			{
				throw new InvalidArgumentException('Caminho da pasta de logs não é um diretório ou não existe.');
			}

			if( !is_writable(Zend_Registry::get('config')->resources->db->params->profiler->path) )
			{
				throw new InvalidArgumentException('Caminho da pasta de logs não tem permissão para escrita.');
			}

			$this->_folderPath = Zend_Registry::get('config')->resources->db->params->profiler->path;
		}

		if( substr($this->_folderPath, -1) != '/' )
		{
			$this->_folderPath .= '/';
		}

		$this->_log = new Zend_Log();
		$writer = new Zend_Log_Writer_Stream(
			$this->_folderPath . '/' . $this->_filename
		);

		$this->_log->addWriter($writer);

		if( !Zend_Registry::isRegistered('profiler') )
		{
			Zend_Registry::set('profiler', $this);
		}
	}

	/**
	 * Intercept the query end and log the profiling data.
	 *
	 * @param  integer $queryId
	 * @throws Zend_Db_Profiler_Exception
	 * @return void
	 */
	public function queryEnd($queryId) {
		$state = parent::queryEnd($queryId);

		if (!$this->getEnabled() || $state == self::IGNORED) {
			return;
		}

		// get profile of the current query
		$profile = $this->getQueryProfile($queryId);

		// update totalElapsedTime counter
		$this->_totalElapsedTime += $profile->getElapsedSecs();

		// create the message to be logged
		$message = "\r\nElapsed Secs: " . round($profile->getElapsedSecs(), 5) . "\r\n";
		$message .= "Query: " . $profile->getQuery() . "\r\n";
		$message .= "Query Params: " . var_export($profile->getQueryParams(), true) . "\r\n";

		if( strpos($profile->getQuery(), '?') !== false )
		{
			$message .= "Query Full: " . $this->bindParamValue($profile->getQuery(), $profile->getQueryParams()) . "\r\n";
		}

		// log the message as INFO message
		$this->_log->info($message);
	}

	/**
	 * Aplica os valores dos parametros pela ordem que vai encontrando.
	 * @param  string $text   SQL com marcação com ? no lugar dos valores
	 * @param  array $params Valores
	 * @return string         SQL com os valores
	 */
	function bindParamValue($text, $params)
	{
		foreach($params as $value)
		{
			$position = strpos($text, '?');

			if( $position )
			{
				$_pre = substr($text, 0, $position);
				$_pos = substr($text, $position+1);

				$_value = null;

				if( is_numeric($value) )
				{
					$_value = $value;
				}
				else
				{
					$_value = "'" . $value . "'";
				}

				$text = $_pre . $_value . $_pos;
			}
		}

		return $text;
	}
}