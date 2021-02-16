<?php
class ApiOss
{
	public function Token($grant_type, $username, $password)
	{
        $url = 'http://api.prod-simyanfar.sangkuriang.co.id/token';
        $data = 'grant_type='.$grant_type.'&username='.$username.'&password='.$password;         
        $result = json_decode($this->PostData($url, $data));
        return $result;
    }

    public function DetailNIB($nib, $access_token)
	{
        $url = 'http://api.prod-simyanfar.sangkuriang.co.id/oss/0.1/detailNIB?nib='.$nib;
        $result = json_decode($this->GetData($url, $access_token));
        return $result;
    }

    public function PostData($url, $json)
    {
        $ch = curl_init($url);

        $headr = array();
        $headr[] = 'cache-control: no-cache';
        $headr[] = 'Content-type: application/x-www-form-urlencoded';
        $headr[] = 'Authorization: Basic YWxBVUZDNlU5MXo1b2Zmc3lIcmY1Yk9HX3FzYTptVGFiU3R1WkZrelpxTm0zWGVUQjJMSzhMTk1h';
        $headr[] = 'Content-length: '. strlen($json);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function GetData($url, $access_token)
    {
        $ch = curl_init($url);

        $headr = array();
        $headr[] = 'Accept: application/json';
        $headr[] = 'Authorization: Bearer '.$access_token;

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
}
