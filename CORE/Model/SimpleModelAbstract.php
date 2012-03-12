<?php

    abstract class CORE_Model_SimpleModelAbstract
    {
        protected $_options = array();

        public function add( $id, $title )
        {
            if( !array_key_exists( $id, $this->_options ) )
                $this->_options[ $id ] = $title;

            return $this;
        }

        public function fetchPair()
        {
            return $this->_options;
        }

        public function find( $id )
        {
            if( array_key_exists( $id, $this->_options ) )
                return $this->_options[ $id ];

            return false;
        }
    }