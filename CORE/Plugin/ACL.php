<?php
    class CORE_Plugin_ACL extends Zend_Controller_Plugin_Abstract
    {
        private $_auth;
        private $_acl;

        // Setando o modulo quando nao tem usuario logado do default
        private $_noauth = array( 
            'module' => 'default', 
            'controller' => 'auth', 
            'action' => 'index' 
        );
        // Setando o modulo quando nao tem permissao de acesso
        private $_noacl = array( 
            'module' => 'default', 
            'controller' => 'auth', 
            'action' => 'nao-autorizado' 
        );

        public function __construct()
        {
            $this->_auth = Zend_Auth::getInstance();
            $this->_acl = new Zend_Acl();

            // adiciona as roles
            $this->_acl
                ->addRole( new Zend_Acl_Role( 'guest' ) )
                ->addRole( new Zend_Acl_Role( 'admin' ) )
                ->addRole( new Zend_Acl_Role( 'parceiro' ) )
                ->addRole( new Zend_Acl_Role( 'user' ) )
                // adiciona o modulo default
                ->add( new Zend_Acl_Resource('default') )
                // adiciona os controllers do modulos default
                ->add(new Zend_Acl_Resource('default:index'), 'default')
                ->add(new Zend_Acl_Resource('default:auth'), 'default')
                ->add(new Zend_Acl_Resource('default:error'), 'default')
                ->add(new Zend_Acl_Resource('default:emprestimos'), 'default')
                ->add(new Zend_Acl_Resource('default:lojas'), 'default')
                ->add(new Zend_Acl_Resource('default:usuarios'), 'default')
                ->add(new Zend_Acl_Resource('default:relatorios'), 'default')
                // dá acesso completo ao módulo autenticacao para guest, user, parceiro e admin
                ->allow( 
                    array('guest', 'parceiro', 'user'), 
                    array('default:auth', 'default:error') 
                )
                ->allow( 'guest', 'default:emprestimos', array('upload') )
                ->allow( array('parceiro', 'user'), 'default:index' )
                ->allow( array('parceiro', 'user'), 'default:emprestimos', array(
                    'index',
                    'add',
                    'view',
                    'calcula'
                ) )
                ->allow( 'parceiro', 'default:emprestimos', array(
                    'change-status',
                    'calcula'
                ) )
                ->allow(array('parceiro','user'),'default:usuarios', array(
                    'selfedit'
                ))
                ->allow( 'admin', 'default' );

            Zend_Registry::set( 'acl', $this->_acl );
        }
        
        public function preDispatch( Zend_Controller_Request_Abstract $request )
        {
            // Auth
            $bOk = false;
            // Associa o perfil de visitante como default
            $role = 'guest';

            if( $request->getControllerName() != 'error' )
            {
                if( $this->_auth->setStorage(  new Zend_Auth_Storage_Session('user')  )->hasIdentity() )
                {
                    // Caso tenha, pega dados do usuario
                    $identity = (array)$this->_auth->setStorage(  new Zend_Auth_Storage_Session('user')  )->getIdentity();
                    
                    $perfis = array(
                        Zend_Registry::get( 'siteConfiguration' )->parceiro->perfil->id => 'parceiro',
                        Zend_Registry::get( 'siteConfiguration' )->admin->perfil->id => 'admin'
                    );
                    
                    $role = 'user';
                    if (array_key_exists($identity['perfil_id'], $perfis)) {
                        $role = $perfis[$identity['perfil_id']]; 
                    }
                    // pega o perfil do usuario logado
                }
                
                $controller = strtolower($request->controller);
                $action = strtolower($request->action);
                $module = strtolower($request->module);
                $resource = $module.':'.$controller;

                if( !$this->_acl->has( strtolower( $resource ) ) )
                {
                    $resource = null;
                }
                
                if( !$this->_acl->isAllowed( $role, $resource, $action ) )
                {
                    //Nao está logado, logo nao tem permissao
                    if( !$this->_auth->setStorage(  new Zend_Auth_Storage_Session('user')  )->hasIdentity() )
                    {
                        $module = $this->_noauth['module'];
                        $controller = $this->_noauth['controller'];
                        $action = $this->_noauth['action'];
                     }
                    else
                    {
                        // Está logado e nao tem permissao
                        $module = $this->_noacl['module'];
                        $controller = $this->_noacl['controller'];
                        $action = $this->_noacl['action'];
                    }

                    $bOk = true;
                }

                // Nao tem permissao, carrega o modulo de sem permissao ou sem usuario logado
                if( $bOk )
                {
                    $request->setModuleName($module);
                    $request->setControllerName($controller);
                    $request->setActionName($action);
                }
            }
        }
    }
?>
