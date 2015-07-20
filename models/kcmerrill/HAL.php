<?php

namespace kcmerrill;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use kcmerrill\HAL\command as command;

class HAL implements MessageComponentInterface {

    var $log;
    var $db;
    var $subscribers = array();
    var $operator;
    var $system_channels = array('h', '_','hal');
    var $config;

    public function __construct($log, $db, $config, $operator = 'Dave') {
        $this->log = $log;
        $this->db = $db;
        $this->config = $config;
        $this->operator = ucwords($operator) ?: 'Dave';
        $this->log->HAL('Hello ' . $this->operator . '.');
        $this->log->HAL("I'm completely operational, and all my circuits are functioning perfectly.");
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->log->info('A new connection.', $conn);
        $this->subscribers[$conn->resourceId] = new \kcmerrill\HAL\subscriber($this->db, $this->log, $conn);
        $this->subscribers[$conn->resourceId]->ip($conn->remoteAddress);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $message = new \kcmerrill\HAL\message($this->db, $this->log, $msg);
        if($message->valid() && $this->subscribers[$from->resourceId]->authenticate($message->signature()) && $this->subscribers[$from->resourceId]->hasPermission($message, $this->system_channels)) {
            $this->log->info('Message from ' . $this->subscribers[$from->resourceId]->token() . ' to ' . $message->channel() . ' is valid.', $message->toPublic());
            /* Set the from on the message */
            $message->from($this->subscribers[$from->resourceId]->toPublic('channels'));
            if($this->config->get('hal.messages.save', true)){
                /* Save it for history sake */
                $message->save(false);
            }
            if(!$message->confirm() && $this->config->get('hal.messages.confirm', false)) {
                /* Should we send a confirmation message? */
                $message->confirm(true);
            }
            if(in_array(strtolower($message->channel()), $this->system_channels)) {
                /* System channels would go here */
                $command = new command($this->db, $this->log, $message);
                return true;
            }
            /* Touch the subscriber(updated_date) */
            $this->subscribers[$from->resourceId]->save();
            $this->log->HAL("Transmitting message. {$this->subscribers[$from->resourceId]->_id()}->{$message->to()}", $from);
            $this->log->info("Transmitting message. {$this->subscribers[$from->resourceId]->_id()}->{$message->to()}", $message->toArray());
            /* Cycle through each subscriber, if they have the channel() then send it to them! */
            foreach($this->subscribers as $r_id=>$s) {
                if($this->subscribers[$r_id]->has('channels', $message->channel())){
                    /* Did the subscriber that sent the message, want a confirmation? */
                    if($this->subscribers[$r_id]->connection() == $from && !$message->confirm()) {
                        continue;
                    }
                    $this->subscribers[$r_id]->connection()->send(json_encode($message->toPublic(), JSON_NUMERIC_CHECK));
                }
            }
        } else {
            $message->success(false);
            $message->msg("I'm sorry dave, I cannot do that. Goodbye");
            if($message->valid()) {
                if($this->subscribers[$from->resourceId]) {
                    if(!$this->subscribers[$from->resourceId]->authenticate($message->signature())) {
                        $this->log->HAL('Something smells funny ' . $this->operator. '. Disconnecting ' . $from->remoteAddress, $from);
                        $message->reason('Authentication failure');
                        $from->close();
                    }
                }
            } else {
                $this->log->HAL("I'm sorry " . ($this->subscribers[$from->resourceId]->_id() ? $this->subscribers[$from->resourceId]->_id() : $from->remoteAddress) . '. I cannot do that. Goodbye.', $from);
            }
            $from->send($message->toJson());
        }
    }

    public function onClose(ConnectionInterface $conn) {
            if(isset($this->subscribers[$conn->resourceId])) {
                unset($this->subscribers[$conn->resourceId]);
            }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log->error('There was an error with the connection', $e);
        $conn->close();
    }
}
