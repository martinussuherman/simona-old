<?php
class ApiUser
{
	public function ApiLogin($username, $password)
	{
        $url = 'https://usermanagement-simyanfar.kemkes.go.id/api/auth';
        $data = array(
            'username' => $username,
            'password' => $password,
            'app' => 'Simyanfar-Simona',
        );
         
        $payload = json_encode($data);
        $result = json_decode($this->PostData($url, $payload));
        if(!empty($result->success))
        {
            return $result;
        }
        else
        {
            return false;
        }
    }

    public function ApiForgot($username, $password)
	{
        $url = 'https://usermanagement-simyanfar.kemkes.go.id/api/auth/forgot-password';
        $data = array(
            'username' => $username,
            'password' => $password,
            'app' => 'Simyanfar-Simona',
        );
         
        $payload = json_encode($data);
        $result = json_decode($this->PostData($url, $payload));
        if(!empty($result->success))
        {
            return $result->success;
        }
        else
        {
            return false;
        }
    }

    public function ApiChangePass($username, $new_password, $old_password)
	{
        $url = 'https://usermanagement-simyanfar.kemkes.go.id/api/auth/change-password';
        $data = array(
            'username' => $username,
            'newpassword' => $new_password,
            'oldpassword' => $old_password,
            'app' => 'Simyanfar-Simona',
        );
         
        $payload = json_encode($data);
         
        $result = json_decode($this->PostData($url, $payload));
        if(!empty($result->success))
        {
            return $result->success;
        }
        else
        {
            return false;
        }
    }

    public function ApiRegister($username, $password)
	{
        $url = 'https://usermanagement-simyanfar.kemkes.go.id/api/user';
        $data = array(
            'username' => $username,
            'password' => $password,
            'role'=>'user',
            'app' => 'Simyanfar-Simona'
        );
         
        $payload = json_encode($data);
         
        return json_decode($this->PostData($url, $payload));
    }

    public function PostData($url, $json)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json))
        );
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function GetData($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
}
