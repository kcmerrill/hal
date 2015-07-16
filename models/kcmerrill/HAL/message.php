<?php

namespace kcmerrill\HAL;

class message extends base {
    var $msg;

    function __construct($db, $log, $msg){
        parent::__construct($db, $log, $msg);
        $this->meta = $this->decode($msg);
        /* Set some defaults */
        $this->sent(time());
    }

    function valid() {
        return
            isset($this->meta['channel']) &&
            isset($this->meta['action']) &&
            isset($this->meta['signature']);
    }

    function decode($msg){
        $msg = trim(stripslashes(trim($msg, "\r\n")));
        $msg = json_decode($msg, TRUE);
        $msg = is_array($msg) ? $msg : array();
        if(isset($msg['to'])) {
            if(stristr($msg['to'] , '.')) {
                list($msg['channel'], $msg['action']) = explode('.', $msg['to'], 2);
            } else {
                $msg['channel'] = $msg['to'];
                $msg['action'] = '_default';
            }
        }
        return $msg;
    }
}
