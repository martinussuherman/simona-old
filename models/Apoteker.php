<?php

class Apoteker extends BaseAddress
{
	public $ktp;
	public $npwp;
	public $stra;
	public $stra_expired;

	public function getSource()
    {
        return 'apoteker';
    }
}