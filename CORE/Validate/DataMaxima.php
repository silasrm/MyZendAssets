<?php

/**
 * @todo Por validacao de igualdade para validar se é >= ou so >
 * @example
 * $this->getElement('rdm_emissao')
 *		->addValidator(new CORE_Validate_DataMaxima(array(
 *			'data_maxima' => $data['rdm_data_entrada'],
 *			'formato' => 'YYYY-MM-dd'
 *		)));
 */
class CORE_Validate_DataMaxima extends Zend_Validate_Abstract
{
	const DATA_INVALIDA = "data_invalida";
	const DATA_MAIOR = "data_maior";
	const DATA_NAO_INFORMADA = "data_nao_informada";

	protected $_messageTemplates = array(
		self::DATA_INVALIDA => "Data %value% é inválida. Tem que está no formato %formato%",
		self::DATA_MAIOR => "Data %value% é maior que %data_maxima%",
		self::DATA_NAO_INFORMADA => "Data máxima não informada",
	);

    protected $_messageVariables = array(
        'data_maxima'  => '_dataMaxima',
        'formato'  => '_formato'
    );

	protected $_dataMaxima = null;
	protected $_zDataMaxima = null;
	protected $_formato = 'dd/MM/YYYY';

	public function __construct( array $options )
	{
		if( !array_key_exists('data_maxima', $options) )
		{
			$this->_error(self::DATA_INVALIDA);

			return false;
		}

		if( !array_key_exists('formato', $options) )
		{
			$options['formato'] = 'dd/MM/YYYY';
		}

		$formato =  $options['formato'];
		$dataMaxima = $options['data_maxima'];

		if( Zend_Date::isDate($dataMaxima, $formato) )
		{
			$this->_zDataMaxima = new Zend_Date($dataMaxima, $formato);
			$this->_dataMaxima = $this->_zDataMaxima->get( $this->_formato );
		}
		else
		{
			$this->_error(self::DATA_INVALIDA);

			return false;
		}
	}

	public function isValid($value)
	{
		$this->_setValue($value);

		if( !Zend_Date::isDate($value, $this->_formato) )
		{
			$this->_error(self::DATA_INVALIDA);

			return false;
		}

		$zdate = new Zend_Date($value, $this->_formato);

		if( !( $this->_zDataMaxima->equals($zdate) || $this->_zDataMaxima->isLater($zdate) ) )
		{
			$this->_error(self::DATA_MAIOR);

			return false;
		}

		return true;
	}
}