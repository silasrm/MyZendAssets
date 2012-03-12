<?php

	/**
	 * View Helper para por em extenso datetime no formato unixtime
	 * 
	 * @see Zend_Measure_Time
	 */
	class CORE_View_Helper_Relativetime
	{
		/**
		 * 
		 * @param $unixtime int
		 * @param $accuracy int
		 * @param $splitter string
		 * @return string
		 */
		public function relativetime($unixtime, $accuracy = 2, $splitter = ', ')
		{
			if (time() > $unixtime)
			{
				$unixtime = time() - $unixtime;
			}
			else
			{
				$unixtime = $unixtime - time();
			}

			$mt = new Zend_Measure_Time($unixtime);
			$units = $mt->getConversionList();

			$chunks = array(
				Zend_Measure_Time::YEAR,
				Zend_Measure_Time::WEEK,
				Zend_Measure_Time::DAY,
				Zend_Measure_Time::HOUR,
				Zend_Measure_Time::MINUTE,
				Zend_Measure_Time::SECOND
			);

			$translations = array(
				'year' => array('ano', 'anos'),
				'week' => array('semana', 'semanas'),
				'day' => array('dia', 'dias'),
				'h' => array('hora', 'horas'),
				'min' => array('minuto', 'minutos'),
				's' => array('segundo', 'segundos')
			);

			$measure = array();

			for($i=0; $i < count($chunks); $i++)
			{
				$chunk_seconds = $units[$chunks[$i]][0];

				if ($unixtime >= $chunk_seconds)
				{
					$measure[$units[$chunks[$i]][1]] = floor($unixtime / $chunk_seconds);
					$unixtime %= $chunk_seconds;
				}
			}

			$measure = array_slice($measure, 0, $accuracy, true);

			$str = '';
			foreach($measure as $key => $val)
			{
				$unit = $translations[$key];

				if($val == 1)
				{
					$unit = $unit[0];
				}
				else
				{
					$unit = $unit[1];
				}

				$str .= $val . ' ' . $unit . $splitter;
			}

			if( strlen(substr($str, 0, 0 - strlen($splitter))) > 0 )
				return substr($str, 0, 0 - strlen($splitter)) . ' atrás';
			else
				return '0 segundos atrás';

		}
	}