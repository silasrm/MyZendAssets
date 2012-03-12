<?php

    class CORE_View_Helper_ShortText
    {
        public function shortText( $text, $total = 260 ) {
            $size = strlen($text);

            if( $size > $total )
                $text = trim( substr( $text, 0, $total ) ) . '...';

            return $text;
        }
    }