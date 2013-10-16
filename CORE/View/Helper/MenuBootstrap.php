<?php

/**
 * Gerando o menu apartir de um objeto de Zend_View_Helper_Navigation
 *
 * @author Silas Ribas <silasrm@gmail.com>
 */
class CORE_View_Helper_MenuBootstrap
{
    /**
     * @param Zend_View_Helper_Navigation $navigation The navigation container.
     *
     * @return string HTML
     */
    public function menuBootstrap($navigation, $cssClass = null, $type = 'normal')
    {
        if( $type == 'navlist' )
        {
            return $this->_navList( $navigation, $cssClass );
        }
        else if( $type == 'stacked' )
        {
            return $this->_stacked( $navigation, $cssClass );
        }

        return $this->_normal( $navigation, $cssClass );
    }

    protected function _normal($navigation, $cssClass = null)
    {
        $html = array('<ul class="nav ' . $cssClass . '">');

        foreach($navigation->getContainer() as $page)
        {
            // visibility of the page
            if (!$page->isVisible()) {
                continue;
            }

            // dropdown
            $dropdown = !empty($page->pages);

            $liClass = array();

            if( $page->isActive() )
            {
                $liClass[] = 'active';
            }

            if( $dropdown )
            {
                $liClass[] = 'dropdown';
            }

            $htmlSubpaginas = array('<ul class="dropdown-menu">');

            $countInativas = 0;
            $algumAtivo = false;
            $existeSubmenu = false;
            foreach( $page->pages as $subpage )
            {
                if( $subpage->isActive() )
                {
                    $algumAtivo = true;
                }

                $algumAtivo = $this->checaAtivo($subpage);

                // visibility of the sub-page
                if (!$subpage->isVisible()) {
                    $countInativas++;
                    continue;
                }

                if( !Zend_Registry::get('acl')->isAllowed( Zend_Registry::get('role'), $subpage->getResource(), $subpage->getPrivilege() ) ) {
                    $countInativas++;
                    continue;
                }
                else
                {
                    $existeSubmenu = true;
                }

                $htmlSubpaginas[] = '<li' . ($subpage->isActive() ? ' class="active"' : '') . '>';
                $htmlSubpaginas[] = '<a href="' . $subpage->getHref() . '">';

                if ($subpage->get('icon')) {
                    $htmlSubpaginas[] = '<i class="icon-' . $subpage->get('icon') . '"></i>';
                }

                $htmlSubpaginas[] = $subpage->getLabel();
                $htmlSubpaginas[] = "</a>";
                $htmlSubpaginas[] = "</li>";
            }

            $htmlSubpaginas[] = "</ul>";

            if( !$existeSubmenu && !Zend_Registry::get('acl')->isAllowed( Zend_Registry::get('role'), $page->getResource(), $page->getPrivilege() ) ) {
                $countInativas++;
                continue;
            }

            if( count($page->pages) == $countInativas )
            {
                $dropdown = false;
                $htmlSubpaginas = array();
            }

            if( $algumAtivo )
            {
                $liClass[] = 'active';
            }

            // header
            $html[] = '<li' . (( count($liClass) > 0 ) ? ' class="' . implode(' ', $liClass) . '"' : '') . '>';
            $htmlPagina = array('<a href="' . ($dropdown ? '#' : $page->getHref()) . '" '
                    . ($dropdown ? 'class="dropdown-toggle" data-toggle="dropdown"': null)
                    . '>');
            $htmlPagina[] = $page->getLabel();

            if ($dropdown) {
                $htmlPagina[] = '<b class="caret"></b>';
            }

            $htmlPagina[] = '</a>';

            $html = array_merge( $html, $htmlPagina );
            $html = array_merge( $html, $htmlSubpaginas );

            $html[] = "</li>";
        }

        $html[] = '</ul>';

        return join(PHP_EOL, $html);
    }

    protected function _navList($navigation, $cssClass = null)
    {
        $html = array('<ul class="nav ' . $cssClass . '">');

        foreach($navigation->getContainer() as $page)
        {
            // visibility of the page
            if (!$page->isVisible()) {
                continue;
            }

            // dropdown
            $header = !empty($page->pages);

            $liClass = array();

            if( $page->isActive() )
            {
                $liClass[] = 'active';
            }

            if( $header )
            {
                $liClass['nav-header'] = 'nav-header';
            }

            $htmlSubpaginas = null;//array('<ul class="dropdown-menu">');

            $countInativas = 0;
            $algumAtivo = false;
            $existeSubmenu = false;
            foreach ($page->pages as $subpage) {
                if( $subpage->isActive() )
                {
                    $algumAtivo = true;
                }

                $algumAtivo = $this->checaAtivo($subpage);

                // visibility of the sub-page
                if (!$subpage->isVisible()) {
                    $countInativas++;
                    continue;
                }

                if( !Zend_Registry::get('acl')->isAllowed( Zend_Registry::get('role'), $subpage->getResource(), $subpage->getPrivilege() ) ) {
                    $countInativas++;
                    continue;
                }
                else
                {
                    $existeSubmenu = true;
                }

                $htmlSubpaginas[] = '<li' . ($subpage->isActive() ? ' class="submenu active"' : ' class="submenu"') . '>';
                $htmlSubpaginas[] = '<a href="' . $subpage->getHref() . '">';

                if ($subpage->get('icon')) {
                    $htmlSubpaginas[] = '<i class="icon-' . $subpage->get('icon') . '"></i>';
                }

                $htmlSubpaginas[] = $subpage->getLabel();
                $htmlSubpaginas[] = "</a>";
                $htmlSubpaginas[] = "</li>";
            }

            //$htmlSubpaginas[] = "</ul>";

            if( !$existeSubmenu && !Zend_Registry::get('acl')->isAllowed( Zend_Registry::get('role'), $page->getResource(), $page->getPrivilege() ) ) {
                $countInativas++;
                continue;
            }

            if( count($page->pages) == $countInativas )
            {
                $header = false;
                $htmlSubpaginas = array();
            }

            if( !$header )
            {
                unset($liClass['nav-header']);
            }

            if( $algumAtivo )
            {
                $liClass[] = 'active';
            }

            // header
            $html[] = '<li' . (( count($liClass) > 0 ) ? ' class="' . implode(' ', $liClass) . '"' : '') . '>';

            if( $header )
            {
                $htmlPagina = array($page->getLabel());
            }
            else
            {
                $htmlPagina = array('<a href="' . $page->getHref() . '" >');
                $htmlPagina[] = $page->getLabel();

                $htmlPagina[] = '</a>';
            }

            $html = array_merge( $html, $htmlPagina );

            $html[] = "</li>";

            $html = array_merge( $html, $htmlSubpaginas );
        }

        $html[] = '</ul>';

        return join(PHP_EOL, $html);
    }

    protected function _stacked($navigation, $cssClass = null)
    {
        $html = array('<ul class="nav ' . $cssClass . '">');

        foreach($navigation->getContainer() as $page)
        {
            // visibility of the page
            if (!$page->isVisible()) {
                continue;
            }

            // dropdown
            $dropdown = !empty($page->pages);

            $liClass = array();

            if( $page->isActive() )
            {
                $liClass[] = 'active';
            }

            if( $dropdown )
            {
                $liClass[] = '';
            }

            $htmlSubpaginas = array('<ul class="nav nav-stacked">');

            $countInativas = 0;
            $algumAtivo = false;
            $existeSubmenu = false;
            foreach( $page->pages as $subpage )
            {
                if( $subpage->isActive() )
                {
                    $algumAtivo = true;

                    $htmlSubpaginas[0] = '<ul class="nav nav-stacked in">';
                }

                $algumAtivo = $this->checaAtivo($subpage);

                // visibility of the sub-page
                if (!$subpage->isVisible()) {
                    $countInativas++;
                    continue;
                }

                if( !Zend_Registry::get('acl')->isAllowed( Zend_Registry::get('role'), $subpage->getResource(), $subpage->getPrivilege() ) ) {
                    $countInativas++;
                    continue;
                }
                else
                {
                    $existeSubmenu = true;
                }

                $htmlSubpaginas[] = '<li' . ($subpage->isActive() ? ' class="active"' : '') . '>';
                $htmlSubpaginas[] = '<a href="' . $subpage->getHref() . '">';

                if ($subpage->get('icon')) {
                    $htmlSubpaginas[] = '<i class="icon-' . $subpage->get('icon') . '"></i>';
                }

                $htmlSubpaginas[] = $subpage->getLabel();
                $htmlSubpaginas[] = "</a>";
                $htmlSubpaginas[] = "</li>";
            }

            $htmlSubpaginas[] = "</ul>";

            if( !$existeSubmenu && !Zend_Registry::get('acl')->isAllowed( Zend_Registry::get('role'), $page->getResource(), $page->getPrivilege() ) ) {
                $countInativas++;
                continue;
            }

            if( count($page->pages) == $countInativas )
            {
                $dropdown = false;
                $htmlSubpaginas = array();
            }

            if( $algumAtivo )
            {
                $liClass[] = 'active';
            }

            // header
            $html[] = '<li' . (( count($liClass) > 0 ) ? ' class="' . implode(' ', $liClass) . '"' : '') . '>';
            $htmlPagina = array('<a href="' . ($dropdown ? '#' : $page->getHref()) . '" '
                    . ($dropdown ? 'class="dropdown-collapse"': null)
                    . '>');
            $htmlPagina[] = '<span>' . $page->getLabel() . '</span>';

            if ($dropdown) {
                $htmlPagina[] = "<i class='icon-angle-down angle-down'></i>";
            }

            $htmlPagina[] = '</a>';

            $html = array_merge( $html, $htmlPagina );
            $html = array_merge( $html, $htmlSubpaginas );

            $html[] = "</li>";
        }

        $html[] = '</ul>';

        return join(PHP_EOL, $html);
    }

    public function checaAtivo($subpage)
    {
        $algumAtivo = false;

        if(!empty($subpage->pages))
        {
            foreach( $subpage->pages as $ssubpage )
            {
                $algumAtivo = $this->checaAtivo($ssubpage);
            }
        }
        else
        {
            $algumAtivo = $subpage->isActive();
        }

        return $algumAtivo;
    }
}