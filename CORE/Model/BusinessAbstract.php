<?php

	/**
		Camada de negócio ( Business ), para guardar as regras de negócios. Ficando como intermediário 
		entre o controller e o model/mapper.
		@author Silas Ribas Martins
		@package CORE
		@version 0.1
	 */
	abstract class CORE_Model_BusinessAbstract
	{
		protected $model = array();
		protected $objModel = array();

		public function __construct() 
		{
			$this->setModel();
		}

		public function setModel()
		{
			foreach( $this->model as $k => $model )
			{
				if( !class_exists( $model ) )
					CORE_Plugin_Log::logErro( $model . ' - Model não existe.' );
				else
					$this->objModel[ $k ] = new $model;
			}
		}

		public function getModel( $model = 'default' ) 
		{
			if( !array_key_exists( $model, $this->objModel ) )
				throw new Exception('Model não existe ou não foi carregado');
			
			return $this->objModel[ $model ];
		}

		/**
			Implementação de busca.
		 */
		abstract function search( array $params );
		/**
			Implementação do insert/update
		 */
		abstract function save( array $data );
	}