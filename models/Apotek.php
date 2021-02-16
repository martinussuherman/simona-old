<?php

class Apotek extends BaseAddress
{
	const IZIN_TYPE_BARU = 0;
	const IZIN_TYPE_PERPANJANGAN = 1;

	public $apoteker_id;
	public $category;
	public $state_monev;
	public $state_sia;
	public $sia_id;
	public $sia;
	public $sia_expired;
	public $last_izin_id;
	public $last_izin_type;
	public $state_perubahan;
	public $wilayah_code;

	public function getSource()
    {
        return 'apotek';
	}

	public function getLastIzin()
	{
		if (empty($last_izin_id)) return null;
		else if ($last_izin_type == IZIN_TYPE_BARU)
		{
			$izin = $IzinPermohonan::findFirst([
				'conditions' => 'id = :last_id:',
				'bind' => ['last_id' => $last_izin_id],
			]);
			return $izin;
		}
		else if ($last_izin_type == IZIN_TYPE_PERPANJANGAN)
		{
			$izin = $IzinPerpanjangan::findFirst([
				'conditions' => 'id = :last_id:',
				'bind' => ['last_id' => $last_izin_id],
			]);
			return $izin;
		}
		else return null;
	}
}