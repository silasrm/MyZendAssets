<?php

    /**
     * Class for transform any string in slug ( string with nothing whitespace, accents and others using in urls as paramethers )
     *
     * @author Silas Ribas <silasrm@gmail.com> github.com/silasrm
     * @version 0.1
     * @package CORE
     * @subpackage Utils
     * @name Slug
     * @example CORE_Utils_Slug::getInstance()->slug( 'Chamar metodo do model no template passando parâmetros' );
     */
    class CORE_Utils_Slug
    {
        /**
         * @var CORE_Utils_Slug
         */
        protected static $_instance = null;

        public function __construct(){}
        public function __clone(){}

        /**
         *  Return a instance of this class
         * @return CORE_Utils_Slug
         */
        public static function getInstance()
        {
            if( null === self::$_instance )
            {
                self::$_instance = new self;
            }

            return self::$_instance;
        }

        public function slug( $string )
        {
            $string = CORE_Utils_String::getInstance()->removeAccents( $string );
            $string = strtolower(  strip_tags( trim( $string ) ) );
            $string = preg_replace( '/[^a-z0-9-]/', '-', $string );
            $string = preg_replace( '/-+/', "-", $string );
            
            return $string;
        }
    }

?>
