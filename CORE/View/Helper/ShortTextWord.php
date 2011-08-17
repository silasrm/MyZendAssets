<?php

    class CORE_View_Helper_ShortTextWord
    {
        public function shortTextWord( $text, $total = 40 ) {
            $_text = explode(' ', $text);
            $size = sizeof($_text);
            
            if( $size > $total ) {
                $_text = array_slice( $_text, 0, $total );
                $text = trim( implode(' ', $_text) ) . '...';
            }

            return $text;
        }
    }