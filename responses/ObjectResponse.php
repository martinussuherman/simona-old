<?php

class ObjectResponse extends BaseResponse {
    protected $obj;
    protected $statusCode = 200;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    public function buildBody() {
        return $this->obj;
    }

    public function getStatusCode() {
        return  $this->statusCode;
    }

    public function setStatusCode($statusCode){
        $this->statusCode = $statusCode;
    }
}

?>