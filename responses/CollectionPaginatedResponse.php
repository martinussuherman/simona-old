<?php

class CollectionPaginatedResponse extends PaginatedResponse
{
    protected $result;

    public function __construct($result)
    {
        parent::__construct($result, count($result), count($result), 0);
    }

}