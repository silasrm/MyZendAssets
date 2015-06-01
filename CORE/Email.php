<?php

/**
 * Class for sent mail with Zend_Mail
 *
 * @author Silas Ribas <silasrm@gmail.com> github.com/silasrm
 * @version 0.2.4
 * @package CORE
 * @name Email
 * @example BOOTSTRAP
 * <code>
    $log = null;
    if( Zend_Registry::get('config')->core->email->log ) {
        $writer = new Zend_Log_Writer_Stream( Zend_Registry::get('config')->core->email->logPath );
        $log = new Zend_Log ( $writer );
    }

    CORE_Email::getInstance()
        ->config(
            array(
                'viewPath' =>Zend_Registry::get('config')->core->email->viewPath,
                'alertTo' =>Zend_Registry::get('config')->core->email->alertTo,
                'transport' => $transport,
                'log' => $log
            )
        )
    ;
    </code>
 *
 * @example MULTIPLES RECIPIENTS AND WITH TEMPLATE
    CORE_Email::getInstance()
        ->setTitle( 'Ola {{nome}}', true, array('nome' => 'Silas Ribas' ) )
        ->setData( array( 'xya' => 'LUPA UMPA!' ) )
        ->setTemplate( 'teste.phtml' )
        ->isHtml() // indicated this message is a html type
        ->addTo('mariadasilva999999@email.com.br')
        ->addTo('mariadasilva9999992@email.com.br')
        ->send();
 *
 * @example USING TEMPLATE BUT IS NOT A HTML MESSAGE, NOT PASSED A PATH FILE OF THE TEMPLATE CODE
    CORE_Email::getInstance()
        ->setTitle( 'Ola {{nome}}', true, array('nome' => 'Silas Ribas' ) )
        ->setData( array( 'xya' => 'LUPA UMPA!' ) )
        ->setTemplate( '<h2>{{nome}}</h2>', true )
        ->isHtml() // indicated this message is a html type
        ->send( 'mariadasilva9999992@email.com.br' );
 *
 * @example USING HTML CODE IN TEMPLATE, NOT PASSED A PATH FILE OF THE TEMPLATE CODE
    CORE_Email::getInstance()
        ->setTitle( 'Ola {{nome}}', true, array('nome' => 'Silas Ribas' ) )
        ->setData( array( 'xya' => 'LUPA UMPA!' ) )
        ->setTemplate( 'Hi {{nome}}.', true )
        ->send( 'mariadasilva9999992@email.com.br' );
 *
 * @example SINGLE RECIPIENT AND WITH TEXT MESSAGE ( NOT HTML MESSAGE )
    CORE_Email::getInstance()
        ->setTitle( 'Ola {{nome}}', true, array('nome' => 'Silas Ribas' ) )
        ->setMessage('Message for body e-mail')
        ->send( 'mariadasilva999999@email.com.br' ); // 1 recipient
 */
class CORE_Email
{
    /**
     * @var CORE_Email
     */
    protected static $_instance = null;

    /**
     * @var Zend_Mail
     */
    protected static $_instanceMail = null;

    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * @var string
     */
    protected $_alertTo = null;

    /**
     * @var Zend_Mail_Transport
     */
    protected $_transport = null;

    /**
     * @var Zend_Log
     */
    protected $_log = null;

    /**
     * @var array
     */
    protected $_data = null;

    /**
     * @var string
     */
    protected $_template = null;

    /**
     * @var string
     */
    protected $_message = null;

    /**
     * @var string
     */
    protected $_title = null;

    /**
     * If template value is not a file path and as a html code.
     * @var booleand
     */
    protected $_isCode = false;

    /**
     * @var int 1 = text, 2 = html
     */
    protected $_messageType = 1;

    public function __construct(){}
    public function __clone(){}

    /**
     *  Return a instance of this class
     * @return CORE_Email
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }

        if (null === self::$_instanceMail) {
            self::$_instanceMail = new Zend_Mail;
        }

        return self::$_instance;
    }

    /**
     *  Return a Zend_Mail instance
     *
     * @return Zend_Mail
     */
    public function getMail()
    {
        return self::$_instanceMail;
    }

    /**
     * Set a configuration of the class
     *
     * @param array $config
     * @return CORE_Email
     */
    public function config(array $config)
    {
        if (!is_array($config))
            throw new InvalidArgumentException('Configuration parameters is not a valid array.');

        if (!array_key_exists('viewPath', $config))
            throw new InvalidArgumentException('Configuration parameter "View Path" not exists.');

        if (empty($config['viewPath']))
            throw new InvalidArgumentException('"View Path" is not set.');

        if (!is_dir($config['viewPath']))
            throw new InvalidArgumentException('"View Path" is not set exists.');

        if (!array_key_exists('transport', $config))
            throw new InvalidArgumentException('Configuration parameter "Transport" not exists.');

        if (empty($config['transport']))
            throw new InvalidArgumentException('"Transport" is not set.');

        $this->_view = new Zend_View();
        $this->_view->setScriptPath($config['viewPath']);

        $this->_transport = $config['transport'];

        /**
         * For alert want a exception occurred
         */
        if (array_key_exists('alertTo', $config)
            && !empty($config['alertTo'])
        ) {
            $this->_alertTo = $config['alertTo'];
        }

        /**
         * For log want a exception occurred
         */
        if (array_key_exists('log', $config)
            && !empty($config['log'])
        ) {
            $this->_log = $config['log'];
        }

        return self::$_instance;
    }

    /**
     * Set data used in template
     * @param array $data
     * @return CORE_Email
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return self::$_instance;
    }

    /**
     * Set a template used in build a message
     * @param string $template
     * @param boolean $isCode
     * @return CORE_Email
     */
    public function setTemplate($template, $isCode = false)
    {
        if (!is_string($template))
            throw new InvalidArgumentException('Template is not a string/path.');

        $this->_template = $template;
        $this->_isCode = $isCode;

        return self::$_instance;
    }

    /**
     * Build a message
     *
     * @return CORE_Email
     */
    public function buildDataInTemplate()
    {
        if ($this->_isCode) {
            $codeTemplate = $this->_template;
        } else {
            $codeTemplate = $this->_view->partial(
                $this->_template,
                $this->_data
            );
        }

        $this->_message = $this->stringf($codeTemplate, $this->_data);

        return self::$_instance;
    }

    /**
     * Indicated a message sent is  a HTML
     * @return CORE_Email
     */
    public function isHtml()
    {
        $this->_messageType = 2;

        return self::$_instance;
    }

    /**
     * Change mark tags on template to value string
     * @url https://gist.github.com/822397 idea by @alganet
     *
     * @param string $template
     * @param array $vars
     * @return string
     */
    public function stringf($template, array $vars = array())
    {
        if (substr(PHP_VERSION, 0, 3) >= 5.3) {
            return preg_replace_callback(
                '/{{(\w+)}}/',
                function ($match) use (&$vars) {
                    return $vars[$match[1]];
                },
                $template
            );
        } else {
            return preg_replace_callback(
                '/{{(\w+)}}/',
                create_function('$match', 'return &$vars[$match[1]];'),
                $template
            );
        }
    }

    /**
     * Return a message in text or html
     *
     * @return string
     */
    public function getMessage()
    {
        if (!empty($this->_template)) {
            $this->buildDataInTemplate();
        }

        return $this->_message;
    }

    /**
     * Set a message text for sent a pure text email
     *
     * @param string $message
     * @return CORE_Email
     */
    public function setMessage($message)
    {
        if (!is_string($message)) {
            throw new InvalidArgumentException('Message is not a string.');
        }

        $this->_messageType = 1;
        $this->_message = $message;

        return self::$_instance;
    }

    /**
     * Set a Title/Subject of the email
     *
     * @param string $title
     * @param boolean $hasVariables
     * @param null|array $data
     * @return CORE_Email
     */
    public function setTitle($title, $hasVariables = false, $data = null)
    {
        if (!is_string($title)) {
            throw new InvalidArgumentException('Title is not a string.');
        }

        // clear subject in Zend_Mail
        $this->getMail()->clearSubject();

        $this->_title = $title;

        if ($hasVariables) {
            $this->_title = $this->stringf($this->_title, $data);
        }

        return self::$_instance;
    }

    /**
     * Set a name/email in TO mail field
     *
     * @param string $email
     * @param string $name
     * @return CORE_Email
     */
    public function addTo($email, $name = '')
    {
        $this->getMail()->addTo($email, $name);

        return self::$_instance;
    }

    /**
     * Set a name/email in CC mail field
     *
     * @param string $email
     * @param string $name
     * @return CORE_Email
     */
    public function addCc($email, $name = '')
    {
        $this->getMail()->addCc($email, $name);

        return self::$_instance;
    }

    /**
     * Set a email in BCC mail field
     *
     * @param string $email
     * @return CORE_Email
     */
    public function addBcc($email)
    {
        $this->getMail()->addBcc($email);

        return self::$_instance;
    }

    /**
     * Sent a email
     *
     * @param string|array $recipient
     * @return boolena|Exception
     * @throws Exception
     * @throws Zend_Log_Exception
     * @throws Zend_Mail_Exception
     */
    public function send($recipient = null)
    {
        if (!is_null($recipient)) {
            if (is_array($recipient)) {
                if (!empty($recipient['nome']) && !empty($recipient['email'])) {
                    $this->getMail()->addTo($recipient['email'], $recipient['nome']);
                } else {
                    throw new InvalidArgumentException('Recipient is invalid');
                }
            } else {
                if (!empty($recipient)) {
                    $this->getMail()->addTo($recipient);
                } else {
                    throw new InvalidArgumentException('Recipient is invalid');
                }
            }
        }

        $this->getMail()->setSubject(utf8_decode($this->_title));

        if ($this->_messageType == 1) {
            $this->getMail()->setBodyText(
                $this->getMessage()
                , 'UTF-8'
                , 'UTF-8'
            );
        } else {
            $this->getMail()->setBodyHtml(
                $this->getMessage()
                , 'UTF-8'
                , 'UTF-8'
            );
        }

        try {
            $this->getMail()->send($this->_transport);
            $this->getMail()->clearRecipients();

            return true;
        } catch (Exception $e) {
            // If alertTo is informed, sent email with alert and exception trace
            if (!is_null($this->_alertTo)) {
                $recipients = $this->getMail()->getRecipients();
                $mail = CORE_Email::getInstance()->getMail();
                $mail->clearRecipients()
                    ->addTo($this->_alertTo)
                    ->clearSubject()
                    ->setSubject('[Alert] Email not sent in ' . __FILE__)
                    ->setBodyHtml(
                        'Exception occurred in sent e-mail:<br /><br />' . $e->__toString()
                        . '<br />Data: ' . var_export(array(
                            'message' => $this->getMessage(),
                            'title' => $this->_title,
                            'recipients' => $recipients,
                        ), true),
                        'UTF-8',
                        'UTF-8'
                    )
                    ->setBodyText(
                        "Exception occurred in sent e-mail:\n\n" . $e->__toString()
                        . '<br />Data: ' . var_export(array(
                            'message' => $this->getMessage(),
                            'title' => $this->_title,
                            'recipients' => $recipients,
                        ), true),
                        'UTF-8',
                        'UTF-8'
                    )
                    ->send($this->_transport);
            }

            // If log is informed, log a exception trace
            if (!is_null($this->_log)) {
                $this->_log->log($e->__toString(), Zend_Log::CRIT);
            }

            throw new Exception($e->getMessage());
        }
    }
}
