<?php

class Wordefinery_SettingsValidateException extends Exception {
    protected $type = 'error';

    public function __construct ($type = "error", $message = "", $code = 0) {
        parent::__construct ($message, $code);
        $this->type = $type;
    }

    final public function getType() {
        return $this->type;
    }
}

class SettingsValidateException extends Exception {
    protected $type = 'error';

    public function __construct ($type = "error", $message = "", $code = 0) {
        parent::__construct ($message, $code);
        $this->type = $type;
    }

    final public function getType() {
        return $this->type;
    }
}
