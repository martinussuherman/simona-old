<?php

use Phalcon\Mvc\Controller;

class ExportController extends BaseController
{
  public function generatexlsx()
  {
    $getParams = $this->request->getPost();

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
    $query->columns(
      [
        'Nama' => 'name',
        'Telepon' => 'phone',
        'Alamat' => 'address',          
        'Kelurahan' => 'kelurahan',          
        'Kecamatan' => 'kecamatan',          
        'Kabupaten' => 'kabupaten',          
        'Kabupaten' => 'kabupaten',          
        'Provinsi' => 'provinsi',
        'Kategori' => 'category',
        'Status' => 'state',
        'Berlaku' => 'sia_expired',
        'SIA' => 'sia',
        'Monev' => 'state_monev',
      ]
    );

		if (is_null($keyword))
		{            
      if ($this->shared->account->role == Account::ROLE_USER) {
				$this->andWhereId($query, 'account_id', $account_id);
			} elseif (is_null($id)) {
				if (!empty($this->shared->account->kabupaten))
				$this->andWhereExact($query, 'kabupaten', $this->shared->account->kabupaten);
				if (!empty($this->shared->account->provinsi))
					$this->andWhereExact($query, 'provinsi', $this->shared->account->provinsi);
			} else {
				$this->andWhereId($query, 'id', $id);
      }
      
			if (!is_null($id)) $this->andWhereId($query, 'id', $id);
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
			if ($this->shared->account->role == Account::ROLE_USER)
				$this->andWhereId($query, 'account_id', $account_id);
			
			$this->andWhereKeywords($query, $keyword,
				['name', 'address', 'kelurahan', 'kecamatan', 'kabupaten', 'provinsi']);
    }

    $rawBody = $query->orderBy('updated_time ASC')->execute()->toArray('name');  
    
    foreach($rawBody as $key => $item) {
      if($item['Status'] === 0) {
        $rawBody[$key]['Status'] = 'Pengajuan Baru';
      } else if($item['Status'] === 1) {
        $rawBody[$key]['Status'] = 'Permohonan Pemeriksaan Izin Baru';
      } else if($item['Status'] === 2) {
        $rawBody[$key]['Status'] = 'Permohonan Pengajuan Ditolak';
      } else if($item['Status'] === 3) {
        $rawBody[$key]['Status'] = 'Syarat Izin Baru Memenuhi';
      } else if($item['Status'] === 4) {
        $rawBody[$key]['Status'] = 'Syarat Izin Baru Tidak Memenuhi';
      } else if($item['Status'] === 5) {
        $rawBody[$key]['Status'] = 'Izin Baru Diterbitkan';
      } else if($item['Status'] === 6) {
        $rawBody[$key]['Status'] = 'Permohonan Perpanjangan';
      } else if($item['Status'] === 7) {
        $rawBody[$key]['Status'] = 'Permohonan Perpanjangan Ditolak';
      } else if($item['Status'] === 8) {
        $rawBody[$key]['Status'] = 'Permohonan Pemeriksaan Izin Perpanjangan';
      } else if($item['Status'] === 9) {
        $rawBody[$key]['Status'] = 'Syarat Izin Perpanjangan Memenuhi';
      } else if($item['Status'] === 10) {
        $rawBody[$key]['Status'] = 'Syarat Izin Perpanjangan Tidak Memenuhi';
      } else if($item['Status'] === 11) {
        $rawBody[$key]['Status'] = 'Izin Perpanjangan';
      } else if($item['Status'] === 12) {
        $rawBody[$key]['Status'] = 'Izin Dicabut';
      } 

      if($item['Monev'] === 0) {
        $rawBody[$key]['Monev'] = 'Belum Dilakukan';
      } else {
        $rawBody[$key]['Monev'] = 'Sudah Dilakukan';
      }

      if($item['Berlaku']) {
        $rawBody[$key]['Berlaku'] = date('Y-m-d', strtotime($rawBody[$key]['Berlaku']));
      }
    }
    
    $time = (new DateTime('NOW'))->format('Y_m_d_H_i_s_u');
    $filename = $time . '.xlsx';

    $result = new stdClass();
    if (empty($rawBody)) {
      $result->success = false;
      $result->message = "Empty.";
      return new ObjectResponse($result);
    }

    $header_keys = array_keys($rawBody[0]);
    $header = array();
    foreach ($header_keys as $hk) {
      $header[$hk] = 'string';
    }

    $wExcel = new Ellumilel\ExcelWriter();
    $wExcel->writeSheetHeader('Sheet1', $header);
    $wExcel->setAuthor('SIMYANFAR - SIMONA');
    foreach ($rawBody as $item) {
      $wExcel->writeSheetRow('Sheet1', array_values($item));
    }

    $strExcel = $wExcel->writeToString();
    return MediaHelper::FileResponseCustom($filename, "application/octet-stream", $strExcel);


  }
}
