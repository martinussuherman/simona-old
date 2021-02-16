<?php

class IzinPermohonanController extends FormIzinController
{
	public function index()
	{
		$getParams = $this->request->get();

		if ($this->checkRoles([Account::ROLE_USER]))
			$getParams['account_id'] = $this->shared->account->id;
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

		return $this->indexData(IzinPermohonan::class, $getParams, 'updated_time DESC', 1);
	}

	public function update()
	{
		$rawBody = $this->request->getPost();

		// TODO: user hanya boleh update bbrp state saja
		// if ($this->checkRoles([Account::ROLE_USER]))
		// 	$rawBody['account_id'] = $this->shared->account->id;
		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->updateData(IzinPermohonan::class, $rawBody);
	}

	public function rawupdate()
	{
		$rawBody = $this->request->getPost();

		return $this->updateRawData(IzinPermohonan::class, $rawBody);
	}

	public function add()
	{
		if (!$this->checkRoles([Account::ROLE_USER]))
			throw new CustomErrorException(200, 'Invalid role');

		try {
			$account_id = $this->shared->account->id;

			$rawBody = $this->request->getPost();
			// $now = Utils::DateTimeNow(); // automatic by db default value

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

			$apoteker_name = $rawBody['NamaLengkapPemohon'];
			$apoteker_ktp = $rawBody['NoKtp'];
			$apoteker_address = $rawBody['Alamat'];
			$apoteker_npwp = $rawBody['NPWP'];
			$apoteker_stra = $rawBody['NoSTRA'];
			$apoteker_nib = $rawBody['nib'];
			$apoteker_stra_exp = $rawBody['MasaBerlakuSTRA'];
			$apoteker_sipa = array_key_exists('NoSIPA', $rawBody) ? $rawBody['NoSIPA'] : null;
			$apoteker_sipa_exp = array_key_exists('MasaBerlakuSIPA', $rawBody) ? $rawBody['MasaBerlakuSIPA'] : null;
			$apoteker_phone = '';
			$apoteker_kelurahan = '';
			$apoteker_kecamatan = '';
			$apoteker_kabupaten = '';
			$apoteker_provinsi = '';
			$apoteker_postal_code = '';

			$apotek_name = $rawBody['NamaApotek'];
			$apotek_address = $rawBody['AlamatApotek'];
			$apotek_phone = $rawBody['TeleponApotek'];
			$apotek_kelurahan = $rawBody['DesaKelurahan'];
			$apotek_kecamatan = $rawBody['Kecamatan'];
			$apotek_kabupaten = $rawBody['KabupatenKota'];
			$apotek_provinsi = $rawBody['provinsi'];
			$apotek_wilayah_code = $rawBody['WilayahCode'];
			$apotek_postal_code = '';

			$errMsgs = [];

			if ($this->isRawDataExists(IzinPermohonan::class, 'raw', 'nib', $apoteker_nib)) {
				$errMsgs[count($errMsgs)] = array(
					'field' => 'nib',
					'message' => 'NIB sudah pernah pernah digunakan.'
				);
			}

			if (count($errMsgs) > 0) {
				throw new ValidationException($errMsgs);
			}
			
			$apoteker = Apoteker::findFirst([
				'conditions' => 'account_id = :account_id:',
				'bind' => ['account_id' => $account_id],
			]);

			if (empty($apoteker))
			{
				$apoteker = new Apoteker();
				$apoteker->id = $this->random->uuid();
				$apoteker->account_id = $account_id;
			}

			//$apoteker->created_time = $now; // automatic by db default value
			$apoteker->name = $apoteker_name;
			$apoteker->address = $apoteker_address;
			$apoteker->ktp = $apoteker_ktp;
			$apoteker->npwp = $apoteker_npwp;
			$apoteker->stra = $apoteker_stra;
			$apoteker->stra_expired = $apoteker_stra_exp;
			if (is_null($apoteker_sipa)) $apoteker->sipa = $apoteker_sipa;
			if (is_null($apoteker_sipa_exp)) $apoteker->sipa_expired = $apoteker_sipa_exp;
			// $apoteker->phone = $apoteker_phone;
			// $apoteker->kelurahan = $apoteker_kelurahan;
			// $apoteker->kecamatan = $apoteker_kecamatan;
			// $apoteker->kabupaten = $apoteker_kabupaten;
			// $apoteker->provinsi = $apoteker_provinsi;
			// $apoteker->postal_code = $apoteker_postal_code;
			$result_apoteker = $apoteker->save();

			//if (!$result_apoteker) print_r($apoteker->getMessages());


			$apotek = Apotek::findFirst([
				'conditions' => 'account_id = :account_id:',
				'bind' => ['account_id' => $account_id],
			]);

			if (empty($apotek))
			{
				$apotek = new Apotek();
				$apotek->id = $this->random->uuid();
				$apotek->account_id = $account_id;
			}

			//$apotek->created_time = $now; // automatic by db default value
			$apotek->apoteker_id = $apoteker->id;
			$apotek->name = $apotek_name;
			$apotek->address = $apotek_address;
			$apotek->phone = $apotek_phone;
			$apotek->kelurahan = $apotek_kelurahan;
			$apotek->kecamatan = $apotek_kecamatan;
			$apotek->kabupaten = $apotek_kabupaten;
			$apotek->wilayah_code = $apotek_wilayah_code;
			$apotek->provinsi = $apotek_provinsi;
			// $apotek->postal_code = $apotek_postal_code;

			$result_apotek = $apotek->save();
			//if (!$result_apotek) print_r($apotek->getMessages());

			$rawBody['apotek_id'] = $apotek->id;
			$izin = $this->addFormData(IzinPermohonan::class, $rawBody);

			//
			$apotek->last_izin_id = $izin->id;
			$apotek->last_izin_type = Apotek::IZIN_TYPE_BARU;

			$result_apotek = $apotek->save();
			//if (!$result_apotek) print_r($apotek->getMessages());

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
			return $this->downloadAction(IzinPermohonan::class, $rawBody);
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
			return $this->generatedocAction(IzinPermohonan::class, $rawBody, 'FORM PERMOHONAN IZIN APOTEK');
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