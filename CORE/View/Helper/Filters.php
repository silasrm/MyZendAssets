<?php

    class CORE_View_Helper_Filters
    {
        public function filters( array $filters, $extraCssClass = null )
        {
            $content = '<ul id="filtros"' . ( $extraCssClass?' class="' . $extraCssClass . '"':null ) . '>';
            
            foreach( $filters as $key => $filter )
            {
                switch( $key )
                {
                    case 0:
                            $content .= '<li class="inicio' . ( $filter['active']?' active':null ) . '">
                                                    <a href="' . $filter['url'] . '" title="' . $filter['title'] . '">
                                                        ' . $filter['text'] . '
                                                    </a>
                                                </li>
                                                <li class="separador"></li>';
                        break;
                    case ( count($filters) - 1 ):
                            $content .= '<li class="fim' . ( $filter['active']?' active':null ) . '">
                                                    <a href="' . $filter['url'] . '" title="' . $filter['title'] . '">
                                                        ' . $filter['text'] . '
                                                    </a>
                                                </li>';
                        break;
                    default:
                            $content .= '<li' . ( $filter['active']?' class="active"':null ) . '>
                                                    <a href="' . $filter['url'] . '" title="' . $filter['title'] . '">
                                                        ' . $filter['text'] . '
                                                    </a>
                                                </li>
                                                <li class="separador"></li>';
                        break;
                }
            }

            $content .= '</ul>';

            echo $content;
        }
    }