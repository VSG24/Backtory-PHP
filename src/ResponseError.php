<?php
namespace VSG24\Backtory;

class ResponseError {

    public $message;
    public $error;

    public function __construct($message, $error)
    {
        $this->message = $message;
        $this->error = $error;
    }

}