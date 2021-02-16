<?php

use Phalcon\Mvc\Controller;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\Email;

class BaseController extends Controller
{
	protected function downloadAction($formDataModel, $rawBody)
	{
		$validatedRequest = $this->validate(
			$rawBody,
			[
				'id' => ['validators' => ['required', 'uuid']],
				'key' => ['validators' => ['required']],
			]
		);
		$id = $rawBody['id'];
		$key = $rawBody['key'];
		
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
				$dir = Utils::UPLOAD_DIR.'/'.get_class($obj).'/'.$obj->id.'/'.$key.'/';
				$files = scandir($dir);
				if (count($files) >= 2 && file_exists($dir.$files[2]))
					return MediaHelper::FileResponse($dir.$files[2]);
				else
					$result->message = 'Key '.$key.' not found';
			}
		}
		else
		{
			$result->message = 'Invalid id';
		}

		return new ObjectResponse($result);
	}
	
	protected function isDataExists($model, $key, $value)
    {
		$result = $model::findFirst([
			'conditions' => $key.' = :value:',
			'bind' => ['value' => $value],
		]);

		return !empty($result);
	}
	
	protected function isRawDataExists($model, $field, $key, $value)
    {
		$query = $model::query();
		$data = $query->execute()->toArray();

		$serialize = array();
		foreach($data as $keys => $item) {
			$data_serialize = json_decode($data[$keys][$field], true);
			array_push($serialize, $data_serialize);
		}

		for ($i = 0; $i <= count($serialize); $i++) {
			$obj = array_key_exists('nib',  $serialize[$i]) ?  $serialize[$i][$key] : null;
			if($obj == null) {
				return false;
				break;
			}
			else {
				if($obj == $value){
					return true;
					break;
				}
			}			
		}
		return false;		
    }

	protected function checkRoles($roles)
	{
		if (!isset($this->shared->account->role))
			throw new CustomErrorException(200, 'Invalid authentication');

		$pass = false;
		foreach ($roles as $role) {
			if ($this->shared->account->role == $role) {
				$pass = true;
				break;
			}
		}

		return $pass;
	}

    protected function andWhereId($query, $key, $value)
    {
		if (TypeValidator::IsUUIDv4($value)) {
			$this->andWhereExact($query, $key, $value);
		}
		else {
			$query->andWhere('1 = 0'); // always false
		}
    }

    protected function andWhereExact($query, $key, $value)
    {
        $where = $key.' = :'.$key.':';
        $bind = [$key => $value];
		$query->andWhere($where, $bind);
    }

    protected function andWhereLike($query, $key, $value, $pre = true, $post = true)
    {
        $where = 'lower('.$key.') like :'.$key.':';
        $bind = [$key => ($pre ? '%' : '').strtolower($value).($post ? '%' : '')];
		$query->andWhere($where, $bind);
    }

	protected function andWhereKeywords($query, $keyword, $keys)
	{
		if (empty($keys)) return;
		$where = 'lower('.implode(') like :keyword: OR lower(', $keys).') like :keyword:';
		$bind = ['keyword' => '%'.strtolower($keyword).'%'];
		$query->andWhere($where, $bind);
	}
	
	protected function andWhereExactValues($query, $key, $values)
	{
		if (is_null($values)) return;
		
		$vals_tmp = array_map('intval', explode(',', $values));
		$vals_bind = array();
		foreach ($vals_tmp as $val_k=>$val_v)
			$vals_bind[$key.$val_k] = $val_v;

		if (count($vals_bind) == 0) return;
		
		$where = '(';
		foreach($vals_bind as $val_k => $val_v)
			$where .= $key.' = :'.$val_k.': OR ';
		$where .= '0=1)';
		
		$query->andWhere($where, $vals_bind);
	}

    public function checkIsAuthenticated()
    {
        if (!isset($this->shared->jwt) || empty($this->shared->jwt)) {
            throw new CustomErrorException(401, 'Invalid authentication');
        }
    }

    public function checkScopes($requiredScopes)
    {
        $this->checkIsAuthenticated();

        $ownedScopes = explode(' ', $this->shared->jwt->scope);

        $hasAllScope = true;

        foreach ($requiredScopes as $key => $requiredScope) {
            if (!in_array($requiredScope, $ownedScopes)) {
                $hasAllScope = false;
            }
        }

        if (!$hasAllScope) {
            throw new CustomErrorException(401, 'User is not allowed to perform this action');
        }
    }

    public function validate($payload, $rules)
    {
        $result = [];
        $validation = new Validation();

        foreach ($rules as $attributeName => $rule) {
            $attributeValue = isset($payload[$attributeName]) ? $payload[$attributeName] : null;
            $result[$attributeName] = $attributeValue;

            if (!empty($rule)) {

                foreach ($rule['validators'] as $key => $validatorName) {
                    $validator = null;

                    $lowerValidatorName = strtolower($validatorName);

                    if ($lowerValidatorName == 'required') {
                        $validator = new PresenceOf();
                    } else if ($lowerValidatorName == 'digit') {
                        $validator = new Digit();
                    } else if ($lowerValidatorName == 'email') {
                        $validator = new Email();
                    }

                    $validation->add($attributeName, $validator);
                }
            }
        }

        $messages = $validation->validate($payload);
        if (count($messages) > 0) {
            $errorMessages = [];

            foreach ($messages as $message) {
                $errorMessages[] = [
                    'field' => $message->getField(),
                    'message' => $message->getMessage()
                ];
            }

            throw new ValidationException($errorMessages);
        }

        return $result;
    }

}