<?php

	class CORE_View_Helper_GetFoto
	{
		public function getFoto( $candidato_id )
		{
            $foto = '/images/foto.jpg';
            if( file_exists( PUBLIC_PATH . '/assets/fotos/' . $candidato_id . '.jpg' ) )
            {
                $foto = '/assets/fotos/' . $candidato_id . '.jpg';
            }

            return $foto;
		}
	}