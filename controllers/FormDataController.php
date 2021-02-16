<?php

class FormDataController extends BaseController
{
	protected function indexData($formDataModel, $getParams, $order = null, $qlimit = 0)
	{
		$limit = array_key_exists('limit', $getParams) ? $getParams['limit'] : 10;
		$offset = array_key_exists('offset', $getParams) ? $getParams['offset'] : 0;
		$keyword = array_key_exists('keyword', $getParams) ? $getParams['keyword'] : null;
		$id = array_key_exists('id', $getParams) ? $getParams['id'] : null;
		$account_id = array_key_exists('account_id', $getParams) ? $getParams['account_id'] : null;
		$apotek_id = array_key_exists('apotek_id', $getParams) ? $getParams['apotek_id'] : null;
		$state = array_key_exists('state', $getParams) ? $getParams['state'] : null;
		$name = array_key_exists('name', $getParams) ? $getParams['name'] : null;
		$category = array_key_exists('category', $getParams) ? $getParams['category'] : null;
		$states = array_key_exists('states', $getParams) ? $getParams['states'] : null;
		if (!is_null($state)) $states .= (is_null($states) ? '' : ',').$state;

		$query = $formDataModel::query();
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

			if (!is_null($account_id)) $this->andWhereId($query, 'account_id', $account_id);
			if (!is_null($apotek_id)) $this->andWhereId($query, 'apotek_id', $apotek_id);
			if (!is_null($name)) $this->andWhereLike($query, 'name', $name);
			if (!is_null($category)) $this->andWhereLike($query, 'category', $category);
			if (!is_null($states)) $this->andWhereExactValues($query, 'state', $states);
		}
		else // favors keyword
		{
			if ($this->shared->account->role == Account::ROLE_USER)
				$this->andWhereId($query, 'account_id', $account_id);

			$this->andWhereKeywords($query, $keyword,['name', 'category']);
		}

		if (!is_null($order))
			$query = $query->orderBy($order);
		if (!is_null($qlimit))
			$query = $query->limit($qlimit);

		$result = $query->execute()->toArray();

		if ($qlimit == 1)
			return new SingleResponse($result);
		else
			return new PaginatedResponse($result, $limit, $offset);
    }

	protected function addFormData($formDataModel, $rawBody)
	{
		// $now = Utils::DateTimeNow(); // automatic by db default value

		$validatedRequest = $this->validate(
			$rawBody,
			[
				'apotek_id' => ['validators' => ['required']]
			]
		);
		
		$apotek_id = $rawBody['apotek_id'];		
		unset($rawBody['apotek_id']);

		$obj = new $formDataModel();
		$obj->id = $this->random->uuid();
		$obj->account_id = $this->shared->account->id;
		$obj->apotek_id = $apotek_id;
		// $obj->created_date = $now; // automatic by db default value
		
		// handle files
		if ($this->request->hasFiles())
		{
			$files = $this->request->getUploadedFiles();
			$obj->updateFiles($files);
			
			foreach($files as $file)
			{
				$rawBody[$file->getKey()] = $file->getName();
			}
		}
		
		$obj->raw = json_encode($rawBody);
		$save = $obj->save();

		if (!$save) print_r($obj->getMessages());
		
		return $obj;
	}

	protected function updateData($formDataModel, $rawBody)
	{
		$validatedRequest = $this->validate(
			$rawBody,
			[
				'id' => ['validators' => ['required', 'uuid']],
			]
		);

		$id = $rawBody['id'];
		$account_id = array_key_exists('account_id', $rawBody) ? $rawBody['account_id'] : null;
		$state = array_key_exists('state', $rawBody) ? $rawBody['state'] : null;
		$sia = array_key_exists('sia', $rawBody) ? $rawBody['sia'] : null;
		$sia_expired = array_key_exists('sia_expired', $rawBody) ? $rawBody['sia_expired'] : null;
		$name = array_key_exists('name', $rawBody) ? $rawBody['name'] : null;
		$category = array_key_exists('category', $rawBody) ? $rawBody['category'] : null;
		
		$result = new stdClass();
		$result->success = false;

		if (TypeValidator::IsUUIDv4($id))
		{
			$obj = $formDataModel::findFirst([
				'conditions' => 'id = :id:',
				'bind' => ['id' => $id],
			]);

			if (empty($obj))
			{
				$result->message = 'Id not found';
			}
			elseif (!is_null($account_id) && $account_id != $obj->account_id)
			{
				$result->message = 'Invalid account id';
			}
			elseif (!empty($this->shared->account->provinsi) && $this->shared->account->provinsi == $obj->provinsi)
			{
				$result->message = 'Invalid provinsi';
			}
			elseif (!empty($this->shared->account->kabupaten) && $this->shared->account->kabupaten == $obj->kabupaten)
			{
				$result->message = 'Invalid kabupaten';
			}
			else
			{
				$now = Utils::DateTimeNow();
				if (!is_null($state)) $obj->state = $state;
				if (!is_null($name)) $obj->name = $name;
				if (!is_null($category)) $obj->category = $category;
				//if (!is_null($sia)) $obj->sia = $sia;
				//if (!is_null($sia_expired)) $obj->sia_expired = $sia_expired;
				$obj->updated_time = $now;
				$obj->save();
				
				// if (!is_null($sia) || !is_null(!$sia_expired))
				// {
				// 	$apotek = Apotek::findFirst([
				// 		'conditions' => 'id = :id:',
				// 		'bind' => ['id' => $obj->apotek_id],
				// 	]);

				// 	if (empty($apotek))
				// 	{
				// 		$result->message = 'Cannot update apotek sia because apotek not found';
				// 	}
				// 	else
				// 	{
				// 		if (!is_null($sia)) $apotek->sia = $sia;
				// 		if (!is_null($sia_expired)) $apotek->sia_expired = $sia_expired;
				// 		$apotek->updated_time = $now;
				// 		$apotek->save();
				// 	}
				// }

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
	
	protected function updateRawData($formDataModel, $rawBody)
	{
		$validatedRequest = $this->validate(
			$rawBody,
			[
				'id' => ['validators' => ['required', 'uuid']],
			]
		);

		$id = $rawBody['id'];
		unset($rawBody['id']);
		
		$result = new stdClass();
		$result->success = false;

		if (TypeValidator::IsUUIDv4($id))
		{
			$obj = $formDataModel::findFirst([
				'conditions' => 'id = :id:',
				'bind' => ['id' => $id],
			]);

			if (empty($obj))
			{
				$result->message = 'Id not found';
			}
			elseif (!empty($this->shared->account->provinsi) && $this->shared->account->provinsi == $obj->provinsi)
			{
				$result->message = 'Invalid provinsi';
			}
			elseif (!empty($this->shared->account->kabupaten) && $this->shared->account->kabupaten == $obj->kabupaten)
			{
				$result->message = 'Invalid kabupaten';
			}
			else
			{
				$now = Utils::DateTimeNow();
				$obj->updated_time = $now;

				// handle files
				if ($this->request->hasFiles())
				{
					$files = $this->request->getUploadedFiles();
					$obj->updateFiles($files);
					
					foreach($files as $file)
					{
						$rawBody[$file->getKey()] = $file->getName();
					}
				}
				
				$obj->raw = json_encode($rawBody);				
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

	protected function generatedocAction($formDataModel, $rawBody, $template)
	{
		$result = new stdClass();
		$result->success = false;

		try
		{
			$validatedRequest = $this->validate($rawBody, ['id' => ['validators' => ['required', 'uuid']]]);
			$id = $rawBody['id'];

			if (TypeValidator::IsUUIDv4($id))
			{
				$obj = $formDataModel::findFirst([
					'conditions' => 'id = :id:',
					'bind' => ['id' => $id],
				]);
	
				if (empty($obj))
				{
					$result->message = 'Id not found';
				}
				else
				{
					$raw = json_decode($obj->raw);
					$nib = property_exists($raw, 'nib') ? $raw->nib : '0';
					$file_template = Utils::TEMPLATE_DIR.'/'.$template.'.docx';
					$dir = Utils::UPLOAD_DIR.'/'.get_class($obj).'/'.$obj->id.'/docxfile/';
					$file_target = $dir.$template.' NIB-'.$nib.'.docx';

					// TODO: prevent file racing, lock? filename with time? delete after use?
					Utils::CreateDir($dir);
					copy($file_template, $file_target);
					$zip = new ZipArchive;

					if ($zip->open($file_target) == true)
					{
						$doc_xml = 'word/document.xml';
						$content = $zip->getFromName($doc_xml);
		
						if (preg_match_all("'<w:t>{(.*?)}</w:t>'si", $content, $matches))
						{
							foreach ($matches[1] as $rawkey)
							{
								$key = strip_tags($rawkey);
								$replace = null;

								if (strlen($key) >= 1 && substr($key, 0, 1) === '#')
								{
									$val = substr($key, 1);
									$replace = property_exists($raw, $val) ? $raw->$val : '';
								}
								elseif(strlen($key) >= 2 && substr($key, 1, 1) === '#')
								{
									$val = substr($key, 2);
									$radio = substr($key, 0, 1) === '1' ? 'Ya' : 'Tidak';
									$replace = property_exists($raw, $val) && strcasecmp($raw->$val, $radio) == 0 ? '✓' : '';
								}

								if (!is_null($replace))
									$content = preg_replace("'<w:t>{($rawkey)}</w:t>'si", "<w:t>$replace</w:t>", $content);
							}
						}

						$zip->addFromString($doc_xml, $content);
						$zip->close();

						$result->success = true;
						$result->message = $file_target;
						return MediaHelper::FileResponse($file_target);
					}
					else
					{
						$result->message = 'docx file error';
					}
				}
			}
			else
			{
				$result->message = 'Invalid id';
			}
		}
		catch (Exception $ex)
		{
			$result->message = $ex->getMessage();
		}

		return new ObjectResponse($result);
	}
}