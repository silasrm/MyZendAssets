<?php

class CORE_View_Helper_ShortText
{
	public function shortText( $text, $total = 260, $cortarPalavras = true )
	{
		$size = strlen($text);

		if( $size > $total )
		{
			if( $cortaPalavras )
			{
				$text = trim( substr( $text, 0, $total ) ) . '...';
			}
			else
			{
				$_text = explode(' ', $text);

				$textNew = null;
				$i = 0;
				while( $textNewPart = array_shift($_text) )
				{
					if( strlen($textNew) > $total )
					{
						break;
					}

					$textNew .= $textNewPart . ' ';
				}

				$text = trim($textNew) . '...';
			}
		}

		return $text;
	}
}