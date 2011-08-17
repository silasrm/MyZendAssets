<?php

class CORE_Plugin_Formata extends Zend_Controller_Plugin_Abstract
{
    function preDispatch( Zend_Controller_Request_Abstract $request ){}

    public static function moedaToFloat( $valor )
    {
        return str_replace( ',', '.', str_replace( '.', '', $valor ) );
    }

    public static function floatToMoeda( $valor )
    {
        $currency = new Zend_Currency('pt_BR');

        return $currency->toCurrency( $valor );
    }

    public static function pointToComma( $valor )
    {
        return str_replace( '.', ',', $valor );
    }

    public static function commaToPoint( $valor )
    {
        return str_replace( ',', '.', $valor );
    }

    public static function dateSqlToBrasil( $data, $formato = 'yyyy-MM-dd', $formatoOut = 'dd/MM/yyyy' )
    {
        $data = str_replace(':000', '-300', $data );

        if( Zend_Date::isDate( $data, $formato ) )
        {
            $date = new Zend_Date( $data );

            return $date->get( $formatoOut );
        }

        return null;
    }

    public static function dateBrasilToSql( $data, $formato = 'dd/MM/yyyy', $formatoOut = 'yyyy-MM-dd' )
    {
        $data = str_replace(':000', '-300', $data );

        if( Zend_Date::isDate( $data, $formato ) )
        {
            $date = new Zend_Date( $data );

            return $date->get($formatoOut);
        }

        return null;
    }

    public static function cpfToString( $cpf )
    {
        return str_replace( '.', '', str_replace( '-', '', $cpf ) );
    }

    public static function stringToCpf( $string )
    {
        return substr( $string, 0, 3 ) . '.' . substr( $string, 3, 3 ) . '.' . substr( $string, 6, 3 ) . '-' . substr( $string, 9, 2 );
    }
}

?>
