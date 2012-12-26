<?php 

/**
 * Implementação de adapter de autenticação de dica do Erick Tedescki http://www.slideshare.net/erickt86/tdc-2012-php
 * 
 * @example Uso normal
 * $auth = Zend_Auth::getInstance();
 * $dbAdapter = Zend_Db_Table::getDefaultAdapter();
 * $authAdapter = new CORE_Auth_64KLoop($dbAdapter);
 * $authAdapter->setTableName('usuarios')
 *				->setIdentityColumn('usuario')
 *				->setCredentialColumn('senha')
 *				->setCredentialSaltColumn('senha_salt')
 *				->setGlobalSalt($salt)
 *				->setCredentialTreatment("and ativo = 1 and excluido = 0")
 *				->setIdentity($login)
 *				->setCredential($senha);
 * 
 * //Efetua o login
 * $result = $auth->authenticate($authAdapter);
 * 
 * if ( $result->isValid() ) {
 *		//Verifica se o login foi efetuado com sucesso
 *		$info = $authAdapter->getResultRowObject(null, 'senha');
 *		$storage = $this->_auth->getStorage();
 *		$storage->write($info);
 *		
 *		return true;
 * }
 */

/**
 * @see Zend_Auth_Adapter_DbTable
 */
require_once 'Zend/Auth/Adapter/DbTable.php';

class CORE_Auth_64KLoop extends Zend_Auth_Adapter_DbTable
{
	protected $_identity = null;
	protected $_identityColumn = null;
	protected $_credential = null;
	protected $_credentialColumn = null;
	protected $_credentialTreatment = null;
	protected $_credentialSaltColumn = null;
	protected $_tableName = null;
	protected $_globalSalt = null;

	public function getIdentity()
	{
		if( is_null($this->_identity) )
		{
			throw InvalidArgumentException('Usuário não informada');
		}

	    return $this->_identity;
	}
	
	public function setIdentity($identity)
	{
		$this->_identity = $identity;

	    return $this;
	}

	public function getIdentityColumn()
	{
		if( is_null($this->_identityColumn) )
		{
			throw InvalidArgumentException('Coluna do usuário não informada');
		}

	    return $this->_identityColumn;
	}
	
	public function setIdentityColumn($identityColumn)
	{
	    $this->_identityColumn = $identityColumn;
		
	    return $this;
	}

	public function getCredential()
	{
		if( is_null($this->_credential) )
		{
			throw InvalidArgumentException('Senha não informada');
		}
		
	    return $this->_credential;
	}
	
	public function setCredential($credential)
	{
	    $this->_credential = $credential;
		
	    return $this;
	}

	public function getCredentialColumn()
	{
		if( is_null($this->_credentialColumn) )
		{
			throw InvalidArgumentException('Coluna da senha não informada');
		}
		
	    return $this->_credentialColumn;
	}
	
	public function setCredentialColumn($credentialColumn)
	{
	    $this->_credentialColumn = $credentialColumn;
		
	    return $this;
	}

	public function getCredentialTreatment()
	{
	    return $this->_credentialTreatment;
	}
	
	public function setCredentialTreatment($credentialTreatment)
	{
	    $this->_credentialTreatment = $credentialTreatment;
		
	    return $this;
	}

	public function getCredentialSaltColumn()
	{
		if( is_null($this->_credentialSaltColumn) )
		{
			throw InvalidArgumentException('Coluna do salt da senha não informada');
		}
		
	    return $this->_credentialSaltColumn;
	}
	
	public function setCredentialSaltColumn($credentialSaltColumn)
	{
	    $this->_credentialSaltColumn = $credentialSaltColumn;

		return $this;
	}

	public function getTableName()
	{
		if( is_null($this->_credential) )
		{
			throw InvalidArgumentException('Tabela não informada');
		}
		
	    return $this->_tableName;
	}
	
	public function setTableName($tableName)
	{
	    $this->_tableName = $tableName;
		
	    return $this;
	}

	public function addInfoMessage( $message )
	{
		$this->_authenticateResultInfo['messages'][] = $message;

		return $this;
	}

	public function setInfoCode( $code )
	{
		$this->_authenticateResultInfo['code'] = $code;

		return $this;
	}

	public function getGlobalSalt()
	{
	    return $this->_globalSalt;
	}
	
	public function setGlobalSalt($globalSalt)
	{
	    $this->_globalSalt = $globalSalt;

		return $this;
	}

    public function geraHashSenha( $saltSenha )
    {
        $hashSenha = hash( 'sha512', $saltSenha . $this->getGlobalSalt() . $this->getCredential() );

        for( $i = 0; $i < 64000; $i++ )
        {
            $hashSenha = hash( 'sha512', $hashSenha );
        }

        return $hashSenha;
    }

	public function authenticate()
	{
		$this->_authenticateResultInfo['identity'] = $this->getIdentity();

		$db = $this->_zendDb;

		$query = $db->select()
					->from( $this->getTableName() )
					->where( 
						$this->getIdentityColumn() . ' = ? ' . $this->getCredentialTreatment(), 
						$this->getIdentity() 
					);
					
		$user = $db->fetchRow( $query );

		if( !isset($user[$this->getIdentityColumn()]) )
		{
			$this->setInfoCode(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND)
				->addInfoMessage('Usuário e/ou senha incorretos');

			return $this->_authenticateCreateAuthResult();
		}
		
		$user[$this->getCredentialSaltColumn()];
		$hash = $this->geraHashSenha( $user[$this->getCredentialSaltColumn()] );

		if( $hash !== $user[ $this->getCredentialColumn() ] )
		{
			$this->setInfoCode(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND)
				->addInfoMessage('Usuário e/ou senha incorretos');

			return $this->_authenticateCreateAuthResult();
		}
		
		$this->setInfoCode(Zend_Auth_Result::SUCCESS)
			->addInfoMessage('Sucesso');

		$this->_resultRow = $user;
		
		return $this->_authenticateCreateAuthResult();
	}
}