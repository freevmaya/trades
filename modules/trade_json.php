<?
	$item = 0;
    if (($start_time = @($_GET['start_time'])) && ($end_time = @($_GET['end_time']))) {
    	$pair = explode('_', $request->getVar('pair', 'BTC_USD'));
    	$cur_in = curID($pair[0]);
    	$cur_out = curID($pair[1]);

        $start_time = date('Y-m-d h:i:s', $start_time);
        $end_time = date('Y-m-d h:i:s', $end_time); 

        $query = "SELECT `id`, UNIX_TIMESTAMP(`time`) as `time`, bid_top, bid_quantity, ask_top, ask_quantity  FROM _orders_{$market['id']} WHERE cur_in={$cur_in} AND cur_out={$cur_out} AND time>'{$start_time}' AND time<'{$end_time}'";
//        echo $query;
        $list = DB::asArray($query);
        if ($list) {
        	foreach ($list as $key=>$item) {
                /*
	        	$query = "SELECT * FROM _ask WHERE parent_id={$item['id']}";
	        	$list[$key]['ask'] = DB::asArray($query);
	        	$query = "SELECT * FROM _bid WHERE parent_id={$item['id']}";
	        	$list[$key]['bid'] = DB::asArray($query);
                */


                $list[$key]['bid'] = array(array('price'=>$item['bid_top'], 'volume'=>$item['bid_quantity']));
                $list[$key]['ask'] = array(array('price'=>$item['ask_top'], 'volume'=>$item['ask_quantity']));

        	}
        }
    }

    echo json_encode($list);
?>