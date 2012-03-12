<?php

abstract class CORE_DbTable_Abstract extends Zend_Db_Table_Abstract
{
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