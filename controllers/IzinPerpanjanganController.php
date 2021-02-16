<?php

class IzinPerpanjanganController extends FormIzinController
{
	public function index()
	{
		$getParams = $this->request->get();

		if ($this->checkRoles([Account::ROLE_USER]))
		{
			// override account id
			$getParams['account_id'] = $this->shared->account->id;
			// TODO: need code for user keyword search
			unset($getParams['keyword']);
		}
		elseif (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->indexData(IzinPermohonan::class, $getParams);
	}

	public function latest()
	{
		$getParams = $this->request->get();

		if ($this->checkRoles([Account::ROLE_USER]))
			$getParams['account_id'] = $this->shared->account->id;
		elseif (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->indexData(IzinPerpanjangan::class, $getParams, 'updated_time DESC', 1);
	}
	
	public function update()
	{
		$rawBody = $this->request->getPost();

		// TODO: user hanya boleh update bbrp state saja
		// if ($this->checkRoles([Account::ROLE_USER]))
		// 	$rawBody['account_id'] = $this->shared->account->id;
		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->updateData(IzinPerpanjangan::class, $rawBody);
	}
	
	public function rawupdate()
	{
		$rawBody = $this->request->getPost();

		return $this->updateRawData(IzinPerpanjangan::class, $rawBody);
	}

	public function add()
	{
		if (!$this->checkRoles([Account::ROLE_USER]))
			throw new CustomErrorException(200, 'Invalid role');

		try
		{
			$rawBody = $this->request->getPost();
			$obj = $this->addFormData(IzinPerpanjangan::class, $rawBody);

			if (!empty($obj)) {			
				$apotek = Apotek::findFirst([
					'conditions' => 'account_id = :account_id:',
					'bind' => ['account_id' => $this->shared->account->id],
				]);

				if (empty($apotek))
					throw new CustomErrorException(200, 'Invalid apotek_id or not belong to account');

				$apotek->last_izin_id = $obj->id;
				$apotek->last_izin_type = Apotek::IZIN_TYPE_PERPANJANGAN;
				$apotek->save();
			}

			$result = new stdClass();
			$result->message = 'success';

			return new ObjectResponse($result);
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
	}
	
	public function download()
	{
		// add role check!
		
		try
		{
			$rawBody = $this->request->getPost();
			return $this->downloadAction(IzinPerpanjangan::class, $rawBody);
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
	}

	public function generatedoc()
	{
		// add role check!
		
		try
		{
			$rawBody = $this->request->getPost();
			return $this->generatedocAction(IzinPerpanjangan::class, $rawBody, 'FORM SELF ASSESSMENT PERPANJANGAN IZIN APOTEK');
			// [
			// 	'data_apotek_nama' => 'name',
			// 	'data_apotek_alamat' => 'address',
			// 	'data_apotek_kelurahan' => 'kelurahan',
			// 	'data_apotek_kecamatan' => 'kecamatan',
			// 	'data_apotek_kabupaten' => 'kabupaten',
			// 	'data_apotek_provinsi' => 'provinsi',
			// 	'data_apotek_nib' => 'nib',
			// 	'data_apotek_nibnama' => 'namaPemilikNib',
			// 	'data_apoteker_nibjenis' => 'jenisPemilikNib',
			// 	'data_apotek_sia' => 'noSia',
			// 	'data_apoteker_nama' => 'namaApotekerPemegangSia',
			// 	'data_apoteker_stra' => 'noStra',
			// 	'data_apoteker_straexpired' => 'straDate',
			// 	'data_apoteker_sipa' => 'noSipa',
			// 	'data_apoteker_sipaexpired' => 'sipaDate',
			// 	'data_dasar_jumlah_apoteker' => 'jumlahApoteker',
			// 	'data_dasar_jumlah_ttk' => 'jumlahTtk',
			// 	'data_dasar_jumlah_nonfarmasi' => 'jumlahTenagaNonFarmasi',
			// 	'data_dasar_jampraktik' => 'jamPraktikApoteker',
			// 	'data_dasar_resep' => 'rataRataResep',
			// 	'data_dasar_nonresep' => 'rataRataKunjungan',
			// 	'data_dasar_jadi' => 'rataRataWaktuObatJadi',
			// 	'data_dasar_racikan' => 'rataRataWaktuObatRacikan',
			// 	'data_dasar_bpjs' => 'kerjasamaBpjs',
			// 	'data_dasar_elektronik' => 'layananElektronik',
			// 	'data_dasar_pengantaran' => 'layananPengantaran',
			// ]);
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
	}
}