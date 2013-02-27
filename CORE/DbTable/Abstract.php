<?php

abstract class CORE_DbTable_Abstract extends Zend_Db_Table_Abstract
{

    protected $_conventionTableName = 'lower';

    protected function _setupTableName() {
        switch ($this->_conventionTableName) {
            case 'lower':
                $this->_name = strtolower($this->_name);
                break;
            case 'upper':
                $this->_name = strtoupper($this->_name);
                break;
        }
        parent::_setupTableName();
    }
	
	
	public function getName()
	{
		return $this->_name;
	}

	public function setName($name)
	{
		$this->_name = $name;
	}

	public function getPrimary()
	{
		return $this->_primary;
	}

	public function setPrimary($primary)
	{
		$this->_primary = $primary;
	}
}