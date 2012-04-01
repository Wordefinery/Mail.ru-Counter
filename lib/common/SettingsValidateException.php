<?php

namespace wordefinery;

class SettingsValidateException extends Exception {
    protected $type = 'error';

    public function __construct ($type = "error", $message = "", $code = 0, Exception $previous = NULL ) {
        parent::__construct ($message, $code, $previous);
        $this->type = $type;
    }

    final public function getType() {
        return $this->type;
    }
}
