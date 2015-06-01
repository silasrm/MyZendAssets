<?php

/**
 * Class CORE_Model_Abstract
 */
abstract class CORE_Model_Abstract
{
    /**
     * @var null
     */
    private static $_db = null;

    /**
     * @var
     */
    protected $_dbTable;

    /**
     * * Para fazer trocar temporária de dbTable.
     * @var
     */
    protected $_dbTableTemporaria;

    /**
     * @var
     */
    protected $_log;

    /**
     * @return null|Zend_Db_Adapter_Abstract
     */
    public function getDb()
    {
        if (is_null(self::$_db))
            self::$_db = Zend_Db_Table::getDefaultAdapter();

        return self::$_db;
    }

    /**
     * @return mixed
     */
    public function getDbTable()
    {
        return $this->_dbTable;
    }

    /**
     * @param $id
     * @return mixed
     * @throws CORE_Model_Exception_RegisterNotFound
     */
    public function find($id)
    {
        $current = $this->_dbTable->find($id)->current();
        if (!$current)
            throw new CORE_Model_Exception_RegisterNotFound;

        return $current->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        if (isset($data['id'])) {
            return $this->_update($data);
        } else {
            return $this->_insert($data);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $where = array(
            'id = ?' => $id
        );

        if (is_array($id)) {
            $where = array(
                'id in (?)' => $id
            );
        }

        return $this->_dbTable->delete($where);
    }

    /**
     * @param array $conditions
     * @param null $orders
     * @param array $cols
     * @param null $limit
     * @return array
     */
    public function fetchPairs(array $conditions = null, $orders = null, $cols = array('*'), $limit = null)
    {
        $sql = $this->_dbTable->select();
        $sql->from($this->_dbTable->getName(), $cols);

        $this->_trataCondicoes($sql, $conditions);
        $this->_trataOrdem($sql, $orders);

        if (!is_null($limit) || $limit != 0) {
            $sql->limit($limit);
        }

        return $this->getDb()->fetchPairs($sql);
    }

    /**
     * @param null $conditions
     * @param null $orders
     * @param Zend_Db_Select $sql
     * @param array $cols
     * @return mixed
     */
    public function fetch($conditions = null, $orders = null, Zend_Db_Select $sql = null, $cols = array('*'))
    {
        if (is_null($sql)) {
            $sql = $this->_dbTable
                ->getAdapter()
                ->select()
                ->from($this->_dbTable->getName(), $cols);
        }

        $this->_trataCondicoes($sql, $conditions);
        $this->_trataOrdem($sql, $orders);

        return $sql->query()->fetch();
    }

    /**
     * @param null $conditions
     * @param null $limit
     * @param null $orders
     * @param Zend_Db_Select $sql
     * @param array $cols
     * @param null $groups
     * @return array
     */
    public function fetchAll(
        $conditions = null,
        $limit = null,
        $orders = null,
        Zend_Db_Select $sql = null,
        $cols = array('*'),
        $groups = null
    )
    {
        if (is_null($sql)) {
            $sql = $this->_dbTable
                ->getAdapter()
                ->select()
                ->from($this->_dbTable->getName(), $cols);
        }

        $this->_trataCondicoes($sql, $conditions);
        $this->_trataOrdem($sql, $orders);
        $this->_trataGrupo($sql, $groups);

        if (!is_null($limit) || $limit != 0) {
            if (!is_array($limit)) {
                $sql->limit($limit);
            } else {
                $sql->limit($limit['count'], $limit['offset']);
            }
        }

        return $sql->query()->fetchAll();
    }

    /**
     * @param array $conditions
     * @return mixed
     */
    public function count(array $conditions = null)
    {
        $sql = $this->_dbTable
            ->getAdapter()
            ->select()
            ->from($this->_dbTable->getName(), array('total' => 'COUNT(id)'));

        $this->_trataCondicoes($sql, $conditions);

        $data = $sql->query()->fetch();

        return $data['total'];
    }

    /**
     * @param array $params
     * @return Zend_Paginator
     * @throws Zend_Exception
     * @throws Zend_Paginator_Exception
     */
    public function search(array $params)
    {
        $str = isset($params['str']) ? $params['str'] : "";
        $conditions = isset($params['conditions']) ? $params['conditions'] : array();
        $ordem = isset($params['ordem']) ? $params['ordem'] : null;
        $page = isset($params['pagina']) ? (int)$params['pagina'] : 1;
        $perPage = isset($params['perpage'])
            ? (int)$params['perpage']
            : Zend_Registry::get('config')->paginator->totalItemPerPage;
        $limit = ($page - 1) * $perPage;
        $where = null;
        $sql = $this->_dbTable
            ->select();

        if (!is_null($ordem)) {
            $this->_trataOrdem($sql, $ordem);
        }

        $this->_trataCondicoes($sql, $conditions);

        if (!is_null($limit) || $limit != 0) {
            $sql->limit($perPage, $limit);
        }

        $paginator = Zend_Paginator::factory($sql);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return $paginator;
    }

    /**
     * @param $id
     * @return mixed
     * @throws CORE_Model_Exception_RegisterNotFound
     */
    public function getAsArray($id)
    {
        return $this->find($id)->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function _insert(array $data)
    {
        return $this->_dbTable->insert($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function _update(array $data)
    {
        $id = $data['id'];
        unset($data['id']);

        return $this->_dbTable->update($data, array('id = ?' => $id));
    }

    /**
     * @param Zend_Db_Select $sql
     * @param array $conditions
     */
    protected function _trataCondicoes(Zend_Db_Select &$sql, array $conditions = null)
    {
        if (!is_null($conditions)) {
            foreach ($conditions as $key => $condition) {
                if (!is_array($condition)) {
                    if (!is_numeric($key)) {
                        $sql->where($key, $condition);
                    } else {
                        $sql->where($condition);
                    }
                } else {
                    if (array_key_exists(1, $condition) && $condition[1] == 'OR') {
                        $sql->orWhere($key, $condition[0]);
                    } else {
                        $sql->where($key, $condition);
                    }
                }
            }
        }
    }

    /**
     * @param Zend_Db_Select $sql
     * @param null $orders
     */
    protected function _trataOrdem(Zend_Db_Select &$sql, $orders = null)
    {
        if (!is_null($orders)) {
            if (is_array($orders)) {
                foreach ($orders as $key => $order) {
                    $sql->order($order);
                }
            } else {
                $sql->order($orders);
            }
        }
    }

    /**
     * @param Zend_Db_Select $sql
     * @param null $orders
     */
    protected function _trataGrupo(Zend_Db_Select &$sql, $orders = null)
    {
        if (!is_null($orders)) {
            if (is_array($orders)) {
                foreach ($orders as $key => $order) {
                    $sql->group($order);
                }
            } else {
                $sql->group($orders);
            }
        }
    }

    public function getDbTableTemporaria()
    {
        return $this->_dbTableTemporaria;
    }

    public function setDbTableTemporaria($dbTableTemporaria)
    {
        return $this->_dbTableTemporaria = $dbTableTemporaria;
    }
}