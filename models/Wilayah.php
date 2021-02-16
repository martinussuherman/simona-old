<?php

class Wilayah extends BaseModel
{
    const STATE_ACTIVE = 1;
    const STATE_REVOKED = 0;

    const LEVEL_NEGARA = 0;
    const LEVEL_PROVINSI = 1;
    const LEVEL_KABUPATENKOTA = 2;
    const LEVEL_KECAMATAN = 3;
    const LEVEL_KELURAHANDESA = 4;

    public $display;
    public $level;
    public $parent_id;
    public $postal_code;

	public function getSource()
    {
        return 'wilayah';
    }

    public function getSchema()
    {
		return 'public';
    }
}