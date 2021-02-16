<?php

class BAPPerpanjanganController extends FormDataController
{
	public function index()
	{
		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		$getParams = $this->request->get();
		return $this->indexData(BAPPerpanjangan::class, $getParams);
	}

	public function latest()
	{
		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		$getParams = $this->request->get();
		return $this->indexData(BAPPerpanjangan::class, $getParams, 'updated_time DESC', 1);
	}

	public function update()
	{
		$rawBody = $this->request->getPost();

		if (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
			throw new CustomErrorException(200, 'Invalid role');

		return $this->updateData(BAPPerpanjangan::class, $rawBody);
	}
	
	public function add()
	{
		if (!$this->checkRoles([Account::ROLE_DINKES]))
			throw new CustomErrorException(200, 'Invalid role');

		try
		{
			$rawBody = $this->request->getPost();
			$this->addFormData(BAPPerpanjangan::class, $rawBody);

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
			return $this->downloadAction(BAPPerpanjangan::class, $rawBody);
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
			return $this->generatedocAction(IzinPerBAPPerpanjanganmohonan::class, $rawBody, 'FORM BAP PERPANJANGAN IZIN APOTEK');
		}
		catch (AuthException $e)
		{
            $this->flash->error($e->getMessage());
        }
	}
}