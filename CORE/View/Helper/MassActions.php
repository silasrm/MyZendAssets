<?php

    class CORE_View_Helper_MassActions
    {
        public function massActions( $title, $optionLabel, $noActionSelectedLabel, array $actions, $extraCssClass = null )
        {
            $content = '<ul id="acoes-em-massa"' . ( $extraCssClass?' class="' . $extraCssClass . '"':null ) . '>';
            $content .= '   <li class="inicio">' . $title . '</li>
                            <li class="separador"></li>
                            <li>
                                <select name="mass_action" id="mass_action" rel="' . $noActionSelectedLabel . '">
                                    <option value="0">' . $optionLabel . '</option>';
            
            foreach( $actions as $action )
                $content .= '       <option value="' . $action['action'] . '" rel="' . $action['url'] . '">' . $action['text'] . '</option>';

            $content .= '       </select>
                            </li>
                            <li class="separador"></li>
                            <li class="fim">
                                <a href="javascript:;">OK</a>
                            </li>
                        </ul>';

            echo $content;
        }
    }