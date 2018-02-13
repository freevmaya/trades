<?
	
	$method = $request->getVar('method');

	function login() {
		GLOBAL $request, $_SESSION;
		$response = array('result'=>0);
		if (($name = $request->getVar('name')) &&
			($password = $request->getVar('password'))) {
			$user = DB::line("SELECT * FROM _users WHERE (`email` LIKE ('{$name}') OR `name` LIKE ('{$name}')) AND password='{$password}'");
			if ($user) {
				$user['token'] = md5(rand(0, 1000000));
				$_SESSION['USER'] = $user;
				$response['result'] = 1;
				$response['token'] = $user['token'];
			}
		}

		return $response;
	}

	function logout() {
		GLOBAL $_SESSION;
		unset($_SESSION['USER']);
		return array('result'=>1);
	}

	function apikey() {
		GLOBAL $request, $suser, $market;
		$response = array('result'=>0);
		$t = $suser['token'];
		$apikeyName = md5('apikey'.$t);
		$secretkeyName = md5('secretkey'.$t);

		if (($apikey = $request->getVar($apikeyName)) &&
			($apisecret = $request->getVar($secretkeyName))) {
			$r = DB::query("REPLACE _apikey (`uid`, `market_id`, `keyApi`, `secretApi`) VALUES ".
					"({$suser['uid']}, {$market['id']}, '$apikey', '$apisecret')");
			$response['result'] = $r?1:0;
		}

		return $response;
	}


	function register() {
		GLOBAL $request;
		$response = array('result'=>0);
		return $response;
	}

	function testStart() {
		GLOBAL $suser, $request, $market;
		$response = array('result'=>0);
		if (($start_time = $request->getVar('start_time', 0)) &&
			($end_time = $request->getVar('end_time', 0)) &&
			($pair = $request->getVar('pair', ''))) {


			$start_time = date(DATEFORMAT, $start_time);
			$end_time = date(DATEFORMAT, $end_time);

			$query = "REPLACE _test (`uid`, `market_id`, `pair`, `start_time`, `end_time`, `cur_time`) ".
					"VALUES ({$suser['uid']}, {$market['id']}, '{$pair}', '{$start_time}', '{$end_time}', '{$start_time}')";
			$response['result'] = DB::query($query)?1:0;
		}
		return $response;
	}

	function testAbort() {
		GLOBAL $suser, $request, $market;
		$response = array('result'=>0);
		if ($pair = $request->getVar('pair', '')) {
			$twhere = "`uid`={$suser['uid']} AND `market_id`={$market['id']} AND `pair`='{$pair}'";
			$query = "UPDATE _test SET `state`='abort' WHERE {$twhere}";
			$response['result'] = DB::query($query)?1:0;
		}
		return $response;
	}

	$response = $method();
	header("Content-type:application/json");	
	echo json_encode($response);
?>