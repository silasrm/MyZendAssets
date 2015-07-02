<?php

require_once 'Zend/Validate/Abstract.php';

/**
 * Class CORE_Validate_DataEntrePeriodo
 */
class CORE_Validate_DataEntrePeriodo extends Zend_Validate_Abstract
{
    /**
     *
     */
    const NOT_DATE = 'notDate';
    /**
     *
     */
    const NOT_BETWEEN = 'notBetween';

    /**
     * @var string
     */
    public $_min = null;

    /**
     * @var string
     */
    public $_max = null;

    /**
     * @var Zend_Date
     */
    public $_minDate = null;

    /**
     * @var Zend_Date
     */
    public $_maxDate = null;

    /**
     * @var string
     */
    public $_minDateMark = null;

    /**
     * @var string
     */
    public $_maxDateMark = null;

    /**
     * @var string
     */
    public $_format = 'dd/MM/yyyy';

    /**
     * @var string
     */
    public $_inclusive = false;

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_minDateMark',
        'max' => '_maxDateMark',
    );

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_DATE => "'%value%' não é uma data válida",
        self::NOT_BETWEEN => "'%value%' deve estar entre '%min%' e '%max%'"
    );

    /**
     * @param $options
     * @throws Zend_Validate_Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['min'] = array_shift($options);

            if (!empty($options)) {
                $temp['max'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['format'] = $options['format'];
            }

            if (!empty($options)) {
                $temp['inclusive'] = $options['inclusive'];
            }

            $options = $temp;
        }

        if (!array_key_exists('min', $options) || !array_key_exists('max', $options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Faltando definir o 'min' e 'max' de anos.");
        }

        $this
            ->setMin($options['min'])
            ->setMax($options['max']);

        if (array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
        }

        if (array_key_exists('inclusive', $options)) {
            $this->setInclusive($options['inclusive']);
        }
    }

    /**
     * Returns the min option
     *
     * @return mixed
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Sets the min option
     *
     * @param  mixed $min
     * @return CORE_Validate_DataEntrePeriodo Provides a fluent interface
     */
    public function setMin($min)
    {
        $this->_min = $min;
        return $this;
    }

    /**
     * Returns the max option
     *
     * @return mixed
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Sets the max option
     *
     * @param  mixed $max
     * @return CORE_Validate_DataEntrePeriodo Provides a fluent interface
     */
    public function setMax($max)
    {
        $this->_max = $max;
        return $this;
    }

    /**
     * Returns the format option
     *
     * @return boolean
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets the format option
     *
     * @param  boolean $format
     * @return CORE_Validate_DataEntrePeriodo Provides a fluent interface
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * @param string $inclusive
     * @return CORE_Validate_DataEntrePeriodo
     */
    public function setInclusive($inclusive)
    {
        $this->_inclusive = $inclusive;
        return $this;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!$value instanceof Zend_Date
            && !Zend_Date::isDate($value, $this->_format)) {
            $this->_error(self::NOT_DATE);

            return false;
        }

        if (!$value instanceof Zend_Date) {
            $valueDate = new Zend_Date($value, $this->_format);
        } else {
            $valueDate = $value;
        }

        $this->_setValue($valueDate->get($this->_format));

        $this->_minDate = new Zend_Date($this->_min, $this->_format);
        $this->_maxDate = new Zend_Date($this->_max, $this->_format);
//        Zend_Debug::dump($this->_minDate->get($this->_format));
//        Zend_Debug::dump($this->_maxDate->get($this->_format));
//        Zend_Debug::dump($valueDate->get($this->_format));
//        die;

        if ($this->getInclusive()
            && (($valueDate->equals($this->_minDate) && !$valueDate->equals($this->_maxDate))
                || (!$valueDate->equals($this->_minDate) && $valueDate->equals($this->_maxDate)))
        ) {
            return true;
        }

        // Se DataBase é ANTES da data mínima
        // Ou DEPOIS da data máxima
        if ($valueDate->isEarlier($this->_minDate)
            || $valueDate->isLater($this->_maxDate)) {
            $this->_minDateMark = $this->_minDate->get($this->_format);
            $this->_maxDateMark = $this->_maxDate->get($this->_format);

            $this->_error(self::NOT_BETWEEN);
            return false;
        }

        return true;
    }
}