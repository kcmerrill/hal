<?php

namespace kcmerrill\HAL;

class subscriber extends base {

    function connection() {
        return $this->content;
    }

    function token($value = NULL) {
        return is_null($value) ? $this->_id() : $this->_id($value);
    }

    function hasPermission($message, $system_channels = array()) {
        if(in_array(strtolower($message->channel()), $system_channels)) {
            $this->log->debug($this->_id() . ' has permission to send to ' . $message->channel());
            return true;
        } else {
            $channels = $this->channels();
            $this->log->debug('Subscriber channels: ', $channels);
            if($channels && is_array($channels)) {
                return in_array($message->channel(), $channels);
            } else {
                $this->log->debug('Subscriber ' . $this->_id() . ' does not have permission to send to ' . $message->channel());
                return false;
            }
        }
    }

    function authenticate($_id) {
        if($this->_id() && $this->_id() == $_id) {
            $this->log->debug($_id . ' already authenticated');
            return true;
        } else {
            $meta = $this->db->findOne(array('_id'=>$_id));
            if($meta) {
                $this->meta = $meta;
                $this->log->debug($_id . ' _id found.', $meta);
                return true;
            } else {
                $this->log->debug($_id . ' _id not found.');
                return false;
            }
        }
    }
}
