<?php

namespace vendor\dmitri;

class Curlo
{
    const VERSION = '2019-10-24';

    private
        $agent  = 'Mozilla/5.0 (Windows; I; Windows NT 5.1; en-GB; rv:1.9.2.13) Gecko/20100101 Firefox/20.0',
        $needHeader     = false,
        $follow         = false,
        $referer        = 'http://google.com/',
        $timeConnect    = 30,
        $timeout        = 30,
        $cookiefile     = '/tmp/cookie.txt';

    public function send($url, $data=null)
    {
        touch($this->cookiefile);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, $this->needHeader);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->follow);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_REFERER, $this->referer);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeConnect);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookiefile);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookiefile);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        if ($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public static function get($url, $needHeader=false)
    {
        $this->needHeader   = $needHeader;
        return $this->send($url);
    }

    public function post($url, $data, $needHeader=false)
    {
        if (!$data) {
            return '';
        }

        $this->needHeader   = $needHeader;

        $data = $this->prepareData($data);

        return $this->send($url, $data);
    }

    protected function prepareData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $str = [];

        foreach ($data as $key => $value) {
            $str[] = $key . '=' . $value;
        }

        $str = implode('&', $str);

        return $str;
    }

    public function setCookieFile($absoluteFilename)
    {
        $this->cookiefile   = $absoluteFilename;

        if (file_exists($absoluteFilename)) {
            unlink($absoluteFilename);
        }
    }

    public function setTimeout($timeout=30)
    {
        if (is_numeric($timeout)) {
            $this->timeout  = $timeout;
        }
    }

    public function needHeader($needHeader=true)
    {
        $this->needHeader   = $needHeader;
    }

    public function followRedirect($follow=true)
    {
        $this->follow   = $follow;
    }

    public static function setReferer($referer)
    {
        $this->referer = $referer;
    }

    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    public function setTime2connect($timeConnect=30)
    {
        if (is_numeric($timeConnect)) {
            $this->timeConnect  = intval($timeConnect);
        }
    }

}
