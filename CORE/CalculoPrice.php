<?php

/*
* Classe que faz o cálculo do valor da tabela Price.
* @see http://pastebin.com/jX3HExWQ
* @author Euller Cristian <eullercdr@gmail.com>
* @package CORE
* @name Core_CalculoPrice
* @version 0.1
*/
class Core_CalculoPrice
{
    protected static $_instance = null;

    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    function calculoPrice($valor, $parcelas, $juros)
    {
        $juros = bcdiv($juros, 100, 15);
        $e = 1.0;
        $cont = 1.0;

        for($k = 1; $k <= $parcelas; $k++)
        {
            $cont = bcmul($cont, bcadd($juros, 1, 15), 15);
            $e = bcadd($e, $cont, 15);
        }

        $e = bcsub($e, $cont, 15);
        $valor = bcmul($valor, $cont, 15);

        return bcdiv($valor, $e, 15);
    }
}