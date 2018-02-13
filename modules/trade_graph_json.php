<?
    session_start();    
    include_once('../include/utils.php');
    
    define('HOUR', 60 * 60);
    function resetData($result, $fields) {
        $data = array();
        $i= 0;
        $minmax = array(1000000000000, 0);
        foreach ($result as $item) {
            $data[$i] = array();
            foreach ($fields as $field) {
                if ($field == 'time') {
                     
                    if (is_numeric($item[$field])) $val = date('d.m.Y H:i', $item[$field]);
                    else $val = $item[$field];
                    
                    $data[$i][] = $val;
                } else {
                    $val = floatval($item[$field]);
                    $data[$i][] = $val;
                    if ($val < $minmax[0]) $minmax[0] = $val;
                    else if ($val > $minmax[1]) $minmax[1] = $val;
                }
            }
            $i++;
        }
        return array('data'=>array_merge(array($fields), $data), 'minmax'=>$minmax);
    }

    $decTime = $request->getVar('decTime', 0);
        
    $all_amount = $request->getVar('all_amount');
    $fields = array('time', 'buy_price', 'sell_price');
    $order_fields = array('time', 'ask_quantity', 'bid_quantity');
    $pair = explode('_', $request->getVar('pair', 'BTC_USD'));
    
    $cur_in = curID($pair[0]);
    $cur_out = curID($pair[1]);
    $hours = sesVar('hours', 6);
    $timeEnd = strtotime($decTime?"NOW - ".(15 * $decTime)." minute":"NOW");
    $timeStart = $timeEnd - ($hours * HOUR);//strtotime("{$timeEnd} -{$hours} HOUR");
    
    $query = "SELECT UNIX_TIMESTAMP(`time`) AS `time`, buy_price, sell_price FROM _trades WHERE cur_in={$cur_in} AND cur_out={$cur_out} AND ".
        "`time` > '".date('Y-m-d H:i:s', $timeStart)."' AND `time` <= '".date('Y-m-d H:i:s', $timeEnd)."' ORDER BY `time`";
//    $query = "SELECT time, buy_price, sell_price FROM _exmo WHERE cur_in={$cur_in} AND cur_out={$cur_out} AND `time` > {$timeStart} AND `time` <= {$timeEnd}";
    $result = resetData(DB::asArray($query), $fields);
    
    $orders = array();
    /*
    $order_fields = array('time', 'bid_amount', 'ask_amount');
    $where = "`time` > '".date('Y-m-d H:i:s', $timeStart)."' AND `time` <= '".date('Y-m-d H:i:s', $timeEnd)."'";
     
    if ($all_amount) $query = "SELECT UNIX_TIMESTAMP(time) as `time`, SUM(bid_amount) AS bid_amount, SUM(ask_amount) AS ask_amount FROM _orders WHERE {$where} GROUP BY `time`";
    else $query = "SELECT UNIX_TIMESTAMP(time) as `time`, bid_amount, ask_amount FROM _orders WHERE cur_in={$cur_in} AND cur_out={$cur_out} AND {$where}"; 
    $orders = resetData(DB::asArray($query), $order_fields);
    */
    
    header("Content-type:application/json");
    echo json_encode(array('trade'=>$result, 'orders'=>$orders, 'hours'=>$hours));
?>