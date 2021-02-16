<?php

class ApotekerController extends BaseController
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
		$state = array_key_exists('state', $getParams) ? $getParams['state'] : null;
		$name = array_key_exists('name', $getParams) ? $getParams['name'] : null;
		$address = array_key_exists('address', $getParams) ? $getParams['address'] : null;
		$phone = array_key_exists('phone', $getParams) ? $getParams['phone'] : null;
		$kelurahan = array_key_exists('kelurahan', $getParams) ? $getParams['kelurahan'] : null;
		$kecamatan = array_key_exists('kecamatan', $getParams) ? $getParams['kecamatan'] : null;
		$kabupaten = array_key_exists('kabupaten', $getParams) ? $getParams['kabupaten'] : null;
		$provinsi = array_key_exists('provinsi', $getParams) ? $getParams['provinsi'] : null;
		$postal_code = array_key_exists('postal_code', $getParams) ? $getParams['postal_code'] : null;
		$ktp = array_key_exists('ktp', $getParams) ? $getParams['ktp'] : null;
		$npwp = array_key_exists('npwp', $getParams) ? $getParams['npwp'] : null;
		$stra = array_key_exists('stra', $getParams) ? $getParams['stra'] : null;
		$states = array_key_exists('states', $getParams) ? $getParams['states'] : null;
		if (!is_null($state)) $states .= (is_null($states) ? '' : ',').$state;

		$query = Apoteker::query();
		if (is_null($keyword))
		{
			if (!is_null($id)) $this->andWhereId($query, 'id', $id);
			if (!is_null($account_id)) $this->andWhereId($query, 'account_id', $account_id);
			if (!is_null($name)) $this->andWhereLike($query, 'name', $name);
			if (!is_null($address)) $this->andWhereLike($query, 'address', $address);
			if (!is_null($phone)) $this->andWhereExact($query, 'phone', $phone);
			if (!is_null($kelurahan)) $this->andWhereLike($query, 'kelurahan', $kelurahan);
			if (!is_null($kecamatan)) $this->andWhereLike($query, 'kecamatan', $kecamatan);
			if (!is_null($kabupaten)) $this->andWhereLike($query, 'kabupaten', $kabupaten);
			if (!is_null($provinsi)) $this->andWhereLike($query, 'provinsi', $provinsi);
			if (!is_null($postal_code)) $this->andWhereExact($query, 'postal_code', $postal_code);
			if (!is_null($ktp)) $this->andWhereExact($query, 'ktp', $ktp);
			if (!is_null($npwp)) $this->andWhereExact($query, 'npwp', $npwp);
			if (!is_null($stra)) $this->andWhereExact($query, 'stra', $stra);
			if (!is_null($sipa)) $this->andWhereExact($query, 'sipa', $sipa);			
			if (!is_null($states)) $this->andWhereExactValues($query, 'state', $states);
		}
		else // favors keyword
		{
			if ($this->shared->account->role == Account::ROLE_USER)
				$this->andWhereId($query, 'account_id', $account_id);
			
			$this->andWhereKeywords($query, $keyword,
				['name', 'address', 'kelurahan', 'kecamatan', 'kabupaten', 'provinsi',
				'phone', 'ktp', 'npwp', 'stra', 'sipa']);
		}

		$result = $query->orderBy('updated_time ASC')->execute()->toArray();
		return new PaginatedResponse($result, $limit, $offset);
    }

	public function update()
	{
		$rawBody = $this->request->getPost();

		$search_key = 'id';
		$search_id = null;

		// TODO: user hanya boleh update bbrp state saja
		if ($this->checkRoles([Account::ROLE_USER]))
		{
			$search_key = 'account_id';
			$search_id = $this->shared->account->id;
		}
		elseif ($this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
		{
			$validatedRequest = $this->validate(
				$rawBody,
				[
					'id' => ['validators' => ['required', 'uuid']],
				]
			);

			$search_key = 'id';
			$search_id = $rawBody['id'];
		}
		else throw new CustomErrorException(200, 'Invalid role');

		$state = array_key_exists('state', $rawBody) ? $rawBody['state'] : null;
		$state_monev = array_key_exists('state_monev', $rawBody) ? $rawBody['state_monev'] : null;
		$name = array_key_exists('name', $rawBody) ? $rawBody['name'] : null;
		$address = array_key_exists('address', $rawBody) ? $rawBody['address'] : null;
		$phone = array_key_exists('phone', $rawBody) ? $rawBody['phone'] : null;
		$kelurahan = array_key_exists('kelurahan', $rawBody) ? $rawBody['kelurahan'] : null;
		$kecamatan = array_key_exists('kecamatan', $rawBody) ? $rawBody['kecamatan'] : null;
		$kabupaten = array_key_exists('kabupaten', $rawBody) ? $rawBody['kabupaten'] : null;
		$provinsi = array_key_exists('provinsi', $rawBody) ? $rawBody['provinsi'] : null;
		$postal_code = array_key_exists('postal_code', $rawBody) ? $rawBody['postal_code'] : null;
		$ktp = array_key_exists('ktp', $rawBody) ? $rawBody['ktp'] : null;
		$npwp = array_key_exists('npwp', $rawBody) ? $rawBody['npwp'] : null;
		$stra = array_key_exists('stra', $rawBody) ? $rawBody['stra'] : null;
		$stra_expired = array_key_exists('stra_expired', $rawBody) ? $rawBody['stra_expired'] : null;
		$sipa = array_key_exists('sipa', $rawBody) ? $rawBody['sipa'] : null;
		$sipa_expired = array_key_exists('sipa_expired', $rawBody) ? $rawBody['sipa_expired'] : null;

		$result = new stdClass();
		$result->success = false;

		if (TypeValidator::IsUUIDv4($search_id))
		{
			$obj = Apoteker::findFirst([
				'conditions' => $search_key.' = :id:',
				'bind' => ['id' => $search_id],
			]);

			if (empty($obj))
			{
				$result->message = 'Id not found';
			}
			else
			{
				if (!is_null($state)) $obj->state = $state;
				if (!is_null($state_monev)) $obj->state_monev = $state_monev;
				if (!is_null($name)) $obj->name = $name;
				if (!is_null($address)) $obj->address = $address;
				if (!is_null($phone)) $obj->phone = $phone;
				if (!is_null($kelurahan)) $obj->kelurahan = $kelurahan;
				if (!is_null($kecamatan)) $obj->kecamatan = $kecamatan;
				if (!is_null($kabupaten)) $obj->kecamatan = $kabupaten;
				if (!is_null($provinsi)) $obj->provinsi = $provinsi;
				if (!is_null($postal_code)) $obj->postal_code = $postal_code;
				if (!is_null($ktp)) $obj->ktp = $ktp;
				if (!is_null($npwp)) $obj->npwp = $npwp;
				if (!is_null($stra)) $obj->stra = $stra;
				if (!is_null($stra_expired)) $obj->stra_expired = $stra_expired;
				if (!is_null($sipa)) $obj->sipa = $sipa;
				if (!is_null($sipa_expired)) $obj->sipa_expired = $sipa_expired;
				$obj->updated_time = Utils::DateTimeNow();
				$obj->save();

				$result->success = true;
				$result->data = $obj;
			}
		}
		else
		{
			$result->message = 'Invalid id';
		}

		return new ObjectResponse($result);
	}
}