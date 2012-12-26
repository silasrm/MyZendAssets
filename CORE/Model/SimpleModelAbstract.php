<?php

abstract class CORE_Model_SimpleModelAbstract
{
	protected $_options = array();

	public function add( $title, $id = null )
	{
		if( !is_null($id) )
		{
			$this->_options[ $id ] = $title;
		}
		else
		{
			$this->_options[] = $title;
		}

		return $this;
	}

	public function fetchPairs( array $exclude = null )
	{
		$options = $this->_options;
		if( !is_null($exclude) )
		{
			foreach( $exclude as $id )
			{
				unset($options[ $id ]);
			}
		}

		return $options;
	}

	public function find( $id, $array = false )
	{
		if( array_key_exists( $id, $this->_options ) )
		{
			if( $array )
			{
				return array( $id, $this->_options[ $id ] );
			}

			return $this->_options[ $id ];
		}

		return false;
	}
}