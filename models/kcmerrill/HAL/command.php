<?php

namespace kcmerrill\HAL;

class command extends base {
    function __construct($db, $log, $msg){
        parent::__construct($db, $log, $msg);
        $this->ok(true);
   }
}
