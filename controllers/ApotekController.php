<?php

class ApotekController extends BaseController
{
	public function index()
	{
		$getParams = $this->request->get();

		if ($this->checkRoles([Account::ROLE_USER]))
			$getParams['account_id'] = $this->shared->account->id;
		elseif (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		$limit = array_key_exists('limit', $getParams) ? $getParams['limit'] : 10;
		$offset = array_key_exists('offset', $getParams) ? $getParams['offset'] : 0;
		$keyword = array_key_exists('keyword', $getParams) ? $getParams['keyword'] : null;
		$id = array_key_exists('id', $getParams) ? $getParams['id'] : null;
		$account_id = array_key_exists('account_id', $getParams) ? $getParams['account_id'] : null;
		$apoteker_id = array_key_exists('apoteker_id', $getParams) ? $getParams['apoteker_id'] : null;
		$name = array_key_exists('name', $getParams) ? $getParams['name'] : null;
		$address = array_key_exists('address', $getParams) ? $getParams['address'] : null;
		$phone = array_key_exists('phone', $getParams) ? $getParams['phone'] : null;
		$kelurahan = array_key_exists('kelurahan', $getParams) ? $getParams['kelurahan'] : null;
		$kecamatan = array_key_exists('kecamatan', $getParams) ? $getParams['kecamatan'] : null;
		$kabupaten = array_key_exists('kabupaten', $getParams) ? $getParams['kabupaten'] : null;
		$provinsi = array_key_exists('provinsi', $getParams) ? $getParams['provinsi'] : null;
		$postal_code = array_key_exists('postal_code', $getParams) ? $getParams['postal_code'] : null;
		$category = array_key_exists('category', $getParams) ? $getParams['category'] : null;
		$wilayah_code = array_key_exists('wilayah_code', $getParams) ? $getParams['wilayah_code'] : null;
		$sia = array_key_exists('sia', $getParams) ? $getParams['sia'] : null;
		$state = array_key_exists('state', $getParams) ? $getParams['state'] : null;
		$states = array_key_exists('states', $getParams) ? $getParams['states'] : null;
		if (!is_null($state)) $states .= (is_null($states) ? '' : ',').$state;
		$state_monev = array_key_exists('state_monev', $getParams) ? $getParams['state_monev'] : null;
		$states_monev = array_key_exists('states_monev', $getParams) ? $getParams['states_monev'] : null;
		if (!is_null($state_monev)) $states_monev .= (is_null($states_monev) ? '' : ',').$state_monev;
		$state_perubahan = array_key_exists('state_perubahan', $getParams) ? $getParams['state_perubahan'] : null;
		$states_perubahan = array_key_exists('states_perubahan', $getParams) ? $getParams['states_perubahan'] : null;
		if (!is_null($state_perubahan)) $states_monev .= (is_null($states_monev) ? '' : ',').$state_perubahan;

		$query = Apotek::query();
		if (is_null($keyword))
		{
			if ($this->shared->account->role == Account::ROLE_USER) {
				$this->andWhereId($query, 'account_id', $account_id);
			} elseif (is_null($id)) {
				if (!empty($this->shared->account->kabupaten))
				$this->andWhereExact($query, 'kabupaten', $this->shared->account->kabupaten);
				if (!empty($this->shared->account->provinsi))
					$this->andWhereExact($query, 'provinsi', $this->shared->account->provinsi);
				if ($wilayah_code <> 'null')
					$this->andWhereExact($query, 'wilayah_code', $wilayah_code);
			} else {
				$this->andWhereId($query, 'id', $id);
			}

			if (!is_null($account_id)) $this->andWhereId($query, 'account_id', $account_id);
			if (!is_null($apoteker_id)) $this->andWhereId($query, 'apoteker_id', $apoteker_id);
			if (!is_null($name)) $this->andWhereLike($query, 'name', $name);
			if (!is_null($address)) $this->andWhereLike($query, 'address', $address);
			if (!is_null($phone)) $this->andWhereExact($query, 'phone', $phone);
			if (!is_null($kelurahan)) $this->andWhereLike($query, 'kelurahan', $kelurahan);
			if (!is_null($kecamatan)) $this->andWhereLike($query, 'kecamatan', $kecamatan);
			if (!is_null($kabupaten)) $this->andWhereLike($query, 'kabupaten', $kabupaten);
			if (!is_null($provinsi)) $this->andWhereLike($query, 'provinsi', $provinsi);
			if (!is_null($postal_code)) $this->andWhereExact($query, 'postal_code', $postal_code);
			if (!is_null($category)) $this->andWhereLike($query, 'category', $category);
			if (!is_null($sia)) $this->andWhereLike($query, 'sia', $sia);
			if (!is_null($states)) $this->andWhereExactValues($query, 'state', $states);
			if (!is_null($states_monev)) $this->andWhereExactValues($query, 'state_monev', $states_monev);
			if (!is_null($states_perubahan)) $this->andWhereExactValues($query, 'state_perubahan', $states_perubahan);
		}
		else // favors keyword
		{
			if ($this->shared->account->role == Account::ROLE_USER) {
				$this->andWhereId($query, 'account_id', $account_id);
			} else {
				if (!empty($this->shared->account->kabupaten))
				$this->andWhereExact($query, 'kabupaten', $this->shared->account->kabupaten);
				if (!empty($this->shared->account->provinsi))
					$this->andWhereExact($query, 'provinsi', $this->shared->account->provinsi);
				if ($wilayah_code <> 'null')
					$this->andWhereExact($query, 'wilayah_code', $wilayah_code);
			}

			$this->andWhereKeywords($query, $keyword,
				['name', 'address', 'kelurahan', 'kecamatan', 'kabupaten', 'provinsi']);
		}

		$result = $query->orderBy('updated_time ASC')->execute()->toArray();
		return new PaginatedResponse($result, $limit, $offset);
    }

	public function update()
	{
		$rawBody = $this->request->getPost();

		$search_key = 'id';
		$search_id = null;

		$validatedRequest = $this->validate(
			$rawBody,
			[
				'id' => ['validators' => ['required', 'uuid']],
			]
		);

		if ($this->checkRoles([Account::ROLE_USER]))
		{
			$search_key = 'id';
			$search_id = $rawBody['id'];
		}
		elseif ($this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
		{
			$search_key = 'id';
			$search_id = $rawBody['id'];
		}
		else throw new CustomErrorException(200, 'Invalid role');

		$state = array_key_exists('state', $rawBody) ? $rawBody['state'] : null;
		$apoteker_id = array_key_exists('apoteker_id', $rawBody) ? $rawBody['apoteker_id'] : null;
		$izin_id = array_key_exists('izin_id', $rawBody) ? $rawBody['izin_id'] : null;
		$state_monev = array_key_exists('state_monev', $rawBody) ? $rawBody['state_monev'] : null;
		$state_perubahan = array_key_exists('state_perubahan', $rawBody) ? $rawBody['state_perubahan'] : null;
		$name = array_key_exists('name', $rawBody) ? $rawBody['name'] : null;
		$address = array_key_exists('address', $rawBody) ? $rawBody['address'] : null;
		$phone = array_key_exists('phone', $rawBody) ? $rawBody['phone'] : null;
		$kelurahan = array_key_exists('kelurahan', $rawBody) ? $rawBody['kelurahan'] : null;
		$kecamatan = array_key_exists('kecamatan', $rawBody) ? $rawBody['kecamatan'] : null;
		$kabupaten = array_key_exists('kabupaten', $rawBody) ? $rawBody['kabupaten'] : null;
		$provinsi = array_key_exists('provinsi', $rawBody) ? $rawBody['provinsi'] : null;
		$postal_code = array_key_exists('postal_code', $rawBody) ? $rawBody['postal_code'] : null;
		$category = array_key_exists('category', $rawBody) ? $rawBody['category'] : null;
		//$sia = array_key_exists('sia', $rawBody) ? $rawBody['sia'] : null;
		//$sia_expired = array_key_exists('sia_expired', $rawBody) ? $rawBody['sia_expired'] : null;

		$result = new stdClass();
		$result->success = false;

		if (TypeValidator::IsUUIDv4($search_id))
		{
			$obj = Apotek::findFirst([
				'conditions' => $search_key.' = :id:',
				'order' => 'updated_time DESC',
				'bind' => ['id' => $search_id],
			]);

			if (empty($obj))
			{
				$result->message = 'Id not found';
			}
			else
			{
				if ($this->checkRoles([Account::ROLE_USER]))
				{
					if ($obj->account_id != $this->shared->account->id)
						throw new CustomErrorException(200, 'Invalid account for apotek id');
					else {
						// TODO: user hanya boleh update bbrp state saja
						switch($obj->state) {
							case 0: // Pengajuan Baru
							case 1: // Penugasan Pemeriksaan Izin Baru
							// case 2: // Permohonan Pengajuan Ditolak
							case 3: // Syarat Izin Baru Memenuhi
							// case 4: // Syarat Izin Baru Tidak Memenuhi
							// case 5: // Izin Baru Diterbitkan
							case 6: // Permohonan Perpanjangan
							// case 7: // Permohonan Perpanjangan Ditolak
							case 8: // Penugasan Pemeriksaan Izin Perpanjangan
							case 9: // Syarat Izin Perpanjangan Memenuhi
							// case 10: // Syarat Izin Perpanjangan Tidak Memenuhi
							// case 11: // Izin Perpanjangan Diterbitkan
							// case 12: // Izin Dicabut
							// case 13: // Permohonan Perubahan Izin
								throw new CustomErrorException(200, 'Invalid state');
							default:
								break;
						}

						if ($obj->state == 13) {
							switch($obj->state_perubahan) {
								case 0: // Permohonan Perubahan
								// case 1: // Permohonan Perubahan Disetujui
								// case 2: // Permohonan Perubahan Ditolak
									throw new CustomErrorException(200, 'Invalid state');
								default:
									break;
							}
						}
					}
				}

				if (!is_null($state)) $obj->state = $state;
				if (!is_null($apoteker_id)) $obj->apoteker_id = $apoteker_id;
				if (!is_null($state_monev)) $obj->state_monev = $state_monev;
				if (!is_null($name)) $obj->name = $name;
				if (!is_null($address)) $obj->address = $address;
				if (!is_null($phone)) $obj->phone = $phone;
				if (!is_null($kelurahan)) $obj->kelurahan = $kelurahan;
				if (!is_null($kecamatan)) $obj->kecamatan = $kecamatan;
				if (!is_null($kabupaten)) $obj->kecamatan = $kabupaten;
				if (!is_null($provinsi)) $obj->provinsi = $provinsi;
				if (!is_null($postal_code)) $obj->postal_code = $postal_code;
				if (!is_null($category)) $obj->category = $category;
				//if (!is_null($sia)) $obj->sia = $sia;
				//if (!is_null($sia_expired)) $obj->sia_expired = $sia_expired;

				if (!is_null($state_perubahan)) {
					if ($state_perubahan == 2 && !is_null($izin_id)) {
						// update data apotek perubahan
						$izin = IzinPerubahan::findFirst([
							'conditions' => 'id = :id:',
							'order' => 'updated_time DESC',
							'bind' => ['id' => $izin_id],
						]);						
						$izin_data = json_decode($izin->raw);

						if (!is_null($izin_data)) {
							$obj->name = $izin_data['NamaApotek'];
							$obj->address = $izin_data['AlamatApotek'];
							$obj->phone = $izin_data['TeleponApotek'];
							$obj->kelurahan = $izin_data['DesaKelurahan'];
							$obj->kecamatan = $izin_data['Kecamatan'];
							$obj->kabupaten = $izin_data['KabupatenKota'];
						}

						// update data apoteker perubahan
						$apoteker = Apoteker::findFirst([
							'conditions' => 'id = :id:',
							'order' => 'updated_time DESC',
							'bind' => ['id' => $obj->apoteker_id],
						]);	
						if (!empty($apoteker)) {
							$apoteker->name = $izin_data['NamaLengkapPemohon'];
							$apoteker->ktp = $izin_data['NoKtp'];
							$apoteker->address = $izin_data['Alamat'];
							$apoteker->phone = $izin_data['TeleponPemohon'];
							$apoteker->npwp = $izin_data['NPWP'];
							$apoteker->stra = $izin_data['NoSTRA'];
							$apoteker->stra_expired = $izin_data['MasaBerlakuSTRA'];
						}

						// reset state back to 0
						$obj->state_perubahan = 0;
					}
					else {
						$obj->state_perubahan = $state_perubahan;
					}
				}

				$obj->updated_time = Utils::DateTimeNow();
				$obj->save();

				$result->success = true;
				$result->data = $obj;
				
				// if (!is_null($sia) || !is_null($sia_expired))
					// $result->message = 'Use API Izin to update SIA number and SIA expired';
			}
		}
		else
		{
			$result->message = 'Invalid id';
		}

		return new ObjectResponse($result);
	}
}