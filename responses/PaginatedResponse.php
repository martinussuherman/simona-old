<?php

class PaginatedResponse extends BaseResponse
{
    protected $result;
    protected $total;
    protected $limit;
    protected $offset;

    public function __construct($result, $limit, $offset)
    {
        $this->result = array_slice($result, $offset, $limit);
        $this->total = count($result);
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function buildBody() {
        return [
            'success' => true,
            'data' =>[
                'total_record' => $this->total,
			    'per_page' => (int) $this->limit,
			    'total_page'	=> $this->limit <= 0 ? 0 : ceil($this->total / $this->limit),
			    'current_page'	=> $this->limit <= 0 ? 0 : floor($this->offset / $this->limit) + 1,
                'result'		=> $this->result
            ]
        ];
    }

    public function getStatusCode() {
        return 200;
    }
}