<?php

class CustomErrorException extends Exception {
    protected $messages;
    protected $statusCode;

    public function __construct($statusCode, $messages)
    {
        $this->messages = $messages;
        $this->statusCode = $statusCode;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getStatusCode(){
        return $this->statusCode;
    }
}