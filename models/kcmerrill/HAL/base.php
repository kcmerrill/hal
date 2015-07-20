<?php

namespace kcmerrill\HAL;

class base {

    protected $log;
    protected $db;
    protected $content = false;
    protected $meta = array();
    protected $type = false;

    function __construct($db, $log, $content = null) {
        $this->content = $content;
        $this->log = $log;
        /* Not sure how this will work in windows :/, do I care? Docker? Not sure yet ... */
        $class = explode('\\', get_class($this));
        $this->type = array_pop($class);
        $this->db = $db->selectCollection($this->type);
    }

    function has($key, $value) {
        if(isset($this->meta[$key])) {
            if(is_array($this->meta[$key])) {
                return in_array($value, $this->meta[$key]);
            } else if (!isset($this->meta[$key])) {
                return false;
            } else {
                return $this->meta[$key] == $value;
            }
        } else {
            /* Does not have this meta field/value combo */
            return false;
        }
    }

    function addTo($key, $value) {
        if(!isset($this->meta[$key])){
            $this->meta[$key] = array($value);
        } else {
            $this->meta[$key][] = $value;
            $this->meta[$key] = array_unique($this->meta[$key]);
        }
    }

    function removeFrom($key, $value) {
        if(isset($this->meta[$key]) && is_array($this->meta[$key])){
            $this->meta[$key] = array_diff($this->meta[$key], array($value));
        }
    }

    function remove($key) {
        if(isset($this->meta[$key])) {
            unset($this->meta[$key]);
        }
    }

    function save($generate_uuid = true) {
        if($generate_uuid) {
            $this->_id($this->_id() ? $this->_id() : $this->uuid());
        }
        $this->created_date($this->created_date() ? $this->created_date() : time());
        $this->updated_date(time());
        if($this->_id()) {
            $saved = $this->db->update(array('_id'=>$this->meta['_id']), $this->meta, array('upsert'=>true));
            $this->log->debug('Saving ' . $this->type . ' as _id ' . $this->_id(), $saved);
        } else {
            $created = $this->db->insert($this->meta);
            $this->log->debug('Creating ' . $this->type, $this->meta);
        }
    }

    function isPrivate() {
        return $this->private();
    }

    function toPublic($to_remove = array()) {
        $to_remove = is_string($to_remove) ? explode('|', $to_remove) : $to_remove;
        $public = $this->meta;
        foreach($public as $key=>$value) {
            if(substr($key, 1) == '_' || in_array($key, $to_remove)) {
                unset($public[$key]);
            }
        }
        return $public;
    }

    function toArray() {
        return $this->meta;
    }

    function toJson() {
        return json_encode($this->meta, JSON_NUMERIC_CHECK);
    }

    function __toString() {
        return json_encode($this->meta, JSON_NUMERIC_CHECK);
    }

    function import($values) {
        if(is_array($values)) {
            $this->meta = array_merge($this->meta, $values);
            return true;
        } else {
            $meta = $this->db->findOne(array('_id'=>$values));
            if($meta) {
                $this->meta = $meta;
                return true;
            } else {
                return false;
            }
        }
    }

    function reset() {
        $this->meta = array();
    }

    function uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    function __call($method, $params) {
        if(isset($params[0])) {
            $this->meta[$method] = $params[0];
        } else {
            return isset($this->meta[$method]) ? $this->meta[$method] : false;
        }
    }
}
