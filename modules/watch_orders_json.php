<?
	$method = $request->getVar('method');

	function updateOrder() {
		GLOBAL $ACTIVES, $request, $suser, $events, $market;
		$response = array('result'=>0);
		if (($action = $request->getVar('action')) &&
			($uid = $suser['uid']) &&
			($pair = $request->getVar('pair')) &&
			($state = $request->getVar('state')) &&
			($volume = $request->getVar('volume'))) {

			$market_id = $market['id']; 
			$id = $request->getVar('id');
			$take_profit = $request->getVar('take_profit', 0);
			$stop_loss = $request->getVar('stop_loss', 0);

			$triggers_json = $request->getVar('triggers');

			if ($triggers = json_decode($triggers_json, true))
				$triggers_json = json_encode($triggers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

			if ($id) {
				if ($rec = DB::line("SELECT * FROM _watch_orders WHERE id={$id} AND uid={$uid}")) {
					$prev_action = json_decode($rec['action']);
					$state_update = "`state`='{$state}'";
					if ($prev_action['state'] != $state) {
						$state_update .= ", `state_time`=NOW(), `triggers_state`=''";
					}

					$query = "UPDATE _watch_orders SET {$state_update}, `pair`='{$pair}', `market_id`={$market_id}, `volume`='{$volume}', `action`='{$action}', `triggers`='{$triggers_json}', `take_profit`={$take_profit}, `stop_loss`={$stop_loss} WHERE id={$id} AND uid={$uid}";
				}
			}
			if (!isset($query)) 
				$query = "INSERT INTO _watch_orders (`uid`, `pair`, `market_id`, `volume`, `state_time`, `create_time`, `state`, `action`, `triggers`, ".
					"`take_profit`, `stop_loss`) ".
					"VALUES ({$uid}, '{$pair}', {$market_id}, '{$volume}', NOW(), NOW(), '{$state}', '{$action}', '{$triggers_json}', {$take_profit}, {$stop_loss})";

			$response['result'] = DB::query($query)?1:0;
			if (!$id) $id = DB::lastID();
			$response['id'] = $id;

			$events->send($uid, 'UPDATEORDER', ['id'=>$response['id'], 'pair'=>$pair]);

		}
		return $response;
	}

	function deleteOrder() {
		GLOBAL $ACTIVES, $request, $suser, $events, $market;
		$response = array('result'=>0);
		if ($id = $request->getVar('id')) {
			$response['result'] = DB::query("DELETE FROM _watch_orders WHERE id={$id}")?1:0;
			$events->send($suser['uid'], 'DELETEORDER', $id);
		}
		return $response;
	}

	function getList() {
		GLOBAL $ACTIVES, $request, $suser, $market;
		$result = array();
		if ($uid = $suser['uid']) {
			$where = "uid={$uid} AND market_id={$market['id']}";
			if ($pair = $request->getVar('pair')) {
				$where .= " AND `pair`='{$pair}'";
			}
			$result = DB::asArray("SELECT * FROM _watch_orders WHERE {$where} ORDER BY `pair`, `action`"); 
		}

		return $result;
	}

	$response = $method();

	$pair_id = getMPID($market['id'], $pairIDs['cur_in'], $pairIDs['cur_out']);
	$result = array('data'=>$response, 'minmax'=>DB::line("SELECT min, max FROM _minmax WHERE pair_id={$pair_id}"));


	header("Content-type:application/json");	
    echo json_encode($result);
?>