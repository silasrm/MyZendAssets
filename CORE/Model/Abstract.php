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
			throw new CORE_Model_Exception_RegisterNotFound;

		return $current->toArray();
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
		$where = array(
			'id = ?' => $id
		);

		if( is_array($id))
		{
			$where = array(
				'id in (?)' => $id
			);
		}

		return $this->_dbTable->delete($where);
	}

	public function fetchPairs(array $conditions = null, $orders = null, $cols = array('*') )
	{
		$sql = $this->_dbTable->select();
		$sql->from( $this->_dbTable->getName(), $cols );

		$this->_trataCondicoes( $sql, $conditions );
		$this->_trataOrdem( $sql, $orders );

		return $this->getDb()->fetchPairs($sql);
	}

	public function fetchAll($conditions = null, $limit = null, $orders = null, Zend_Db_Select $sql = null, $cols = array('*') )
	{
		if( is_null($sql) )
		{
			$sql = $this->_dbTable
						->getAdapter()
						->select()
						->from( $this->_dbTable->getName(), $cols );
		}

		$this->_trataCondicoes( $sql, $conditions );
		$this->_trataOrdem( $sql, $orders );

		if( !is_null($limit) || $limit != 0 )
		{
			$sql->limit($limit);
		}

		return $sql->query()->fetchAll();
	}

	public function fetch($conditions = null, $orders = null, Zend_Db_Select $sql = null, $cols = array('*') )
	{
		if( is_null($sql) )
		{
			$sql = $this->_dbTable
						->getAdapter()
						->select()
						->from( $this->_dbTable->getName(), $cols );
		}

		$this->_trataCondicoes( $sql, $conditions );
		$this->_trataOrdem( $sql, $orders );

		return $sql->query()->fetch();
	}

	public function count(array $conditions = null)
	{
		$sql = $this->_dbTable
					->getAdapter()
					->select()
					->from( $this->_dbTable->getName(), array( 'total' => 'COUNT(id)' ) );

		$this->_trataCondicoes( $sql, $conditions );

		$data = $sql->query()->fetch();

		return $data['total'];
	}

	public function search(array $params)
	{
		$str = isset($params['str']) ? $params['str'] : "";
		$conditions = isset($params['conditions']) ? $params['conditions'] : array();
		$ordem = isset($params['ordem']) ? $params['ordem'] : null;
		$grupo = isset($params['grupo']) ? $params['grupo'] : null;
		$page = isset($params['pagina']) ? (int) $params['pagina'] : 1;
		$perPage = isset($params['perpage'])
					? (int) $params['perpage']
					: Zend_Registry::get('config')->paginator->totalItemPerPage;
		$limit = ( $page - 1 ) * $perPage;
		$where = null;
		$sql = $this->_dbTable
					->select();

		if( !is_null($ordem) )
		{
			$this->_trataOrdem( $sql, $ordem );
		}

		if( !is_null($grupo) )
		{
			$this->_trataGrupo( $sql, $grupo );
		}

		$this->_trataCondicoes( $sql, $conditions );

		if ( !is_null($limit) || $limit != 0 )
		{
			$sql->limit($perPage, $limit);
		}

		$paginator = Zend_Paginator::factory($sql);
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

	protected function _trataCondicoes( Zend_Db_Select &$sql, array $conditions = null )
	{
		if (!is_null($conditions)) {
			foreach ($conditions as $key => $condition) {
				if( !is_array($condition) )
				{
					if( !is_numeric( $key ) )
					{
						$sql->where($key, $condition);
					}
					else
					{
						$sql->where($condition);
					}
				}
				else
				{
					if( array_key_exists(1, $condition) && $condition[1] == 'OR' )
					{
						$sql->orWhere($key, $condition[0]);
					}
					else
					{
						$sql->where($key, $condition);
					}
				}
			}
		}
	}

	protected function _trataOrdem( Zend_Db_Select &$sql, $orders = null )
	{
		if( !is_null($orders) )
		{
			if( is_array($orders) )
			{
				foreach ($orders as $order) {
					$sql->order($order);
				}
			}
			else
			{
				$sql->order($orders);
			}
		}
	}

	protected function _trataGrupo( Zend_Db_Select &$sql, $grupos = null )
	{
		if( !is_null($grupos) )
		{
			if( is_array($grupos) )
			{
				foreach ($grupos as $grupo) {
					$sql->group($grupo);
				}
			}
			else
			{
				$sql->group($grupos);
			}
		}
	}
}