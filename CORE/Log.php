<?php

/**
 * Classe para criação de log
 * Inicializacao no Bootstrap:
 * <code>
 * protected function _initLog() {
 *     $log = new CORE_Log( DATA_PATH . '/logs/' );
 *     $log->iniciaConfig(array('email.log', 'acessos_cursos.log'));
 *
 *     Zend_Registry::set('log', $log);
 * }
 * </code>
 */
class CORE_Log extends Zend_Log
{
    /**
     * Guarda o caminho da pasta dos logs
     * @var string
     */
    private $_path = null;

    /**
     * Guarda o destino padrão, para onde vão todas as mensagens
     * que estão sendo logadas.
     * @var string
     */
    private $_destinoPadrao = null;

    /**
     * Guarda a lista dos principais arquivos que devem ser criados
     * @var array
     */
    private $_arquivosPrincipais = array(
        'application.log',
        'atividades.log',
        'erros.log',
    );

    /**
     * Tamanho máximo para o arquivo de log
     * @var int
     */
    private $_maxSize = 5242880; // In bytes. 5MB

    /**
     * @param null|Zend_Log_Writer_Abstract $path
     * @param Zend_Log_Writer_Abstract $writer
     */
    public function __construct($path, Zend_Log_Writer_Abstract $writer = null)
    {
        $this->setPath($path);
        $this->setDestinoPadrao($this->getPath() . 'application.log');

        $this->iniciaConfig();

        parent::__construct($writer);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function setPath($path)
    {
        return $this->_path = $path;
    }

    /**
     * @return string
     */
    public function getDestinoPadrao()
    {
        return $this->_destinoPadrao;
    }

    /**
     * @param $destinoPadrao
     * @return mixed
     */
    public function setDestinoPadrao($destinoPadrao)
    {
        return $this->_destinoPadrao = $destinoPadrao;
    }

    /**
     * @return array
     */
    public function getArquivosPrincipais()
    {
        return $this->_arquivosPrincipais;
    }

    /**
     * @param $arquivosPrincipais
     * @return array
     */
    public function setArquivosPrincipais($arquivosPrincipais)
    {
        return $this->_arquivosPrincipais = array_merge($this->getArquivosPrincipais(), $arquivosPrincipais);
    }

    /**
     * Faz o log da mensagem
     * @param  string $mensagem
     * @param  int|constant $prioridade Zend_Log::NOTICE
     * @param  string $destino null
     * @param  mixed $extras Extra information to log in event
     * @return Zend_Log
     */
    public function log($mensagem, $prioridade = Zend_Log::NOTICE, $destino = null, $extras = null)
    {
        $this->_writers = array();
        if (is_null($destino)) {
            $destino = $this->getDestinoPadrao();
        } else {
            $destino = realpath(DATA_PATH . '/logs/' . $destino);
        }

        $this->_checaTamanhoArquivo($destino);

        $writer = new Zend_Log_Writer_Stream($destino);
        $this->addWriter($writer);

        parent::log($mensagem, $prioridade, $extras);

        return $this;
    }

    /**
     * @param $mensagem
     * @return Zend_Log
     */
    public function logErro($mensagem)
    {
        return $this->log(
            $mensagem,
            Zend_Log::ERR,
            'erros.log'
        );
    }

    /**
     * @param array $arquivosExtras
     * @return $this
     */
    public function iniciaConfig(array $arquivosExtras = null)
    {
        if (!is_null($arquivosExtras) && count($arquivosExtras) > 0) {
            $this->setArquivosPrincipais($arquivosExtras);
        }

        foreach ($this->_arquivosPrincipais as $arquivo) {
            $this->_criaArquivo($this->getPath() . $arquivo);
        }

        return $this;
    }

    /**
     * @param $arquivo
     * @return $this
     */
    private function _criaArquivo($arquivo)
    {
        if (!file_exists($arquivo)) {
            $fileInfo = pathinfo($arquivo);

            if (!is_dir($fileInfo['dirname'])) {
                mkdir($fileInfo['dirname'], 0755);
            }

            if (!is_writable($fileInfo['dirname'])) {
                chmod($fileInfo['dirname'], 0755);
            }

            file_put_contents($arquivo, '');

            chmod($arquivo, 0755);
        } else {
            if (!is_writable($arquivo)) {
                chmod($arquivo, 0755);
            }
        }

        return $this;
    }

    /**
     * @param $arquivo
     */
    private function _checaTamanhoArquivo($arquivo)
    {
        if (file_exists($arquivo)
            && filesize($arquivo) > $this->_maxSize
        ) {
            $fileInfo = pathinfo($arquivo);
            $nb = 1;
            $logfiles = scandir($fileInfo['dirname']);
            foreach ($logfiles as $file) {
                $tmpnb = substr($file, strlen($fileInfo['basename']));
                if ($nb < $tmpnb) {
                    $nb = $tmpnb;
                }
            }

            rename(
                $fileInfo['dirname'] . '/' . $fileInfo['basename'],
                $fileInfo['dirname'] . '/' . $fileInfo['basename'] . '.' . ($nb + 1)
            );
        }
    }
}