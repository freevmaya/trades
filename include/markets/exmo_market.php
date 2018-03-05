<?
class exmo_market extends baseMarket {
	public function api_query($api_name, array $req = array()) 	{
	    $url = "http://api.exmo.me/v1/$api_name";
		$dec = null;
	    $mt = explode(' ', microtime());
	    $NONCE = $mt[1] . substr($mt[0], 2, 6);

	    $req['nonce'] = $NONCE;

	    // generate the POST data string
	    static $ch = null;
	    if (is_null($ch)) {
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
	    }

	    $post_data = http_build_query($req, '', '&');
	    $user = $this->getUser();
		if ($this->checkApiKey()) {
		    $sign = hash_hmac('sha512', $post_data, $user['secretApi']);
		    $headers = array(
		        'Sign: ' . $sign,
		        'Key: ' . $user['keyApi'],
		    );
		    //print_r($user);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	    // run the query
	    $res = curl_exec($ch);
	    if ($res === false) throw $this->log->logRecord('Could not get reply:'. curl_error($ch), 'error');
	   
	    $dec = json_decode($res, true);

	    if ($this->isErrorResponse($dec)) {
	    	if (isset($dec['error'])) {
	    		$this->log->logRecord('api_name: '.$api_name.', response error: '.$dec['error'], 'error');
	    	}
	    	if (strpos($res, '502 Bad Gateway') > -1) {
	    		if ($this->attempts < 10) {
	    			usleep(100000);
	    			$this->attempts++;
	    			$dec = $this->api_query($api_name, $req);
	    		}
	    	}
	    } else {
	    	usleep(100000);
	    	$this->attempts = 0;
	    }

	    return $dec;
	}

	protected function isErrorResponse($dec) {
		return (($dec == null) || (isset($dec['error']) && ($dec['error'])));
	}	


	public function getBalances() {
		if (($info = $this->api_query('user_info')) && ($info['balances']))
			return $info['balances'];
		else return null;
	}	
}
?>