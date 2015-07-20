<?php
namespace kcmerrill\HAL;
/**
 * Very basic websocket client.
 * Supporting handshake from drafts:
 *  draft-hixie-thewebsocketprotocol-76
 *  draft-ietf-hybi-thewebsocketprotocol-00
 *
 * @author Simon Samtleben <web@lemmingzshadow.net>
 * @version 2011-09-15
 */

class client
{
    private $_Socket = null;

    public function __construct($host, $port)
    {
        $this->_connect($host, $port);
    }

    public function __destruct()
    {
        $this->_disconnect();
    }

    public function sendData($data, $tries = 10)
    {
        // send actual data:
        if(fwrite($this->_Socket, "\x00" . $data . "\xff" ) === false) {
            if($tries <= 0) {
                return false;
            } else {
                /* Bleh, lets try to resend it */
                return $this->sendData($data, $tries--);
            }
        } else {
            return true;
        }
    }

    private function _connect($host, $port, $tries = 10)
    {
        $key1 = $this->_generateRandomString(32);
        $key2 = $this->_generateRandomString(32);
        $key3 = $this->_generateRandomString(8, false, true);

        $header = "GET /echo HTTP/1.1\r\n";
        $header.= "Upgrade: WebSocket\r\n";
        $header.= "Connection: Upgrade\r\n";
        $header.= "Host: ".$host.":".$port."\r\n";
        $header.= "Origin: http://" .  $_SERVER['SERVER_NAME'] . "\r\n";
        $header.= "Sec-WebSocket-Key1: " . $key1 . "\r\n";
        $header.= "Sec-WebSocket-Key2: " . $key2 . "\r\n";
        $header.= "\r\n";
        $header.= $key3;


        $this->_Socket = fsockopen($host, $port, $errno, $errstr, 2);
        if(fwrite($this->_Socket, $header) === false) {
            /* Bleh, lets try to resend it */
            if($tries <= 0) {
                return $this->_connect($host, $port, $tries--);
            }
        } else {
            return true;
        }
    }

    private function _disconnect()
    {
        fclose($this->_Socket);
    }

    private function _generateRandomString($length = 10, $addSpaces = true, $addNumbers = true)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        $useChars = array();
        // select some random chars:
        for($i = 0; $i < $length; $i++)
        {
            $useChars[] = $characters[mt_rand(0, strlen($characters)-1)];
        }
        // add spaces and numbers:
        if($addSpaces === true)
        {
            array_push($useChars, ' ', ' ', ' ', ' ', ' ', ' ');
        }
        if($addNumbers === true)
        {
            array_push($useChars, rand(0,9), rand(0,9), rand(0,9));
        }
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, $length);
        return $randomString;
    }
}

