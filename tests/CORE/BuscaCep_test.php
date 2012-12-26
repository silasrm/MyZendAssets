<?php

	require_once '../CORE/BuscaCepAbstract.php';
	require_once '../CORE/BuscaCep.php';
	require_once '../CORE/BuscaCep/Adapter/BuscarCep.php';
	require_once '../CORE/BuscaCep/Adapter/RepublicaVirtual.php';

	class Cep extends PHPUnit_Framework_TestCase 
	{
		public function testTestaConexao()
		{
			$this->assertNotNull( file_get_contents('http://www.buscarcep.com.br/?cep=41500-206&formato=string&chave=1G3v4ZSmI1.o9nigvSlHDlsdbulvEy/') );
		}
		
		public function testBuscaNaClasse()
		{
			$busca = new CORE_BuscaCep();
			$this->assertNotNull( $busca->busca('41500206') );
		}
		
		/**
		* @expectedException InvalidArgumentException
		*/
		public function testBuscaNaClasseCepInvalido()
		{
			$busca = new CORE_BuscaCep();
			$busca->busca('41500A206');
		}
		
		/**
		* @expectedException InvalidArgumentException
		*/
		public function testBuscaNaClasseCepInvalidoTamanho()
		{
			$busca = new CORE_BuscaCep();
			$busca->busca('415002069');
		}
		
		public function testDadosCep()
		{
			$busca = new CORE_BuscaCep();
			$this->assertInternalType( 'array', $busca->busca('41500206') );
		}
		
		/**
		* @expectedException Exception
		*/
		public function testBuscaNaClasseCepInexistente()
		{
			$busca = new CORE_BuscaCep();
			$busca->busca('41150320');
		}
		
		public function testTestaAdapterERepublicaVirtual()
		{
			$busca = new CORE_BuscaCep();
			$this->assertInstanceOf( 'CORE_BuscaCep_Adapter_RepublicaVirtual', $busca->setAdapter('CORE_BuscaCep_Adapter_RepublicaVirtual')->getAdapter() );
		}
	}
