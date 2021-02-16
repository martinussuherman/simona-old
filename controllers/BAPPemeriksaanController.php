<?php

class BAPPemeriksaanController extends FormDataController
{
	public function index()
	{
		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		$getParams = $this->request->get();
		return $this->indexData(BAPPemeriksaan::class, $getParams);
	}

	public function latest()
	{
		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		$getParams = $this->request->get();
		return $this->indexData(BAPPemeriksaan::class, $getParams, 'updated_time DESC', 1);
	}
	
	public function update()
	{
		$rawBody = $this->request->getPost();

		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->updateData(BAPPemeriksaan::class, $rawBody);
	}

	public function add()
	{
		if (!$this->checkRoles([Account::ROLE_DINKES]))
			throw new CustomErrorException(200, 'Invalid role');

		try
		{
			$rawBody = $this->request->getPost();
			$this->addFormData(BAPPemeriksaan::class, $rawBody);

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
			return $this->downloadAction(BAPPemeriksaan::class, $rawBody);
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
			return $this->generatedocAction(BAPPemeriksaan::class, $rawBody, 'FORM BAP PEMERIKSAAN APOTEK');
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
	}
}