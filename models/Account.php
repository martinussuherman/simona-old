<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\Email as EmailValidator;

class Account extends BaseModel
{
	const ROLE_ADMIN = 'admin';
	const ROLE_DINKES = 'dinkes';
	const ROLE_USER = 'user';
    const ROLE_PTSP = 'ptsp';
    
    const STATE_NEW = '0';
    const STATE_VERIFY = '1';
    const STATE_FORGOT = '2';

	public $ktp;
	public $phone;
	public $password;
	public $email;
	public $register_ip;
	public $last_ip;
    public $role;
    public $kabupaten;
    public $provinsi;
    public $wilayah_code;

	public function getSource()
    {
        return 'account';
    }

	public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model' => $this,
                    'message' => 'please enter a correct email address',
                ]
            )
        );

        return $this->validate($validator);
    }
}