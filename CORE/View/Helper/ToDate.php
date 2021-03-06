<?php

class CORE_View_Helper_ToDate
{
	public function toDate(
		$date,
		$formatOut = 'dd/MM/yyyy HH:mm:ss',
		$formatIn = 'yyyy-MM-dd HH:mm:ss',
		$dataInversa = false
	)
	{
		if( !$dataInversa )
		{
			if( Zend_Date::isDate( $date, $formatIn ) )
			{
				$zDate = new Zend_Date();
				return $zDate->set( $date, $formatIn )
							  ->get( $formatOut );
			}
		}
		else
		{
			$novaDtNascimento = explode($formatIn, $date);
			$novaDtNascimento = array_reverse( $novaDtNascimento );
			$novaDtNascimento = implode($formatOut, $novaDtNascimento);

			return $novaDtNascimento;
		}
	}
}