<?php

class Curl {

    private $_curl;
    private $_protocol;
    private $_host;
    private $_port;

    public function __construct($protocol, $host, $port, $username = false, $password = false) {

        $this->_protocol  = $protocol;
        $this->_host      = $host;
        $this->_port      = $port;

        $this->_curl      = curl_init();


        if ($port == 4002 && $username && $password) {
            $headers = [
                'Content-Type: application/json',
                sprintf('Authorization: Basic %s', base64_encode($username . ':' . $password)),
            ];
        } else {
            $headers = [
                'Content-Type: application/json',
            ];
        }

        curl_setopt_array($this->_curl, [CURLOPT_RETURNTRANSFER => true,
                                         CURLOPT_FOLLOWLOCATION => true,
                                         CURLOPT_FRESH_CONNECT  => true,
                                         //CURLOPT_VERBOSE        => true,
                                         CURLOPT_HTTPHEADER     => $headers,
                                        ]);
    }

    public function __destruct() {
        curl_close($this->_curl);
    }

    public function sanitize($string, $lowercase = false) {

        // Add spaces after separating items
        $string = str_ireplace(['<br/>', '<br>', '<br />', '</p>', '<hr>', '<hr />', '<hr/>', "\t", "\r", "\f"], "\n ", $string);

        // Decode htmlentities
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

        // Remove all tags
        $string = strip_tags($string);

        // Encode htmlentities
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');

        // Replace encoded spaces
        $string = str_ireplace(['&nbsp;'], " ", $string);

        // Remove all symbols except required
        $string = preg_replace("/[^\d@%\{\}\?\(\)\/\-\=\|:&#;,.\+\w\n ]/ui", "", $string);

        // Remove not ASCII characters from a string
        $string = preg_replace("/[[:^print:]]/ui", " ", $string);

        // Remove double \n
        $string = preg_replace("/(\n[ ]?)+/ui", "\n", $string);

        // Remove double spaces
        $string = preg_replace("/ +/ui", " ", $string);

        // Trim spaces
        $string = trim($string);

        // Trim -
        $string = trim($string, '-');

        // Trim _
        $string = trim($string, '_');

        // Lowercase
        if ($lowercase) {
            $string = mb_strtolower($string, 'UTF-8');
        }

        return $string;
    }

    protected function prepare($uri, $method, $timeout = 30, array $postfields = []) {

        curl_setopt($this->_curl, CURLOPT_URL, $this->_protocol . '://' . $this->_host . ':' . $this->_port . $uri);
        curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($this->_curl, CURLOPT_TIMEOUT, $timeout);

        if ($method == 'POST' && $postfields) {
            curl_setopt($this->_curl, CURLOPT_POSTFIELDS, json_encode($postfields));
        }
    }

    protected function execute($json = true) {

        $response    = curl_exec($this->_curl);
        $errorNumber = curl_errno($this->_curl);
        $errorText   = curl_error($this->_curl);

        if ($errorNumber > 0) {
            return false;
        }

        if ($response) {
            if ($json) {
                return json_decode($response, true);
            } else {
                return $response;
            }
        }

        return false;
    }
}
