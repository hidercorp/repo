<?php
define('USERAGENT_USERAGENT', "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36");

define('USERAGENT_SB_MAX_HTTP_RETRIES', 3);
define('USERAGENT_METHOD_GET', "get");
define('USERAGENT_METHOD_POST', "post");
define('USERAGENT_METHOD_OPTIONS', "options");

class UserAgent2{
    private $debug;
    private $lastErrorNo;
    private $lastErrorStr;
    private $ch;
    private $lastStatus;
    private $lastHeaders;

    function __construct($cookie_file, $debug=false){
        $this->setDebug($debug);
        $this->curlSetCookie($cookie_file);
        $this->curlInit();
    }

    function clearCookies(){
        if($this->cookie_file && file_exists($this->cookie_file)){
            unlink($this->cookie_file);
        }
    }

    function getStatus(){
        return $this->lastStatus;
    }

    function getHeader($name=false){
        if($name){
            return isset($this->lastHeaders[$name]) ? $this->lastHeaders[$name] : false;
        }
        return $this->lastHeaders;
    }

    function curlSetCookie($cookie_file){
        $this->cookie_file = $cookie_file;
    }

    function getMyIP(){
        $content = $this->request(USERAGENT_METHOD_GET, "http://myip.ru/index_small.php");
        if(preg_match("#<tr><td>([0-9]+)\\.([0-9]+)\\.([0-9]+)\\.([0-9]+)</td></tr>#", $content, $m)){
            return "{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}";
        }
        return false;
    }

    public function curlInit(){
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, USERAGENT_USERAGENT);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
    }

    function closeCurl(){
        curl_close($this->ch);
    }

    function __destruct(){
        $this->closeCurl();
    }

    function setProxy($proxy, $proxyAuth=false, $proxyType=CURLPROXY_HTTP, $basicAuth=false, $proxyTunnel=false){
        if($proxy){
            curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
            curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, $proxyTunnel);
            if($proxyAuth){
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxyType);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
            }
            if($basicAuth){
                curl_setopt($this->ch, CURLOPT_PROXY, CURLAUTH_BASIC);
            }
        }
    }


    private function trace($msg){
        if($this->debug){
            echo "";
        }
    }

    function setDebug($debug_mode){
        $this->debug = $debug_mode;
    }

    function request($method, $url, $ref=false, $data=false, $additionalHeaders=null, $verbose=false){
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_VERBOSE, $this->debug || $verbose);
        $headers = array(
        );
        if($additionalHeaders){
            foreach($additionalHeaders as $k=>$v){
                $headers[$k] = $v;
            }
        }
        $tmp = $headers;
        $headers = array();
        foreach($tmp as $k=>$v){
            $headers[] = "$k: $v";
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        if($ref) {
            curl_setopt($this->ch, CURLOPT_REFERER, $ref);
        }else{
            curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
        }

        $ret = false;
        $max_retry = USERAGENT_SB_MAX_HTTP_RETRIES;
        while(true) {
            $data = curl_exec($this->ch);
            $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
            $header = substr($data, 0, $header_size);
            $ret = substr($data, $header_size);

            $tmp = preg_split("/\n/", $header);
            $header = array();
            foreach($tmp as $line){
                if(preg_match("/^([^:]+): (.*)$/", $line, $m)){
                    $header[$m[1]] = trim($m[2]);
                }
            }

            $this->lastErrorNo = curl_errno($this->ch);
            $this->lastErrorStr = curl_error($this->ch);
            $this->lastStatus = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            $this->lastHeaders = $header;

            if(in_array($this->lastErrorNo, array(CURLE_OPERATION_TIMEOUTED, CURLE_COULDNT_CONNECT, CURLE_RECV_ERROR)) && $max_retry > 0){
                $this->trace("HTTP ERROR: Timed out. Retry ($max_retry left)...");
                $max_retry--;
                continue;
            }
            if($this->lastErrorNo != 0){
                $this->trace("HTTP ERROR: {$this->lastErrorStr} [{$this->lastErrorNo}]");
            }
            break;
        }

        $ret = trim($ret);

        return $ret;
    }

    function setCurlOption($opt, $val){
        curl_setopt($this->ch, $opt, $val);
    }

    function postForm($url, $postData, $ref=false){
        $headers = array(
            'Content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        );
        return $this->request(USERAGENT_METHOD_POST, $url, $ref, $postData, $headers);
    }

    function saveFromUrl($url, $outFile, $binary=false){
        $data = $this->request(USERAGENT_METHOD_GET, $url);
        if(!($fp = fopen($outFile, "w" . ($binary ? "b" : "")))){
            $this->lastErrorStr = "Failed to create file $outFile";
            return false;
        }
        fwrite($fp, $data);
        fclose($fp);
        return true;
    }
}
