<?
define('DEMOSTARTBALANCE_USD', 2000);

class Account {
	private $user;
	private $market_rec;
	private $market_name;
	private $market;
	private $account_type;
	private $id;
	function __construct($user, $market_rec, $account_type='real') {
		$this->user 		= $this->getDBUser($user['uid'], $market_rec['id']);
		$this->market_rec 	= $market_rec;
		$this->account_type = $account_type;
		$this->market_name 	= $this->market_rec['name'];

    	include_once('include/markets/'.$this->market_name.'_market.php');		

    	$mclass = $this->market_name.'_market';
    	$this->market = new $mclass($this);
		$this->id = $this->checkAccount();
	}

	public function getUser() {
		return $this->user;
	}

	public function market_id() {
		return $this->market_rec['id'];
	}

	protected function getDBUser($uid, $market_id) {
		$query = "SELECT u.*, k.keyApi, k.secretApi, a.type as account_type FROM _users u ".
				"LEFT JOIN _apikey k ON u.uid = k.uid AND k.market_id={$market_id} ".
				"INNER JOIN _accout a ON a.id=u.account_id ".
				"WHERE u.uid={$uid}";
		return DB::line($query);
	}

	protected function checkAccount() {
		$id = 0;
		if ($this->user) {
			$query = "SELECT * FROM _accout WHERE uid={$this->user['uid']} AND market_id={$this->market_rec['id']} AND `type`='{$this->account_type}'";
			if ($rec = DB::line($query)) {
				$id = $rec['id'];
			} else {
				DB::query("REPLACE _accout (`uid`, `type`, `market_id`) VALUES ({$this->user['uid']}, '{$this->account_type}', {$this->market_rec['id']})");
				$id = DB::lastID();
			}

			DB::query("UPDATE _users SET account_id={$id} WHERE uid={$this->user['uid']}");
		}

		return $id;
	}

	public function resetBalance($cur_id, $start_value=0) {
		DB::query("REPLACE _balance (`uid`, `account_id`, `cur_id`) VALUES ({$this->user['uid']}, {$this->id}, {$cur_id})");
		DB::query("DELETE FROM _transaction WHERE account_id={$this->id} AND `cur_id`={$cur_id}");
		if ($start_value != 0) $this->transaction($cur_id, $start_value);
	}

	public function transaction($cur_id, $value) {
		$query = "INSERT INTO _transaction (`account_id`, `cur_id`, `value`) VALUES ({$this->id}, {$cur_id}, {$value})";
		if (DB::query($query)) {
			$query = "SELECT SUM(`value`) FROM _transaction WHERE ".
						"`account_id`={$this->id} AND `cur_id`={$cur_id}";
			$balance = DB::one($query);
			DB::query("UPDATE _balance SET `value` = {$balance} WHERE uid={$this->user['uid']} AND account_id={$this->id} AND cur_id={$cur_id}");
		} else return false;
	}

	public function getBalance($cur_id) {
		$query = "SELECT value FROM _balance WHERE uid={$this->user['uid']} AND account_id={$this->id} AND cur_id={$cur_id}";
		if ($line = DB::line($query)) return $line['value'];
		else return false;
	}

	public function balance($cur_id) {
		if ($value = $this->getBalance($cur_id)) {
			return $value;
		} else {
			$start_value = 0;
			if ($this->account_type == 'demo') {
				if (curSign($cur_id) == 'USD') $start_value = DEMOSTARTBALANCE_USD;
			}
			$this->resetBalance($cur_id, $start_value);
			return $start_value;
		}
	}

	public function setApiKey($keyApi, $secretApi) {
		$query = "REPLACE _apikey (`uid`, `market_id`, `keyApi`, `secretApi`) VALUES ".
		"({$this->user['uid']}, {$this->market_rec['id']}, '{$keyApi}', '{$secretApi}')";
		$r = DB::query($query);
		$this->user['keyApi'] = $keyApi;
		$this->user['secretApi'] = $secretApi;
		$this->refresBalances();
		return $r;
	}

	public function refresBalances() {
		if ($this->account_type == 'real') {
			if ($balances = $this->market->getBalances()) {
				$curids = [];
				foreach ($balances as $cur_name=>$item) {
					$curids[] = curID($cur_name);
				}

				$query = "SELECT b.*, c.sign FROM _balance b INNER JOIN _currency c ON b.cur_id=c.cur_id WHERE b.account_id={$this->id} AND b.cur_id IN (".implode(',', $curids).")";
				$dblist = DB::asArray($query);
				$clears = [];
				foreach ($dblist as $item) {
					$sign = $item['sign'];
					if ($balances[$sign] == $item['value']) unset($balances[$sign]);
					else $clears[] = $item['cur_id'];
				}

				if (count($clears) > 0) 
					DB::query("DELETE FROM _transaction WHERE account_id={$this->id} AND cur_id IN (".implode(',', $clears).")");
				
				foreach ($balances as $sign=>$value) {
					$cur_id = curID($sign);
					DB::query("REPLACE _balance (`uid`, `account_id`, `cur_id`, `value`) VALUES ({$this->user['uid']}, {$this->id}, {$cur_id}, {$value})");
					DB::query("INSERT INTO _transaction (`account_id`, `cur_id`, `value`) VALUES ({$this->id}, {$cur_id}, {$value})");
				}
			}
		}
	}

	public function logRecord($text) {
		console::log($text);
	}
}
?>