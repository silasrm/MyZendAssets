<?php

    abstract class CORE_Db_DomainObjectAbstract
    {
        private $id = null;
        protected $_mapper = null;

        public function __construct( array $options = null )
        {
            if( is_array($options) )
                $this->setOptions($options);
        }

        public function setOptions( array $options )
        {
            $methods = get_class_methods($this);

            foreach( $options as $key => $value )
            {
                $method = 'set' . ucfirst($key);

                if( in_array($method, $methods) )
                    $this->$method($value);
            }
            
            return $this;
        }

        public function setId( $id )
        {
            if( !is_null($this->id) )
            {
                throw new Exception('ID não pode ser alterado');
            }
            
            $this->id = $id;
        }

        public function getId()
        {
            return $this->id;
        }

        public function getMapper()
        {
            $m = new $this->_mapper;
            
            return $m;
        }

        public function save()
        {
            $this->getMapper()->save($this);
        }

        public function fetchAll( Zend_Db_Select $select = null )
        {
            return $this->getMapper()->fetchAll($select);
        }

        public function count( Zend_Db_Select $select = null )
        {
            return $this->getMapper()->count($select);
        }

        public function find( $id )
        {
            return $this->getMapper()->find($id);
        }

        public function getAsArray( $id )
        {
            return $this->getMapper()->getAsArray($id);
        }

        public function delete()
        {
            return $this->getMapper()->delete($this);
        }

        public function getLastInsertId()
        {
            return $this->getMapper()->getLastInsertId();
        }

        public function toArray()
        {
            return $this->getMapper()->toArray($this);
        }

        public function __call( $name, $arguments )
        {
            /**
                Verificar se o método existe e pode ser chamado ( public )
            */
            if( method_exists( $this->getMapper(), $name ) 
                && is_callable( array( $this->getMapper(), $name ) ) ) 
            {
                return $this->getMapper()->$name( implode( ',', $arguments ) );
            }
        }
    }