<?
class baseMarket {
	protected $balances;
	protected $log;
	protected $account;

	function __construct($account) {
		$this->account = $account;
		$this->log = $account;
		$this->initialize();
	}

	protected function initialize() {
	}

	public function getUser() {
		return $this->account->getUser();
	}

	public function checkApiKey() {
		$user = $this->getUser();
		return ($user != null) && (@$user['secretApi']) && (@$user['keyApi']);
	}

	public function refresBalances() {
		
	}
}
?>