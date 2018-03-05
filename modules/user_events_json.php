<?
	function getUserEvents() {
		GLOBAL $suser;


		$events = DB::asArray("SELECT DATE_FORMAT(`time`, '%d.%m %T') AS `time`, `type`, `data`, `event` ".
							"FROM _user_events WHERE uid={$suser['uid']} AND `event` IN ".
							"('CONSOLE', 'ORDERSUCCESS', 'FAILORDER') ORDER BY `time` DESC");

		return $events;
	} 

	$method = $request->getVar('method');
	$result = $method();

    header("Content-type:application/json");
    echo json_encode($result);	
?>