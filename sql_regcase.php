<?php

/**
 * Função para usar no lugar da sql_regcase() que foi depreciada no PHP 5.3. Depende da extensão mbstring.
 */
function mb_sql_regcase( $string, $encoding='auto' )
{
  	$max = mb_strlen( $string, $encoding );
	$ret = null;

  	for( $i = 0; $i < $max; $i++ )
	{
    	$char = mb_substr( $string, $i, 1, $encoding );
    	$up = mb_strtoupper( $char, $encoding );
    	$low = mb_strtolower( $char, $encoding );
    	$ret .= ( $up != $low )
				?'[' . $up . $low . ']' : $char;
  	}

  	return $ret;
} 
