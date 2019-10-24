<?php

namespace vendor\dmitri;

class USD2Currency
{

    private $cache      = false;
    private $cachetime  = 12; // in hours
    private $cachefile  = 'currencies.txt';
    private $data;
    private $apiKEY;

    public function __construct($apiKey, $cachetime=12, $cachefile='currencies.txt')
    {
        $this->apiKEY   = $apiKey;
        
        if($cachetime) {
            $this->cachetime    = $cachetime;
        }
        
        if($cachefile) {
            $this->cachefile    = $cachefile;
        }
    }
    
    public function get($cur='EUR', $amount=1)
    {
        $this->listing();

        if(!self::$data['rates']) {
            return false;
        }
        
        $cur    = strtoupper(trim($cur));
        $data   = self::$data['rates'];

        if ('USD' == $cur) {
            $data['result'] = $amount;
            return $data;
        }

        foreach (self::$data['rates'] as $code => $val) {
            if ($code != $cur) {
                continue;
            }

            $data['result'] = $amount * $val;
            return $data;
        }

        return false;
    }

    public function listing()
    {
        $data       = $this->getNewCurrencies();
        $this->data = json_decode($data, true);
    }

    protected function getNewCurrencies()
    {
        $data   = $this->getCacheData();

        if ($data) {
            return $data;
        }

        $url    = 'http://data.fixer.io/api/latest?access_key=' . $this->apiKEY . '&base=USD';
        $data   = file_get_contents($url);

        if ($this->cache) {
            file_put_contents($this->cachefile, $data);
        }

        return $data;
    }

    protected function getCacheData()
    {
        if (!$this->cache || !file_exists($this->cachefile)) {
            return null;
        }

        $delta      = time() - filectime($this->cachefile);
        $cachetime  = $this->cachetime * 3600;

        if ($delta > $cachetime) {
            return null;
        }

        $data = file_get_contents($this->cachefile);

        if ($data) {
            return $data;
        }

        return null;
    }
}
