<?php

class IzinPerubahanController extends FormIzinController
{
	public function index()
	{
		$getParams = $this->request->get();

		if ($this->checkRoles([Account::ROLE_USER]))
			$getParams['account_id'] = $this->shared->account->id;
		elseif (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->indexData(IzinPerubahan::class, $getParams);
	}

	public function latest()
	{
		$getParams = $this->request->get();

		if ($this->checkRoles([Account::ROLE_USER]))
			$getParams['account_id'] = $this->shared->account->id;
		elseif (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->indexData(IzinPerubahan::class, $getParams, 'updated_time DESC', 1);
	}
	
	public function update()
	{
		$rawBody = $this->request->getPost();

		// TODO: user hanya boleh update bbrp state saja
		// if ($this->checkRoles([Account::ROLE_USER]))
		// 	$rawBody['account_id'] = $this->shared->account->id;
		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

        $result = $this->updateData(IzinPerubahan::class, $rawBody);
        
        if ($result->success) {

        }

        return $result;
	}
	
	public function rawupdate()
	{
		$rawBody = $this->request->getPost();

		return $this->updateRawData(IzinPerubahan::class, $rawBody);
	}

	public function add()
	{
		if (!$this->checkRoles([Account::ROLE_USER]))
			throw new CustomErrorException(200, 'Invalid role');

		try {
			$account_id = $this->shared->account->id;

			$rawBody = $this->request->getPost();

			$validatedRequest = $this->validate(
				$rawBody,
				[
					'NamaLengkapPemohon' => ['validators' => ['required']],
					'NoKtp' => ['validators' => ['required', 'digit']],
					'Alamat' => ['validators' => ['required']],
					'TeleponPemohon' => ['validators' => ['required']],
					'NPWP' => ['validators' => ['required']],
					'NoSTRA' => ['validators' => ['required']],
					'MasaBerlakuSTRA' => ['validators' => ['required']],
					'NamaApotek' => ['validators' => ['required']],
					'AlamatApotek' => ['validators' => ['required']],
					'TeleponApotek' => ['validators' => ['required']],
					'DesaKelurahan' => ['validators' => ['required']],
					'Kecamatan' => ['validators' => ['required']],
					'KabupatenKota' => ['validators' => ['required']],
				]
			);

			$izin = $this->addFormData(IzinPerubahan::class, $rawBody);

			$apotek = Apotek::findFirst([
				'conditions' => 'account_id = :account_id:',
				'bind' => ['account_id' => $this->shared->account->id],
			]);

			if (empty($apotek))
				throw new CustomErrorException(200, 'Apotek not found');

			$apoteker = Apoteker::findFirst([
				'conditions' => 'account_id = :account_id:',
				'bind' => ['account_id' => $this->shared->account->id],
			]);

			if (empty($apoteker))
				throw new CustomErrorException(200, 'Apoteker not found');

			$data = new stdClass();
			$data->message = 'success';
			$data->id = $izin->id;
			$data->apotek_id = $apotek->id;
			$data->apoteker_id = $apoteker->id;
			$data->raw = $izin->raw;

			return new ObjectResponse($data);
		}
		catch (AuthException $e) {
            $this->flash->error($e->getMessage());
        }
	}
	
	public function download()
	{
		// add role check!
		
		try
		{
			$rawBody = $this->request->getPost();
			return $this->downloadAction(IzinPerubahan::class, $rawBody);
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
			return $this->generatedocAction(IzinPerubahan::class, $rawBody, 'FORM PERMOHONAN IZIN APOTEK');
			// [
			// 	'data_pemohon_nama' => 'NamaLengkapPemohon',
			// 	'data_pemohon_ktp' => 'NoKtp',
			// 	'data_pemohon_alamat' => 'Alamat',
			// 	'data_pemohon_telepon' => 'TeleponPemohon',
			// 	'data_pemohon_npwp' => 'NPWP',
			// 	'data_pemohon_stra' => 'NoSTRA',
			// 	'data_pemohon_straexpired' => 'MasaBerlakuSTRA',
			// 	'data_apotek_nama' => 'NamaApotek',
			// 	'data_apotek_alamat' => 'AlamatApotek',
			// 	'data_apotek_telepon' => 'TeleponApotek',
			// 	'data_apotek_kelurahan' => 'DesaKelurahan',
			// 	'data_apotek_kecamatan' => 'Kecamatan',
			// 	'data_apotek_kabupaten' => 'KabupatenKota',
			// ]);
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
	}
}