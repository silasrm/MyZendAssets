<?php

    abstract class CORE_Db_DataMapperAbstract
    {
        private static $_db = null;
        protected $_dbTable = null;
        protected $_model = null;

        public function getDb()
        {
            if( is_null(self::$_db) )
                self::$_db = Zend_Db_Table::getDefaultAdapter();

            return self::$_db;
        }

        public function getDbTable()
        {
            $this->_dbTable = new $this->_dbTable;

            if( !$this->_dbTable instanceof Zend_Db_Table_Abstract )
                throw new Exception('Tipo inválido de tabela');
            
            return $this->_dbTable;
        }

        public function save( CORE_Db_DomainObjectAbstract $obj )
        {
            if( is_null($obj->getId()) )
                return $this->_insert($obj);
            else
                return $this->_update($obj);
        }

        public function count( Zend_Db_Select $select = null )
        {
            $dbTable = $this->getDbTable();
            $db = $this->getDb();
            $data = (!is_null($select)) ? $db->fetchAll($select) : $dbTable->fetchAll();
            
            return count($data);
        }

        public function fetchAll( Zend_Db_Select $select = null )
        {
            $dbTable = $this->getDbTable();
            $db = $this->getDb();
            $data = (!is_null($select)) ? $db->fetchAll($select) : $dbTable->fetchAll();
            $dataObjArray = array();

            foreach( $data as $row )
                $dataObjArray[] = $this->_populate($row);

            return $dataObjArray;
        }

        public function find( $id )
        {
            $result = $this->getDbTable()->find((int) $id);

            if( 0 == count($result) )
                return false;
            
            return $this->_populate( $result->current() );
        }

        public function getAsArray( $id )
        {
            $result = $this->getDbTable()->find((int) $id);

            if( 0 == count($result) )
                return false;

            $row = $result->current();
            
            return $row->toArray();
        }

        public function delete( CORE_Db_DomainObjectAbstract $obj )
        {
            $result = $this->getDbTable()->find((int) $obj->getId());

            if( 0 == count($result) )
                return false;

            $row = $result->current();
            
            return $row->delete();
        }

        public function deleteCollection( array $ids )
        {
            $count = 0;

            foreach( $ids as $id )
            {
                $result = $this->getDbTable()->find( (int) $id );

                if( 0 == count($result) )
                    return false;

                $row = $result->current();
                
                if( $row->delete() )
                    $count++;
            }

            return $count;
            
        }

        protected function _populate( $data )
        {
            $obj = new $this->_model;

            foreach( $data as $k => $v )
            {
                $method = 'set' . ucfirst($k);

                if( !method_exists($obj, $method) )
                {
                    throw new Exception('Invalid property - ' . $method);
                }

                $obj->$method( $v );
            }
            
            return $obj;
        }

        public function getLastInsertId()
        {
            $db = $this->getDb();
            
            return $db->lastInsertId();
        }

        public function toArray( CORE_Db_DomainObjectAbstract $obj )
        {
            $class = get_class($obj);
            $methods = get_class_methods($class);

            $_methodsGetDefaults = array( 'getAsArray', 'getMapper', 'getLastInsertId' );

            $_data = array();

            foreach( $methods as $method )
            {
                if( ( substr( $method, 0, 3 ) == 'get' )
                    && ( !in_array( $method, $_methodsGetDefaults ) ) )
                {
                    $property = substr( $method, 3 );
                    $nameArrayKey = strtolower($property);

                    $_data[ $nameArrayKey ] = $obj->$method();
                }
            }

            return $_data;
        }

        abstract protected function _insert(CORE_Db_DomainObjectAbstract $obj);

        abstract protected function _update(CORE_Db_DomainObjectAbstract $obj);
    }