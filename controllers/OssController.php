<?php

use Phalcon\Mvc\Controller;

class OssController extends BaseController
{
    public function token()
    {
        try {

            if ($this->checkRoles([Account::ROLE_USER]))
                $getParams['account_id'] = $this->shared->account->id;
            elseif (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
                throw new CustomErrorException(200, 'Invalid role');

            $grant_type = 'password';
            $username = 'simyanfar';
            $password = 'svi0G4SEI3OlOp7nxMftXnbdCpr5sJ';

            $api = new ApiOss();	
            $result = $api->Token($grant_type, $username, $password);
            
            return $result;
        }
        catch (AuthException $e) {
            $this->flash->error($e->getMessage());
        }        
    }

    public function detailNIB()
    {
        try {

            if ($this->checkRoles([Account::ROLE_USER]))
                $getParams['account_id'] = $this->shared->account->id;
            elseif (!$this->checkRoles([Account::ROLE_DINKES, Account::ROLE_PTSP]))
                throw new CustomErrorException(200, 'Invalid role');

            $getParams = $this->request->get();
            $data_token = $this->token();
            $access_token = $data_token->access_token;

            $nib = array_key_exists('nib', $getParams) ? $getParams['nib'] : null;

            $api = new ApiOss();	
            $result = $api->DetailNIB($nib, $access_token);

            return $result;
        }
        catch (AuthException $e) {
            $this->flash->error($e->getMessage());
        }
    }
}
