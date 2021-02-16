<?php

class SiaController extends BaseController
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
		$apotek_id = array_key_exists('apotek_id', $getParams) ? $getParams['apotek_id'] : null;
		$state = array_key_exists('state', $getParams) ? $getParams['state'] : null;
		$name = array_key_exists('name', $getParams) ? $getParams['name'] : null;
		$sia_number = array_key_exists('sia_number', $getParams) ? $getParams['sia_number'] : null;
		if (!is_null($state)) $states .= (is_null($states) ? '' : ',').$state;

		$query = Apotek::query();
		if (is_null($keyword))
		{
			if (!is_null($id)) $this->andWhereId($query, 'id', $id);
			if (!is_null($account_id)) $this->andWhereId($query, 'account_id', $account_id);
			if (!is_null($apotek_id)) $this->andWhereId($query, 'apotek_id', $apoteker_id);
			if (!is_null($name)) $this->andWhereLike($query, 'name', $name);
			if (!is_null($sia_number)) $this->andWhereLike($query, 'sia_number', $sia_number);			
			if (!is_null($states)) $this->andWhereExactValues($query, 'state', $states);
		}
		else // favors keyword
		{
			if ($this->shared->account->role == Account::ROLE_USER)
				$this->andWhereId($query, 'account_id', $account_id);
			
			$this->andWhereKeywords($query, $keyword,
				['name']);
		}

		$result = $query->execute()->toArray();
		return new PaginatedResponse($result, $limit, $offset);
    }

    public function current()
    {
        $getParams = $this->request->get();
        $obj = null;

        if ($this->checkRoles([Account::ROLE_USER]))
        {
            $apotek = Apotek::findFirst([
                'conditions' => 'account_id = :id:',
                'bind' => ['id' => $this->shared->account->id],
            ]);

            if (empty($apotek))
			    throw new CustomErrorException(200, 'Apotek not found');

            $obj = Sia::findFirst([
                'conditions' => 'id = :id:',
                'bind' => ['id' => $apotek->sia_id],
            ]);
        }
        elseif ($this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
        {
            $validatedRequest = $this->validate(
                $getParams,
                [
                    'apotek_id' => ['validators' => ['required', 'uuid']],
                ]
            );
    
            $apotek_id = $getParams['apotek_id'];

            $apotek = Apotek::findFirst([
                'conditions' => 'id = :id:',
                'bind' => ['id' => $apotek_id],
            ]);

            if (empty($apotek))
			    throw new CustomErrorException(200, 'Apotek not found');

            $obj = Sia::findFirst([
                'conditions' => 'id = :id:',
                'bind' => ['id' => $apotek->sia_id],
            ]);
        }
		else
			throw new CustomErrorException(200, 'Invalid role');

		return new ObjectResponse($obj);
    }

    public function add()
	{
        $rawBody = $this->request->getPost();

		if ($this->checkRoles([Account::ROLE_PTSP]))
        {
        }
		else
            throw new CustomErrorException(200, 'Invalid role');
        
        $validatedRequest = $this->validate(
            $rawBody,
            [
                'apotek_id' => ['validators' => ['required', 'uuid']],
            ]
        );

        $apotek_id = $rawBody['apotek_id'];

		$state = array_key_exists('state', $rawBody) ? $rawBody['state'] : null;
		$sia_number = array_key_exists('sia_number', $rawBody) ? $rawBody['sia_number'] : null;
		$sia_expired = array_key_exists('sia_expired', $rawBody) ? $rawBody['sia_expired'] : null;
		$sia_remarks = array_key_exists('sia_remarks', $rawBody) ? $rawBody['sia_remarks'] : null;

		$result = new stdClass();
		$result->success = false;

        $izin = null;
        $apotek = Apotek::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $apotek_id],
        ]);

        if (empty($apotek))
            throw new CustomErrorException(200, 'Apotek not found');

        if (!is_null($apotek->last_izin_id) && $apotek->last_izin_type == Apotek::IZIN_TYPE_BARU)
        {
            $izin = IzinPermohonan::findFirst([
                'conditions' => 'id = :id:',
                'bind' => ['id' => $apotek->last_izin_id],
            ]);    
        }
        elseif (!is_null($apotek->last_izin_id) && $apotek->last_izin_type == Apotek::IZIN_TYPE_PERPANJANGAN)
        {
            $izin = IzinPerpanjangan::findFirst([
                'conditions' => 'id = :id:',
                'bind' => ['id' => $apotek->last_izin_id],
            ]);    
        }

        $now = Utils::DateTimeNow();

        $obj = null;
        if (!empty($izin))
        {
            $sia_id = $izin->sia_id;
            if (!is_null($sia_id))
            {
                $obj = Sia::findFirst([
                    'conditions' => 'id = :id:',
                    'bind' => ['id' => $sia_id,],
                ]);
            }
        }

        if (empty($obj))
        {
            $obj = new Sia();
            $obj->id = $this->random->uuid();
            $obj->account_id = $apotek->account_id;
        }
        $obj->account_id = $this->shared->account->id;
        $obj->apotek_id = $apotek_id;
        $obj->sia_time = $now;
        $apotek->sia_id = $obj->id;

        if (!is_null($izin))
        {
            $izin->sia_id = $obj->id;
        }
        if (!is_null($state))
        {
            $obj->state = $state;
            $apotek->state_sia = $state;
        }
        if (!is_null($sia_number))
        {
            $obj->sia_number = $sia_number;
            $apotek->sia = $sia_number;
        }
        if (!is_null($sia_expired))
        {
            $obj->sia_expired = $sia_expired;
            $apotek->sia_expired = $sia_expired;
        }
        if (!is_null($sia_remarks))
        {
            $obj->sia_remarks = $sia_remarks;
        }
        
        if ($this->request->hasFiles())
        {
            $files = $this->request->getUploadedFiles();					
            foreach($files as $file)
            {
                if ($file->getKey() == 'sia_file')
                {
                    $obj->updateFile($file);
                    $obj->sia_file = $file->getName();
                    $obj->sia_time = $now;
                }
            }
        }

        $obj->save();
        $apotek->save();

        $result->success = true;
        $result->data = $obj;

		return new ObjectResponse($result);
	}

	public function update()
	{
        $rawBody = $this->request->getPost();
        $apotek = null;

		// TODO: user hanya boleh update bbrp state saja
        if ($this->checkRoles([Account::ROLE_USER]))
        {
            $apotek = Apotek::findFirst([
                'conditions' => 'account_id = :id:',
                'bind' => ['id' => $this->shared->account->id],
            ]);

            if (empty($apotek))
                throw new CustomErrorException(200, 'Apotek not found for role user');
        }
        else if ($this->checkRoles([Account::ROLE_PTSP]))
        {
            $validatedRequest = $this->validate(
                $rawBody,
                [
                    'apotek_id' => ['validators' => ['required', 'uuid']],
                ]
            );
    
            $apotek_id = $rawBody['apotek_id'];

            $apotek = Apotek::findFirst([
                'conditions' => 'id = :id:',
                'bind' => ['id' => $apotek_id],
            ]);
            
            if (empty($apotek))
                throw new CustomErrorException(200, 'Apotek not found');
        }
		else
            throw new CustomErrorException(200, 'Invalid role');
        
        if (is_null($apotek->sia_id))
            throw new CustomErrorException(200, 'SIA id null');

        $id = $apotek->sia_id;
		$state = array_key_exists('state', $rawBody) ? $rawBody['state'] : null;
		$sia_number = array_key_exists('sia_number', $rawBody) ? $rawBody['sia_number'] : null;
		$sia_expired = array_key_exists('sia_expired', $rawBody) ? $rawBody['sia_expired'] : null;
		$sia_remarks = array_key_exists('sia_remarks', $rawBody) ? $rawBody['sia_remarks'] : null;
		$revoke_remarks = array_key_exists('revoke_remarks', $rawBody) ? $rawBody['revoke_remarks'] : null;
		$return_remarks = array_key_exists('return_remarks', $rawBody) ? $rawBody['return_remarks'] : null;

		$result = new stdClass();
		$result->success = false;

		if (TypeValidator::IsUUIDv4($id))
		{
			$obj = Sia::findFirst([
				'conditions' => 'id = :id:',
				'bind' => ['id' => $id],
			]);

			if (empty($obj))
			{
				$result->message = 'SIA id not found';
			}
			else
			{
                $now = Utils::DateTimeNow();
                $obj->updated_time = $now;
				if (!is_null($state)) $obj->state = $state;
				if (!is_null($name)) $obj->name = $name;
				if (!is_null($sia_number)) $obj->sia_number = $sia_number;
				if (!is_null($sia_expired)) $obj->sia_expired = $sia_expired;
				if (!is_null($sia_remarks)) $obj->sia_remarks = $sia_remarks;
                if (!is_null($revoke_remarks))
                {
                    $obj->revoke_remarks = $revoke_remarks;
                }
                if (!is_null($return_remarks))
                {
                    $obj->return_remarks = $return_remarks;
                    $obj->return_time = $now;
                }
                
				if ($this->request->hasFiles())
				{
					$files = $this->request->getUploadedFiles();					
					foreach($files as $file)
					{
						if ($file->getKey() == 'sia_file')
						{
							$obj->updateFile($file);
                            $obj->sia_file = $file->getName();
                            $obj->sia_time = $now;
						}
						elseif ($file->getKey() == 'revoke_file')
						{
							$obj->updateFile($file);
							$obj->revoke_file = $file->getName();
                            $obj->revoke_time = $now;
						}
					}
				}

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
    
    public function downloadsia()
	{
		// add role check!
		
		try
		{
            $rawBody = $this->request->getPost();
            $rawBody['key'] = 'sia_file';
			return $this->downloadAction(Sia::class, $rawBody);
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
    }
    
    public function downloadrevoke()
	{
		// add role check!
		
		try
		{
            $rawBody = $this->request->getPost();
            $rawBody['key'] = 'revoke_file';
			return $this->downloadAction(Sia::class, $rawBody);
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
	}
}