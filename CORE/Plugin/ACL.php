<?php
    class CORE_Plugin_ACL extends Zend_Controller_Plugin_Abstract
    {
        private $_auth;
        private $_acl;

        // Setando o modulo quando nao tem usuario logado do default
        private $_noauth = array( 'module' => 'default', 'controller' => 'auth', 'action' => 'index' );
        // Setando o modulo quando nao tem usuario logado
        private $_noauthadmin = array( 'module' => 'admin', 'controller' => 'auth', 'action' => 'index' );
        // Setando o modulo quando nao tem permissao de acesso
        private $_noacl = array( 'module' => 'default', 'controller' => 'auth', 'action' => 'nao-autorizado' );
        // Setando o modulo quando nao tem permissao de acesso
        private $_noacladmin = array( 'module' => 'admin', 'controller' => 'auth', 'action' => 'nao-autorizado' );

        public function __construct()
        {
            $this->_auth = Zend_Auth::getInstance();
            $this->_acl = new Zend_Acl();

            // adiciona as roles
            $this->_acl->addRole( new Zend_Acl_Role( 'guest' ) )
                                ->addRole( new Zend_Acl_Role( 'user' ) )
                                ->addRole( new Zend_Acl_Role( 'admin' ) )
                                // adiciona o modulo default
                                ->add( new Zend_Acl_Resource('default') )
                                // adiciona os controllers do modulos default
                                ->add(new Zend_Acl_Resource('default:index'), 'default')
                                ->add(new Zend_Acl_Resource('default:auth'), 'default')
                                ->add(new Zend_Acl_Resource('default:register'), 'default')
                                ->add(new Zend_Acl_Resource('default:user'), 'default')
                                ->add(new Zend_Acl_Resource('default:error'), 'default')
                                // adiciona o modulo admin
                                ->add( new Zend_Acl_Resource('admin') )
                                // adiciona os controllers do modulos admin
                                ->add(new Zend_Acl_Resource('admin:index'), 'admin')
                                ->add(new Zend_Acl_Resource('admin:auth'), 'admin')
                                ->add(new Zend_Acl_Resource('admin:users'), 'admin')
                                ->add(new Zend_Acl_Resource('admin:error'), 'admin')
                                // dá acesso completo ao módulo autenticacao para guest, aluno e admin
                                ->allow( 'guest', 'default:auth' )
                                ->allow( 'guest', 'default:register' )
                                ->allow( 'guest', 'admin:auth' )
                                ->allow( 'guest', 'default:error' )
                                ->allow( 'guest', 'admin:error' )
                                ->allow( 'user', 'default' )
                                ->allow( 'admin', 'admin' );
        }
        
        public function preDispatch( Zend_Controller_Request_Abstract $request )
        {
            // Auth
            $bOk = false;
            // Associa o perfil de visitante como default
            $role = 'guest';

            if( $request->getModuleName() == 'admin' )
            {
                 if( $this->_auth->setStorage(  new Zend_Auth_Storage_Session('admin')  )->hasIdentity() )
                {
                    // Caso tenha, pega dados do usuario
                    $identity = (array)$this->_auth->setStorage(  new Zend_Auth_Storage_Session('admin')  )->getIdentity();

                    // pega o perfil do usuario logado
                    $role = 'admin';
                }
            }
            else
            {
                if( $this->_auth->setStorage(  new Zend_Auth_Storage_Session('user')  )->hasIdentity() )
                {
                    // Caso tenha, pega dados do usuario
                    $identity = (array)$this->_auth->setStorage(  new Zend_Auth_Storage_Session('user')  )->getIdentity();

                    // pega o perfil do usuario logado
                    $role = 'user';
                }
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
                if( $request->getModuleName() == 'admin' ) // admin
                {
                    //Nao está logado, logo nao tem permissao
                    if( !$this->_auth->setStorage(  new Zend_Auth_Storage_Session('admin')  )->hasIdentity() )
                    {
                        $module = $this->_noauthadmin['module'];
                        $controller = $this->_noauthadmin['controller'];
                        $action = $this->_noauthadmin['action'];
                     }
                    else
                    {
                        // Está logado e nao tem permissao
                        $module = $this->_noacladmin['module'];
                        $controller = $this->_noacladmin['controller'];
                        $action = $this->_noacladmin['action'];
                    }
                }
                else // usuario normal
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
                }

                $bOk = true;
            }

            // Nao tem permissao, redireciona para o modulo de sem permissao ou sem usuario logado
            if( $bOk )
            {
                // seta a action
                $request->setModuleName($module);
                $request->setControllerName($controller);
                $request->setActionName($action);
            }
        }
    }
?>
