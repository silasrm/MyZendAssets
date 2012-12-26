<?php

    abstract class CORE_SimpleModelAbstract
    {
        protected $_options = array();

        public function add( $id, $title )
        {
            if( !array_key_exists( $id, $this->_options ) )
                $this->_options[ $id ] = $title;

            return $this;
        }

        public function getPair()
        {
            return $this->_options;
        }

        public function get( $id )
        {
            if( array_key_exists( $id, $this->_options ) )
                return $this->_options[ $id ];

            return false;
        }
    }