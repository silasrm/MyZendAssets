<?php

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

echo mb_sql_regcase('silas');
echo '<hr/>';
echo sql_regcase('silas');
