<?php

abstract class CORE_Model_Abstract
{
    private static $_db = null;
    protected $_dbTable;
    protected $_log;

    public function getDb()
    {
        if( is_null(self::$_db) )
            self::$_db = Zend_Db_Table::getDefaultAdapter();

        return self::$_db;
    }

    public function getDbTable() 
    {
        return $this->_dbTable;
    }

    public function find($id)
    {
        $current = $this->_dbTable->find($id)->current();
        if( !$current )
            throw new Exception('Registro não encontrado.');

        return $current;
    }

    public function save(array $data)
    {
        if (isset($data['id'])) {
            return $this->_update($data);
        } else {
            return $this->_insert($data);
        }
    }

    public function delete($id)
    {
        return $this->_dbTable->delete('id=' . $id);
    }

    public function fetchPair( $conditions = null )
    {
        $query = $this->_dbTable->select();
        $query->from( $this->_dbTable->getName() );

        if (!is_null($conditions)) {
            foreach ($conditions as $ky => $condition) {
                $sql->where($ky, $condition);
            }
        }
        
        return $this->getDb()->fetchPairs($query);
    }

    public function fetchAll($params=null)
    {
        $page = isset($params['page']) ? (int) $params['page'] : 1;
        $perage = isset($params['perpage']) ? (int) $params['perpage'] : 10;

        $paginator = Zend_Paginator::factory($this->_dbTable->fetchAll());
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perage);
        return $paginator;
    }

    public function getAll($conditions = null)
    {
        $sql = $this->_dbTable->getAdapter()            
                        ->select()
                        ->from( $this->_dbTable->getName() );
        
        if (!is_null($conditions)) {
            foreach ($conditions as $ky => $condition) {
                $sql->where($ky, $condition);
            }
        }

        return $sql->query()->fetchAll();
    }

    public function search(array $params)
    {
        $str = isset($params['str']) ? $params['str'] : "";
        $filtro = isset($params['filtro']) ? $params['filtro'] : "";
        $page = isset($params['page']) ? (int) $params['page'] : 1;
        $perPage = isset($params['perpage']) 
                    ? (int) $params['perpage'] 
                    : Zend_Registry::get('config')->paginator->totalItemPerPage;
        $limit = ( $page - 1 ) * $perPage;
        $where = null;
        $select = $this->_dbTable->select();

        if (!empty($str)) {
            $select->where($filtro . " like '%" . $str . "%'");
        }

        if ( !is_null($limit) || $limit != 0 )
            $select->limit($perPage, $limit);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }

    public function getAsArray( $id ) {
        return $this->find($id)->toArray();
    }

    public function _insert(array $data)
    { 
        return $this->_dbTable->insert($data);
    }

    public function _update(array $data)
    {
        $id = $data['id'];
        unset($data['id']);

        return $this->_dbTable->update($data, array('id = ?' => $id ));
    }

    #abstract public function _insert(array $data);

    #abstract public function _update(array $data);
}
