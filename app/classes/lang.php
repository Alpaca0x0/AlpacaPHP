<?php
class Lang{
    public string $code;
    public array|false $data = false;

    private function getBrowserLanguage(){
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if(preg_match('/^([a-z]{2}(-[A-Z]{2})?)/i', $acceptLang, $matches)){
            $parts = explode('-', $matches[1]);
            return count($parts) === 2 ? strtolower($parts[0]).'-'.strtoupper($parts[1]) : strtolower($parts[0]);
        }
        return 'en-US';
    }

    public function __construct(string|false $lang=false){
        $hadCookie = isset($_COOKIE['lang']);
        if(!$lang){ $lang = $hadCookie ? $_COOKIE['lang'] : $this->getBrowserLanguage(); }
        $lang = preg_replace('/[^a-zA-Z0-9\-]+/', '', $lang);
        if(!$hadCookie && !headers_sent()){
            setcookie('lang', $lang, [
                'expires' => time() + 60 * 60 * 24 * 365, // 1 year
                'path' => ROOT,
                'domain' => DOMAIN,
            ]);
        }
        $this->code = $lang;
    }

    // load a language file, e.g. load('common') => langs/common/{code}.php
    public function load(string $filename, bool $append=true){
        $file = File::in(Path::lang)::try($filename.'/'.strtolower($this->code), ['.php']);
        $data = $file ? include($file) : false;
        if($append && $this->data && is_array($this->data) && is_array($data)){
            $this->data = array_merge($this->data, $data);
        }else{
            $this->data = $data;
        }
        return $this->data;
    }

    // get a translated value by dot-notation key, e.g. get('page.title')
    public function get(string $key, $vars=[]){
        $value = $this->data;
        foreach(explode('.', $key) as $segment){
            if(!is_array($value) || !array_key_exists($segment, $value)){ return null; }
            $value = $value[$segment];
        }
        if(is_string($value) && $vars){
            $value = preg_replace_callback('/\{\{([a-zA-Z0-9_]+)\}\}/', function($matches) use ($vars){
                return array_key_exists($matches[1], $vars) && is_scalar($vars[$matches[1]]) ? (string)$vars[$matches[1]] : $matches[0];
            }, $value);
        }
        return $value;
    }

    // dump loaded data as JSON, e.g. for handing to frontend JS
    public function dump(){
        return json_encode($this->data ?: (object)[], JSON_UNESCAPED_UNICODE);
    }
}
