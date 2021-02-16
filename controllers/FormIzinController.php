<?php

class FormIzinController extends FormDataController
{
	protected function updateData($formDataModel, $rawBody)
	{
		$validatedRequest = $this->validate(
			$rawBody,
			[
				'id' => ['validators' => ['required', 'uuid']],
			]
		);
		$id = $rawBody['id'];
		$state = array_key_exists('state', $rawBody) ? $rawBody['state'] : null;
		$name = array_key_exists('name', $rawBody) ? $rawBody['name'] : null;
		$category = array_key_exists('category', $rawBody) ? $rawBody['category'] : null;
		//$sia = array_key_exists('sia', $rawBody) ? $rawBody['sia'] : null;
		//$sia_expired = array_key_exists('sia_expired', $rawBody) ? $rawBody['sia_expired'] : null;
		//$sia_remarks = array_key_exists('sia_remarks', $rawBody) ? $rawBody['sia_remarks'] : null;
		$reject_remarks = array_key_exists('reject_remarks', $rawBody) ? $rawBody['reject_remarks'] : null;
		//$revoke_remarks = array_key_exists('revoke_remarks', $rawBody) ? $rawBody['revoke_remarks'] : null;
		//$return_remarks = array_key_exists('return_remarks', $rawBody) ? $rawBody['return_remarks'] : null;
		
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
			else
			{
				$now = Utils::DateTimeNow();
				if (!is_null($state)) $obj->state = $state;
				if (!is_null($name)) $obj->name = $name;
				if (!is_null($category)) $obj->category = $category;
				//if (!is_null($sia)) $obj->sia = $sia;
				//if (!is_null($sia_expired)) $obj->sia_expired = $sia_expired;
				//if (!is_null($sia_remarks)) $obj->sia_remarks = $sia_remarks;
				if (!is_null($reject_remarks)) $obj->reject_remarks = $reject_remarks;
				//if (!is_null($revoke_remarks)) $obj->revoke_remarks = $revoke_remarks;
				//if (!is_null($return_remarks)) $obj->return_remarks = $return_remarks;
				$obj->updated_time = $now;
				
				// handle files (moved to SiaController)
				// if ($this->request->hasFiles())
				// {
				// 	$files = $this->request->getUploadedFiles();					
				// 	foreach($files as $file)
				// 	{
				// 		if ($file->getKey() == 'sia_file')
				// 		{
				// 			$obj->updateFile($file);
				// 			$obj->sia_file = $file->getName();
				// 		}
				// 		elseif ($file->getKey() == 'revoke_file')
				// 		{
				// 			$obj->updateFile($file);
				// 			$obj->revoke_file = $file->getName();
				// 		}
				// 	}
				// }
				
				$obj->save();
				
				// if (!is_null($sia) && !is_null($sia_expired))
				// {
				// 	$apotek = is_null($obj->apotek_id) ? null :
				// 		Apotek::findFirst([
				// 			'conditions' => 'id = :id:',
				// 			'bind' => ['id' => $obj->apotek_id],
				// 		]);
					
				// 	if (!empty($apotek))
				// 	{
				// 		$apotek->sia = $sia;
				// 		$apotek->sia_expired = $sia_expired;
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
		$search_key = 'id';
		$search_id = null;

		if ($this->checkRoles([Account::ROLE_USER]))
		{
			$validatedRequest = $this->validate(
				$rawBody,
				[
					'apotek_id' => ['validators' => ['required', 'uuid']],
				]
			);

			$search_key = 'apotek_id';
			$search_id = $rawBody['apotek_id'];
			unset($rawBody['apotek_id']);
			// TODO: pengecekan apakah apotek_id sesuai dengan account_id pengguna
		}
		// elseif ($this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
		// {
		// 	$validatedRequest = $this->validate(
		// 		$rawBody,
		// 		[
		// 			'apotek_id' => ['validators' => ['required', 'uuid']],
		// 		]
		// 	);

		// 	$search_key = 'apotek_id';
		// 	$search_id = $rawBody['apotek_id'];
		// 	unset($rawBody['apotek_id']);
		// }
		else throw new CustomErrorException(200, 'Invalid role');
		
		$result = new stdClass();
		$result->success = false;

		if (TypeValidator::IsUUIDv4($search_id))
		{
			$obj = $formDataModel::findFirst([
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
}