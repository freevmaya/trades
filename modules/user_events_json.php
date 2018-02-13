<?
	function getUserEvents() {
		GLOBAL $suser;
		$events = DB::asArray("SELECT DATE_FORMAT(`time`, '%d.%m %T') AS `time`, `type`, `data` FROM _user_events WHERE uid={$suser['uid']} AND `event`='CONSOLE'");

		return $events;
	} 

	$method = $request->getVar('method');
	$result = $method();

    header("Content-type:application/json");
    echo json_encode($result);	
?>