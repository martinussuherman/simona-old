<?php

class Sia extends BaseModel
{
    const SIA_INACTIVE = 0;
	const SIA_ACTIVE = 1;
	const SIA_REVOKED = 2;
	const SIA_RETURNED = 3;
	const SIA_RETURN_CANCELLED = 4;

	public $apotek_id;
	public $sia_number;
	public $sia_expired;
	public $sia_file;
	public $sia_remarks;
	public $sia_time;
	public $revoke_remarks;
	public $revoke_file;
	public $revoke_time;
	public $return_remarks;
	public $return_time;

	public function getSource()
    {
        return 'sia';
    }
}