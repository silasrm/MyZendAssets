<?php

require_once 'Zend/Validate/Abstract.php';

class CORE_Validate_Vimeo_VideoExists extends Zend_Validate_Abstract
{
	const NOT_EXISTS = 'videoNotExists';

	protected $_messageTemplates = array(
		self::NOT_EXISTS => 'Não foi encontrado nenhum vídeo com o ID informado.'
	);

	public function isValid($value)
	{
		$value = (string) $value;
		$this->_setValue($value);

		$error = false;
		$vimeo = new CORE_Vimeo;
		$videoDetails = $vimeo->getVideoDetailsById($value);

		if ( is_null($videoDetails) ) {
			$error = true;
			$this->_error(self::NOT_EXISTS);
		}

		return !$error;
	}
}