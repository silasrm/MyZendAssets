<?php

class CORE_Plugin_Counter extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch( Zend_Controller_Request_Abstract $request )
    {
        $cache = Zend_Registry::get('cache');
        $cache->setLifetime(300); // 5min
        //$cache->clean();
        if (($total = $cache->load('counter')) === false) {
            $db = new Model_Chamado();
            $naoLidos = $db->counter('n');
            $aguardandoEstimativa = $db->counter('aguardandoEstimativa');
            $estimadosTrabalhando = $db->counter('estimadosTrabalhando');
            $venceHoje = $db->counter('h');
            $vencidos = $db->counter('vencidos');

            //seta a quantidade de não lidos/branco
            //seta a quantidade de chamados com status Em Análise sem estimativa/Amarelo
            //seta a quantidade de chamados q vencem na data atual/laranja
            //seta a quantidade de chamados q vencidos/vermelho

            /*
            $total =  array(
                'white'=> $naoLidos['total'],
                'yellow'=> $aguardandoEstimativa['total'],
                'orange'=> $venceHoje['total'],
                'red'=> $vencidos['total'],
            );
            */
            $total =  array(
                'white'=> count($naoLidos),
                'yellow'=> count($aguardandoEstimativa),
                'gray'=> count($estimadosTrabalhando),
                'orange'=> count($venceHoje),
                'red'=> count($vencidos)
            );

            $totalColecao =  array(
                'white'=> $naoLidos,
                'yellow'=> $aguardandoEstimativa,
                'gray'=> $estimadosTrabalhando,
                'orange'=> $venceHoje,
                'red'=> $vencidos
            );
            
            $cache->save($total, 'counter'); 
            $cache->save($totalColecao, 'counterColecao'); 
        } else {
            $totalColecao = $cache->load('counterColecao');
        }

        Zend_Registry::set('counter', $total);
        Zend_Registry::set('counterColecao', $totalColecao);
    }
}