<?php

    /**
     * Implement integration with PayPal API, support MassPay.
     */
    class CORE_Plugin_PayPal extends Zend_Controller_Plugin_Abstract
    {
        private $response;
        private $apiUsername;
        private $apiPassword;
        private $apiSignature;
        private $apiVersion = '51.0';
        private $environment = 'sandbox';
        private $urlBase = 'https://api-3t.sandbox.paypal.com/nvp';
        private $content;
        private $curlConfig;
        
        function preDispatch( Zend_Controller_Request_Abstract $request ){}

        /**
         *Execute MassPay action with data informed
         *
         * @param <array> $dataPayment
         * @param <string> $emailSubject
         * @param <string> $receiverType
         * @param <string> $currency
         * @return <string|boolean>
         */
        public function massPay( $dataPayment, $emailSubject, $receiverType = 'EmailAddress', $currency = "USD" ) {
            if( !is_array($dataPayment) )
                throw new InvalidArgumentException ( 'Payment data is not valid format.' );

            $prefix = $this->getConfig();
            $default = "&EMAILSUBJECT=" . urlencode($emailSubject) . "&RECEIVERTYPE=" . urlencode($receiverType) . "&CURRENCYCODE=" . urlencode($currency);

            $data = null;
            foreach( $dataPayment as $i => $payDestiny ) {
                if( array_key_exists( 'receiverEmail', $payDestiny ) )
                    $data .= '&L_EMAIL' . $i . '=' . urlencode($payDestiny['receiverEmail']);

                if( array_key_exists( 'uniqueID', $payDestiny ) )
                    $data .= '&L_UNIQUEID' . $i . '=' . urlencode($payDestiny['uniqueID']);
                
                $data .=  '&L_Amt' . $i . '=' . urlencode($payDestiny['amount']) . '&L_NOTE' . $i . '=' . urlencode($payDestiny['note']);
            }

            $this->content = $prefix . $default . $data;

            $this->execute();

            if( $this->isValid() )
                return $this->response;
            else
                return false;
        }

        /**
         *Verify if response is a Success.
         *
         * @return <boolean|exception>
         */
        public function isValid() {
            $response = explode('&', $this->response->getBody());

            $responseArray = array();
            foreach( $response as $v ) {
                $value = explode('=', $v);

                if( sizeof($value) > 1 ) {
			$responseArray[$value[0]] = urldecode($value[1]);
		}
            }

            if( ( 0 == sizeof($responseArray)) || !array_key_exists('ACK', $responseArray ) ) {
                throw new Exception( "Invalid HTTP Response for POST request( " . $this->content . " ) to " . $this->urlBase . ". Error detail: "  . print_r($responseArray, true) );
            }

            if( "SUCCESS" == strtoupper($responseArray["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($responseArray["ACK"]) )
                return true;
            else
                #throw new Exception( "Failed: " . print_r($responseArray, true) );
                throw new Exception( "[PayPal Error] Code: " . $responseArray['L_ERRORCODE0'] . ', Message: ' . $responseArray['L_LONGMESSAGE0'], $responseArray['L_ERRORCODE0'] );
        }

        /**
         *Get response in array format
         *
         * @return <array>
         */
        public function getResponseAsArray() {
            $response = explode('&', $this->response->getBody());

            $responseArray = array();
            foreach( $response as $v ) {
                $value = explode('=', $v);

                if( sizeof($value) > 1 ) {
			$responseArray[$value[0]] = urldecode($value[1]);
		}
            }

            return $responseArray;
        }

        /**
         * Set cUrl default config for Zend Http Client adapter cUrl
         */
        public function getCurlConfig() {
            $this->curlConfig = array( CURLOPT_VERBOSE => true
                                                        , CURLOPT_SSL_VERIFYPEER => false
                                                        , CURLOPT_SSL_VERIFYHOST => false
                                                        , CURLOPT_RETURNTRANSFER => true
                                                        , CURLOPT_POST => false
                                                        , CURLOPT_POSTFIELDS => $this->content );
        }

        /**
         *Set environment, sandbox is a default
         *
         * @param <string> $environment
         */
        public function setEnvironment( $environment ) {
            if( $environment != 'sandbox'
                && $environment != 'beta-sandbox'
                && $environment != 'live' )
                throw new InvalidArgumentException ( 'The environment is invalid! ' );
                
            $this->environment = $environment;

            if( $environment == 'sandbox' || $environment == 'beta-sandbox' )
                $this->urlBase = "https://api-3t." . $environment . ".paypal.com/nvp";
            else
                $this->urlBase = "https://api-3t.paypal.com/nvp";
        }

        /**
         *Set Signature API
         *
         * @param <string> $signature
         */
        public function setSignature( $signature ) {
            $this->apiSignature = $signature;
        }

        /**
         *Set Username API
         *
         * @param <string> $username
         */
        public function setUsername( $username ) {
            $this->apiUsername = $username;
        }

        /**
         *Set Password API
         *
         * @param <string> $password
         */
        public function setPassword( $password ) {
            $this->apiPassword = $password;
        }

        /**
         *Set Version API
         *
         * @param <string> $version
         */
        public function setVersion( $version ) {
            $this->apiVersion = $version;
        }

        /**
         *Set all API Config
         *
         * @param <string> $signature
         * @param <string> $username
         * @param <string> $password
         * @param <string> $version
         */
        public function setApiConfig( $signature, $username, $password, $version = '51.0' ) {
            $this->setSignature( $signature );
            $this->setUsername( $username );
            $this->setPassword( $password );
            $this->setVersion( $version );
        }

        /**
         *Get a string with authentication info and method of the resquet API
         *
         * @param <string> $method
         * @return <string>
         */
        private function getConfig( $method = 'MassPay' ) {
            if( is_null($this->apiUsername)
                || is_null($this->apiPassword)
                || is_null($this->apiSignature) )
                throw new LengthException( 'Config is not informed.' );

            return  "METHOD=" . $method . "&VERSION=" . $this->apiVersion . "&PWD=" . $this->apiPassword . "&USER=" . $this->apiUsername . "&SIGNATURE=" . $this->apiSignature;

        }

        /**
         *Execute de request to API
         *
         * @return <string>
         */
        private function execute() {
            $httpClientAdapter = new Zend_Http_Client_Adapter_Curl();
            $this->getCurlConfig();
            
            $httpClient = new Zend_Http_Client();
            $httpClient->setAdapter( $httpClientAdapter );
            $httpClient->setUri( $this->urlBase . '?' . $this->content );

            if( is_array( $this->curlConfig ) )
                $httpClient->setConfig( array( 'curloptions' => $this->curlConfig ) );

            $this->response = $httpClient->request( Zend_Http_Client::GET );
            return $this->response;
        }
    }