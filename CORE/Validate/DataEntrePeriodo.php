<?php

require_once 'Zend/Validate/Abstract.php';

class CORE_Validate_DataEntrePeriodo extends Zend_Validate_Abstract
{
    const NOT_DATE = 'notDate';
    const NOT_BETWEEN = 'notBetween';
 
    public $_min = 0;
    public $_minDate = null;
    public $_max = 100;
    public $_maxDate = null;
    public $_formatInput = 'dd/MM/yyyy';
    public $_formatCompare = 'MM/yyyy';
 
    protected $_messageVariables = array(
        'min' => '_minDate',
        'max' => '_maxDate',
        'minIdade' => '_min',
        'maxIdade' => '_max'
    );
 
    protected $_messageTemplates = array(
        self::NOT_DATE => "'%value%' não é uma data válida",
        self::NOT_BETWEEN => "'%value%' deve estar entre '%min%' ( %maxIdade% anos ) e '%max%'  ( %minIdade% anos )"
    );

    public function __construct($options)
    {
        if( $options instanceof Zend_Config )
        {
            $options = $options->toArray();
        }
        else if( !is_array($options) )
        {
            $options = func_get_args();
            $temp['min'] = array_shift($options);

            if( !empty($options) )
            {
                $temp['max'] = array_shift($options);
            }

            if( !empty($options) )
            {
                $temp['format_input'] = $options['format_input'];
            }

            if( !empty($options) )
            {
                $temp['format_compare'] = $options['format_compare'];
            }

            $options = $temp;
        }

        if (!array_key_exists('min', $options) || !array_key_exists('max', $options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Faltando definir o 'min' e 'max' de anos.");
        }

        $this->setMin($options['min'])
             ->setMax($options['max']);

        if (array_key_exists('format_input', $options)) {
            $this->setFormatInput($options['format_input']);
        }

        if (array_key_exists('format_compare', $options)) {
            $this->setFormatCompare($options['format_compare']);
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
     * Returns the format_input option
     *
     * @return boolean
     */
    public function getFormatInput()
    {
        return $this->_formatInput;
    }

    /**
     * Sets the format_input option
     *
     * @param  boolean $formatInput
     * @return CORE_Validate_DataEntrePeriodo Provides a fluent interface
     */
    public function setFormatInput($formatInput)
    {
        $this->_formatInput = $formatInput;
        return $this;
    }

    /**
     * Returns the format_compare option
     *
     * @return boolean
     */
    public function getFormatCompare()
    {
        return $this->_formatCompare;
    }

    /**
     * Sets the format_compare option
     *
     * @param  boolean $formatCompare
     * @return CORE_Validate_DataEntrePeriodo Provides a fluent interface
     */
    public function setFormatCompare($formatCompare)
    {
        $this->_formatCompare = $formatCompare;
        return $this;
    }

    public function isValid( $value )
    {
        $this->_setValue($value);

        if( !Zend_Date::isDate($value, $this->_formatInput) )
        {
            $this->_error(self::NOT_DATE);

            return false;
        }

        $zdate = new Zend_Date();
        $minDate = $zdate->now()->subYear($this->_max);
        $maxDate = $zdate->now()->subYear($this->_min);

        $nascimento = new Zend_Date( $value, $this->_formatInput );

        // Se Nascimento é ANTES da data mínima 
        // Ou DEPOIS da data máxima
        if( $nascimento->isEarlier( $minDate, $this->_formatCompare ) 
            || $nascimento->isLater( $maxDate, $this->_formatCompare ) )
        {
            $this->_minDate = $zdate->setDate( $minDate )->get( $this->_formatCompare );
            $this->_maxDate = $zdate->setDate( $maxDate )->get( $this->_formatCompare );

            $this->_error(self::NOT_BETWEEN);
            return false;
        }

        return true;
    }
}