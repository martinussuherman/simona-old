<?php

use \Firebase\JWT\JWT;

class AccountController extends BaseController
{
	public function index()
	{
		$getParams = $this->request->get();

		if ($this->checkRoles([Account::ROLE_USER]))
			$getParams['id'] = $this->shared->account->id;
		elseif (!$this->checkRoles([Account::ROLE_ADMIN]))
			throw new CustomErrorException(200, 'Invalid role');

		$limit = array_key_exists('limit', $getParams) ? $getParams['limit'] : 10;
		$offset = array_key_exists('offset', $getParams) ? $getParams['offset'] : 0;
		$keyword = array_key_exists('keyword', $getParams) ? $getParams['keyword'] : null;
		$id = array_key_exists('id', $getParams) ? $getParams['id'] : null;
		$state = array_key_exists('state', $getParams) ? $getParams['state'] : null;
		$username = array_key_exists('username', $getParams) ? $getParams['username'] : null;
		$name = array_key_exists('name', $getParams) ? $getParams['name'] : null;
		$email = array_key_exists('email', $getParams) ? $getParams['email'] : null;
		$phone = array_key_exists('phone', $getParams) ? $getParams['phone'] : null;
		$ktp = array_key_exists('ktp', $getParams) ? $getParams['ktp'] : null;
		$role = array_key_exists('role', $getParams) ? $getParams['role'] : null;
		$states = array_key_exists('states', $getParams) ? $getParams['states'] : null;
		if (!is_null($state)) $states .= (is_null($states) ? '' : ',') . $state;

		$query = Account::query();
		if (is_null($keyword)) {
			if (!is_null($id)) $this->andWhereId($query, 'id', $id);
			if (!is_null($username)) $this->andWhereLike($query, 'username', $username);
			if (!is_null($name)) $this->andWhereLike($query, 'name', $username);
			if (!is_null($phone)) $this->andWhereExact($query, 'phone', $phone);
			if (!is_null($email)) $this->andWhereLike($query, 'email', $email);
			if (!is_null($ktp)) $this->andWhereExact($query, 'ktp', $ktp);
			if (!is_null($role)) $this->andWhereExact($query, 'role', $role);
			if (!is_null($states)) $this->andWhereExactValues($query, 'state', $states);
		} else // favors keyword
		{
			$this->andWhereKeywords(
				$query,
				$keyword,
				['username', 'name', 'email', 'phone', 'ktp']
			);
		}

		$result = $query->orderBy('updated_time ASC')->execute()->toArray();
		return new PaginatedResponse($result, $limit, $offset);
	}

	public function update()
	{
		$rawBody = $this->request->getPost();

		// TODO: user hanya boleh update bbrp state saja
		if ($this->checkRoles([Account::ROLE_USER]))
			$rawBody['account_id'] = $this->shared->account->id;
		elseif (!$this->checkRoles([Account::ADMIN]))
			throw new CustomErrorException(200, 'Invalid role');

		$validatedRequest = $this->validate(
			$rawBody,
			[
				'id' => ['validators' => ['required', 'uuid']],
			]
		);

		$id = $rawBody['id'];
		$password = array_key_exists('password', $rawBody) ? $rawBody['password'] : null;
		// these can't be changed
		// $state = array_key_exists('state', $rawBody) ? $rawBody['state'] : null;
		// $name = array_key_exists('username', $rawBody) ? $rawBody['username'] : null;
		// $email = array_key_exists('email', $rawBody) ? $rawBody['email'] : null;
		// $ktp = array_key_exists('ktp', $rawBody) ? $rawBody['ktp'] : null;
		// $phone = array_key_exists('phone', $rawBody) ? $rawBody['phone'] : null;

		$result = new stdClass();
		$result->success = false;

		if (TypeValidator::IsUUIDv4($id)) {
			$obj = Account::findFirst([
				'conditions' => 'id = :id:',
				'bind' => ['id' => $id],
			]);

			if (empty($obj)) {
				$result->message = 'Id not found';
			} else {
				if (!is_null($password)) $obj->password = password_hash($password, PASSWORD_DEFAULT);
				// these can't be changed
				// if (!is_null($state)) $obj->state = $state;
				// if (!is_null($name)) $obj->name = $name;
				// if (!is_null($email)) $obj->address = $address;
				// if (!is_null($phone)) $obj->phone = $phone;
				// if (!is_null($ktp)) $obj->ktp = $ktp;
				$obj->updated_time = (new DateTime('NOW'))->format('Y-m-d H:i:s');
				$obj->save();

				$result->success = true;
				$result->data = $obj;
			}
		} else {
			$result->message = 'Invalid id';
		}

		return new ObjectResponse($result);
	}

	public function login()
	{
		try {
			$rawBody = $this->request->getPost();
			$validatedRequest = $this->validate(
				$rawBody,
				[
					'username' => ['validators' => ['required']],
					'password' => ['validators' => ['required']]
				]
			);

			$username = $rawBody['username'];
			$password = $rawBody['password'];

			$api = new ApiUser();
			$success = false;
			$dataUserManagement = $api->ApiLogin($username, $password);
			$result = $api->ApiLogin($username, $password);

			$acc = Account::findFirst([
				'conditions' => 'username = :username:',
				'bind' => ['username' => $username],
			]);

			$response = new stdClass();

			if (!$result) {
				$response->message = 'ApiLogin failed';
				$dataUserManagement = null;
				$success = false;

				if (!empty($acc)) {
					$response->message = 'Account found';

					if (empty($acc->password)) {
						$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
						if ($acc->Password == $hashedPassword) {
							$acc->save();
							$success = true;
						}
					} else {
						$success = password_verify($password, $acc->password);
					}

					if ($acc->role == Account::ROLE_DINKES || $acc->role == Account::ROLE_PTSP) {
						$api = new ApiUser();
						$success = $api->ApiLogin($username, $password);
					}
				}
			} else {
				if (!$acc) {
					$acc = new Account();
					$acc->id = $result->data->account_id;
					$acc->username = $username;
					$acc->name = $username;
					$acc->ktp = "1234561234567890";
					//$acc->phone = $phone;
					$acc->email = $username . "@simona.com";
					$acc->password = "not defined";
					//$acc->created_time = Utils::DateTimeNow(); // automatic by db default value
					$acc->register_ip = $this->request->getClientAddress();

					$acc->role = $result->data->account_role;
					$result = $acc->save();
				}
				$success = true;
			}

			$response->success = $success;

			if ($success) {
				$data = $this->generateToken($acc, $dataUserManagement);
				$response->data = $data;
			} elseif (empty($response->message)) {
				$response->message = 'Login failed';
			}

			return new ObjectResponse($response);
		} catch (AuthException $e) {
			$this->flash->error($e->getMessage());
		}
	}

	public function logout()
	{
		try {
			if (!isset($this->shared->token))
				throw new CustomErrorException(200, 'Invalid authentication');

			$token = $this->shared->token;

			if (empty($token)) {
				throw new CustomErrorException(200, 'Invalid token');
			} else {
				$token->state = Token::STATE_REVOKED;
				$token->save();
			}

			$result = new stdClass();
			$result->success = true;

			return new ObjectResponse($result);
		} catch (AuthException $e) {
			$this->flash->error($e->getMessage());
		}
	}

	public function register()
	{
		try {
			$rawBody = $this->request->getPost();

			$validatedRequest = $this->validate(
				$rawBody,
				[
					'email' => ['validators' => ['required', 'email']],
					'password' => ['validators' => ['required']],
					'username' => ['validators' => ['required']],
					'nama' => ['validators' => ['required']],
					'ktp' => ['validators' => ['required', 'digit']],
					'telepon' => ['validators' => ['required', 'digit']]
				]
			);

			$password = $rawBody['password'];
			$username = $rawBody['username'];
			$name = $rawBody['nama'];
			$ktp = $rawBody['ktp'];
			$phone = $rawBody['telepon'];
			$email = $rawBody['email'];
			$hashedpassword = password_hash($password, PASSWORD_DEFAULT);

			$errMsgs = [];

			if ($this->isDataExists(Account::class, 'username', $username)) {
				$errMsgs[count($errMsgs)] = array(
					'field' => 'username',
					'message' => 'Username has been used.'
				);
			}

			if ($this->isDataExists(Account::class, 'email', $email)) {
				$errMsgs[count($errMsgs)] = array(
					'field' => 'email',
					'message' => 'Email has been used.'
				);
			}

			if ($this->isDataExists(Account::class, 'ktp', $ktp)) {
				$errMsgs[count($errMsgs)] = array(
					'field' => 'ktp',
					'message' => 'KTP has been used.'
				);
			}

			if ($this->isDataExists(Account::class, 'phone', $phone)) {
				$errMsgs[count($errMsgs)] = array(
					'field' => 'telepon',
					'message' => 'Telephone has been used.'
				);
			}

			if (count($errMsgs) > 0) {
				throw new CustomErrorException(200, $errMsgs);
			}

			$acc = new Account();
			$acc->id = $this->random->uuid();
			$acc->username = $username;
			$acc->name = $name;
			$acc->ktp = $ktp;
			$acc->phone = $phone;
			$acc->email = $email;
			$acc->password = $hashedpassword;
			//$acc->created_time = Utils::DateTimeNow(); // automatic by db default value
			$acc->register_ip = $this->request->getClientAddress();
			$acc->role = Account::ROLE_USER;
			$acc->state = Account::STATE_NEW;
			$result = $acc->save();
			$api = new ApiUser();
			$success = $api->ApiRegister($username, $password);
			if (!$result) print_r($acc->getMessages());
			$data = new stdClass();
			$data->message = Utils::MESSAGE_SUCCESS;
			return new ObjectResponse($data);
		} catch (AuthException $e) {
			$this->flash->error($e->getMessage());
		}
	}

	private function generateToken($acc, $dataUserManagement)
	{
		$data = new stdClass();
		$data->account_id = $acc->id;

		$id = $this->random->uuid();
		$refreshToken = $this->random->uuid();
		$tokenExp = time() + 7 * 86400;
		$refreshTokenExp = $tokenExp + 23 * 3600;

		$tokenPayload = [
			"id" => $id,
			"acc" => $acc->id,
			"re" => $refreshToken,
			"exp" => $tokenExp,
			"re_exp" => $refreshTokenExp,
			"role" => $acc->role
		];

		$data->token = JWT::encode($tokenPayload, $this->config['jwt_secret_key']);
		$data->role = $acc->role;

		if ($dataUserManagement != '') {
			$data->wilayah_code = $dataUserManagement->data->wilayah_code;
		} else {
			$data->wilayah_code = null;
		}

		$token = new Token();
		$token->id = $id;
		$token->token = $data->token;
		$token->account_id = $acc->id;
		$token->refresh = $refreshToken;
		$token->refresh_exp = $refreshTokenExp;
		$token->name = $this->request->getHeader("User-Agent");
		$token->state = Token::STATE_ACTIVE;
		$result = $token->save();

		if (!$result) print_r($token->getMessages());

		return $data;
	}

	public function forgotPassword()
	{
		try {
			$rawBody = $this->request->getPost();
			$validatedRequest = $this->validate(
				$rawBody,
				[
					'username' => ['validators' => ['required']],
					'password' => ['validators' => ['required']]
				]
			);

			$username = $rawBody['username'];
			$password = $rawBody['password'];

			$api = new ApiUser();
			$success = $api->ApiForgot($username, $password);

			if (!$success) {
				$acc = Account::findFirst([
					'conditions' => 'username = :username:',
					'bind' => ['username' => $username],
				]);
				$success = false;
				if (empty($acc)) {
					$result->message = 'Account not found';
				} else {
					if ($acc->role == Account::ROLE_DINKES || $acc->role == Account::ROLE_PTSP) {
						$api = new ApiUser();
						$success = $api->ApiForgot($username, $password);
					} else {
						if (!is_null($password)) {
							$acc->password = password_hash($password, PASSWORD_DEFAULT);
							$acc->updated_time = (new DateTime('NOW'))->format('Y-m-d H:i:s');
							$acc->save();
							$success = true;
						}
					}
				}
			}

			$result = new stdClass();
			$result->success = false;
			if ($success) {
				$result->success = $success;
				$result->message = 'Password Changed';
			} else {
				$result->message = 'Password Not Changed';
			}

			return new ObjectResponse($result);
		} catch (AuthException $e) {
			$this->flash->error($e->getMessage());
		}
	}

	public function changePassword()
	{
		try {
			$rawBody = $this->request->getPost();
			$validatedRequest = $this->validate(
				$rawBody,
				[
					'username' => ['validators' => ['required']],
					'oldpassword' => ['validators' => ['required']],
					'newpassword' => ['validators' => ['required']]
				]
			);

			$username = $rawBody['username'];
			$oldpassword = $rawBody['oldpassword'];
			$newpassword = $rawBody['newpassword'];

			$api = new ApiUser();
			$success = $api->ApiChangePass($username, $newpassword, $oldpassword);

			if (!$success) {
				$acc = Account::findFirst([
					'conditions' => 'username = :username:',
					'bind' => ['username' => $username],
				]);
				$success = false;
				if (empty($acc)) {
					$result->message = 'Account not found';
				} else {
					if ($acc->role == Account::ROLE_DINKES || $acc->role == Account::ROLE_PTSP) {
						$api = new ApiUser();
						$success = $api->ApiChangePass($username, $newpassword, $oldpassword);
					} else {
						if (!is_null($newpassword)) {
							$acc->password = password_hash($newpassword, PASSWORD_DEFAULT);
							$acc->updated_time = (new DateTime('NOW'))->format('Y-m-d H:i:s');
							$acc->save();
							$success = true;
						}
					}
				}
			}


			$result = new stdClass();
			$result->success = false;
			if ($success) {
				$result->success = $success;
				$result->message = 'Password Changed';
			} else {
				$result->message = 'Password Not Changed';
			}

			return new ObjectResponse($result);
		} catch (AuthException $e) {
			$this->flash->error($e->getMessage());
		}
	}
}
