<?
class Events {
	protected $url;
	function __construct() {
		$this->url = "http://localhost/pub";
	}

	public function send($uid, $event, $params, $type='info') {
		return $this->_send($this->url.'?id=userevents'.$uid, json_encode(array('event'=>$event, 'data'=>$params, 'type'=>$type, 'time'=>date('d.m H:i:s')), JSON_UNESCAPED_UNICODE));
	}

	public function broadcast($event, $params) {
		return $this->_send($this->url.'?id=broadcast', json_encode(array('event'=>$event, 'data'=>$params, 'time'=>date('d.m H:i:s')), JSON_UNESCAPED_UNICODE));
	}

	public function pairdata($market, $pair, $params) {
		$ch = $market.'-'.$pair;
		return $this->_send($this->url.'?id='.$ch, json_encode($params), JSON_UNESCAPED_UNICODE);
	}

	public function _send($url, $data_str) {
		$ch = curl_init($url); 
        
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, str_replace('"', '\\"', $data_str));

        $result = curl_exec($ch);
        curl_close($ch);
        return $result; 
	}
}
?>