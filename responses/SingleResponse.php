<?php

class SingleResponse extends BaseResponse
{
    protected $result;
    protected $fieldName;

    public function __construct($result, $fieldName = 'object')
    {
        $this->result = $result;
        $this->fieldName = $fieldName;
    }

    public function buildBody() {
        if(count($this->result) < 1) {
            return [
                'status' => Utils::MESSAGE_ERROR,
                'error_messages' => [
                    [
                        "field" => $this->fieldName,
                        "message" => 'Data Not Found'
                    ]
                ]
            ];
        } else {
            return [
                'status' => Utils::MESSAGE_SUCCESS,
                'data' => $this->result[0]
            ];
        }
    }

    public function getStatusCode() {
        return count($this->result) < 1 ? 404 : 200;
    }
}