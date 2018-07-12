<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . "simple_html_dom.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "UserAgent2.php");

define('QIWI_HOST', "qiwi.com");
define('QIWI_URL_MAIN', "https://" . QIWI_HOST);
define('QIWI_URL_MAINACTION', QIWI_URL_MAIN . "/main.action");
define('QIWI_STS', "sts");
define('QIWI_STATUS_SUCCESS', "status_SUCCESS");
define('QIWI_STATUS_ERROR', "status_ERROR");
define('QIWI_STATUS_PROCESSED', "status_PROCESSED");
define('QIWI_STATUS_PAID', "status_PAID");
define('QIWI_STATUS_CANCELED', "status_CANCELED");
define('QIWI_STATUS_AWAITING_CONFIRM', "status_AWAITING_CONFIRM");
define('QIWI_STATUS_NOT_PAID', "status_NOT_PAID");
define('QIWI_BILLS_MODE_IN', 1);
define('QIWI_BILLS_MODE_OUT', 2);
define('QIWI_BILLS_MODE_INOUT', 3);
define('QIWI_SETTINGS_VERSION', "3.6.0");
define('QIWI_CURRENCY_RUB', "643");
define('QIWI_CURRENCY_USD', "840");
define('QIWI_CURRENCY_EUR', "978");
define('QIWI_CURRENCY_KAZ', "398");

class QIWIControl{
    private $id;
    private $password;
    private $auth_ticket;
    private $sts_auth_ticket;
    private $auth_links;
    private $logged_in;
    private $debug;
    private $cookie_file;
    private $proxy;
    private $proxyAuth;
    private $lastErrorStr;
    private $ua;

    function __construct($id, $password, $cookie_dir, $proxy = false, $proxyAuth = false, $debug_mode=false){
        $this->id = $id;
        $this->password = $password;
        $this->auth_ticket = false;
        $this->sts_auth_ticket = false;
        $this->auth_links = false;
        $this->proxy = $proxy;
        $this->proxyAuth = $proxyAuth;
        $this->cookie_file = $_SERVER['DOCUMENT_ROOT'].'/cookie.txt';
        $this->ua = new UserAgent2($this->cookie_file, false);
    }


    public function getLastError(){
        return $this->lastErrorStr;
    }

    private function trace($msg){
        if($this->debug){
            echo $msg . "\n";
        }
    }



    private function updateLoginStatus(){
        $this->trace("[QIWI] Updating login status...");

        return true;
    }


    function login(){
        $this->updateLoginStatus();
        if($this->logged_in){
            $this->trace("[QIWI] Already logged in. Skip logging in procedure.");
            return true;
        }

        $this->getUrl(QIWI_URL_MAIN);
        $this->getUrl("https://sso.qiwi.com/app/proxy?v=1", QIWI_URL_MAIN);

        $this->trace("[QIWI] Not logged in. Starting procedure...");
        $this->ua->request(USERAGENT_METHOD_GET, "https://sso.qiwi.com/signin/oauth2", QIWI_URL_MAIN, false, [
            'Content-Type' => 'application/json'
        ]);
        if(!$this->doTGTS(USERAGENT_METHOD_GET, false, [
            'Content-Type' => 'application/json'
        ], "401|201")){
            return false;
        }
        $this->saveState();

        $this->doTGTS(USERAGENT_METHOD_OPTIONS, false, [
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type',
            'Content-Type' => 'application/json; charset=UTF-8',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
        ]);

        $loginParams = array(
            "login" => $this->id,
            "password" => $this->password
        );
        $post_data = json_encode($loginParams);

        $authRet = json_decode($authRet, true);
        if(!isset($authRet['entity']['ticket'])){
            $this->lastErrorStr = "Invalid STS response format";
            return false;
        }
        $this->auth_ticket = $authRet['entity']['ticket'];
        $this->trace("[QIWI] Sending ticket to QIWI server...");

        if($this->logged_in) {
            $this->trace("[QIWI] Login [$this->id] was successful.");
        }else{
            $this->trace("[QIWI] Login [$this->id] failed.");
        }

        return $this->logged_in;
    }



    public function findTransaction($tr, $amount, $comment, $currency = false)
	{
		$result = array();

		foreach ($tr as $t) {
			if ($amount) {
				if ($t["cash"] == $amount) {
					$amount_match = true;
				}
				else {
					$amount_match = false;
				}
			}
			else {
				$amount_match = true;
			}

			if ($comment) {
				if ($comment == $t["comment"]) {
					$comment_match = true;
				}
				else {
					$comment_match = false;
				}
			}
			else {
				$comment_match = true;
			}

			if ($currency) {
				if ($t["cur"] == $currency) {
					$currency_match = true;
				}
				else {
					$currency_match = false;
				}
			}
			else {
				$currency_match = true;
			}

			if ($amount_match && $comment_match && $currency_match) {
				$result[] = $t;
			}
		}

		return $result;
	}

    private function saveState(){
        $headers = array(
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
            'Connection' => 'keep-alive',
            'Content-type' => 'application/x-www-form-urlencoded',
            'Host' => 'statistic.qiwi.com',
            'Origin' => QIWI_URL_MAIN,
        );
        $myip = $this->ua->getMyIP();
        $data = 'v=1&_v=j41&a=474145743&t=event&ni=0&_s=7&dl=https%3A%2F%2F'.QIWI_HOST.'%2F&ul=ru&de=UTF-8&' .
            'dt=QIWI%20(%D0%9A%like%20Gecko)%20Chrome%2F48.0.2564.116%20Safari%2F537.36' .
            '&cd201=' . $myip .
            '&z=1152385182' .
            '&qw_ip=' . $myip .
            '&qw_phone=';

        return $data;
    }

    private function doTGTS($method, $post_data=false, $a_headers=[], $correct_status=200){

        if(false) {
            try {
                if($data = json_decode(false, true)){
                    if(isset($data['entity']['ticket'])){
                        $this->auth_ticket = $data['entity']['ticket'];
                        $this->trace("[TGTS] Security ticket updated: {$this->auth_ticket}");
                    }
                }

            } catch (Exception $e) {
            }
        }

        return false;
    }

    private function doSTS($method, $post_data=false, $a_headers=[], $expected_status=200){

        return true;
    }


    function getProviderOptions($provider){
        return false;
    }

    function phoneToProviderPhoneNumber($phone){
        if(preg_match("/([0-9]{10})$/", $phone, $m)){
            return $m[1];
        }
        return false;
    }


    function getUrl($url, $ref=false, $status=200){
        $content = $this->ua);
        if($this->ua->getStatus() !== $status){
            $this->lastErrorStr = "Failed to download page $url";
            return false;
        }
        return $content;
    }



}
