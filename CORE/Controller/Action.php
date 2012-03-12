<?php

/**
* ControllerAction para ser usado de forma onde tudo 
* que seja compartilhado entre todos os Actions 
* possa ser adicionado aqui e os Controllers devem 
* extender essa classe.
*/
class CORE_Controller_Action extends Zend_Controller_Action
{
	protected $_data = null;
	protected $_auth = false;
	protected $flashMessenger = null;
    public $_infoUser = null;
    protected $_tipoPagina = 'normal';
    protected $_eUsuario = false;

	public function init()
	{
		$this->view->tipoPagina = $this->_tipoPagina;
		$this->flashMessenger = $this->_helper->FlashMessenger;
		$this->view->messages = $this->flashMessenger->getMessages();

		if ($this->_request->isPost()) {
			$this->_data = $this->_request->getPost();
			if (isset($this->_data['submit'])) {
				unset($this->_data['submit']);
			}
			if (isset($this->_data['Enviar'])) {
				unset($this->_data['Enviar']);
			}
		}
        
		$this->_auth = new Model_Auth();
		$this->view->auth = $this->_auth;

		if( ( $this->_request->getControllerName() == 'auth' )
			&& ( $this->_request->getActionName() == 'login' )
			&& ( $this->_auth->isLogado() === true ) ) {
			$this->_redirect('/');
		}

		$this->view->controllerName = $this->_request->getControllerName();

        // Se estiver logado
		if( $this->_auth->isLogado() === true ) {
	        $this->_infoUser = $this->_auth->getData();
	        $this->view->infoUser = $this->_infoUser;

	        if( $this->_infoUser->perfil_id == Zend_Registry::get( 'siteConfiguration' )->usuario->perfil->id )
	        {
				$this->_eUsuario = true;
				$this->view->eUsuario = $this->_eUsuario;
	        }
		}
	}
}
