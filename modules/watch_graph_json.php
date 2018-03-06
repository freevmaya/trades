<?
    include_once('../include/utils.php');

    define("QUANTPERSEC", 30);

    function lastOrders($cur_in, $cur_out) {
        GLOBAL $request, $market;
        return DB::line("SELECT ask_top, bid_top, ask_glass, bid_glass, `time` FROM _orders_{$market['name']} ".
                    "WHERE cur_in={$cur_in} AND cur_out={$cur_out} ORDER BY id DESC LIMIT 0, 1" );
    }


    function asLine() {
        GLOBAL $request, $market;

        $quant      = $request->getVar('quant', 1);
        $all_amount = $request->getVar('all_amount', 10);
        $fields     = array('time', 'buy_price', 'sell_price');
        $pair       = explode('_', $request->getVar('pair', 'BTC_USD'));
        
        $cur_in     = curID($pair[0]);
        $cur_out    = curID($pair[1]);

        $quantsec   = $quant * QUANTPERSEC;
        $secs       = $quantsec * $all_amount;
        $ctime      = floor(strtotime('NOW') / QUANTPERSEC) * QUANTPERSEC;

        $timeStart  = date('Y-m-d H:i:s', $ctime - $secs);
        $timeEnd    = date('Y-m-d H:i:s', $ctime);

        $query = "SELECT COUNT(`time`) AS `count`, AVG(buy_price) AS buy_price, AVG(sell_price) AS sell_price, ".
            "AVG(buy_volumes) AS buy_volumes, AVG(sell_volumes) AS sell_volumes, ".
            "FLOOR(unix_timestamp(`time`) / {$quantsec}) AS `t` ".
            "FROM _trades_{$market['name']} ".
            "WHERE cur_in={$cur_in} AND cur_out={$cur_out} AND ".
            "`time` >= '{$timeStart}' AND `time` <= '{$timeEnd}' GROUP BY `t` ORDER BY `t`";
            
        //echo $query;
        $trade = DB::asArray($query, null, true);
        $result = array();
        $volumes = array();
        $gi = 0;

        foreach ($trade as $item) {
            $time   = $item['t'] * $quantsec;
            $result[]   = array($time, $item['buy_price'], $item['sell_price']);
            $volumes[]  = array($time, $item['buy_volumes'], $item['sell_volumes']);
        }

        return array('trade'=>$result, 'volumes'=>$volumes, 'last_orders'=>lastOrders($cur_in, $cur_out));
    }

    function asCandle() {
        GLOBAL $request, $market;
        $quant      = $request->getVar('quant', 1);
        $all_amount = $request->getVar('all_amount', 10);
        $fields     = array('time', 'buy_price', 'sell_price');
        $pair       = explode('_', $request->getVar('pair', 'BTC_USD'));
        
        $cur_in     = curID($pair[0]);
        $cur_out    = curID($pair[1]);

        $quantsec   = $quant * QUANTPERSEC;
        $secs       = $quantsec * $all_amount;
        $timeEnd    = floor(strtotime('NOW') / $quantsec) * $quantsec;
        $timeStart  = $timeEnd - $secs - $quantsec;

        $timeStart  = date('Y-m-d H:i:s', $timeStart);
        $timeEnd    = date('Y-m-d H:i:s', $timeEnd);

        $query = "SELECT COUNT(`time`) AS `count`, ".
            "MIN(buy_price) AS min_price, MAX(buy_price) AS max_price, buy_price AS close_price, ".
            "AVG(buy_volumes) AS buy_volumes, AVG(sell_volumes) AS sell_volumes, ".
            "FLOOR(unix_timestamp(`time`) / {$quantsec}) AS `t` ".
            "FROM _trades_{$market['name']} ".
            "WHERE cur_in={$cur_in} AND cur_out={$cur_out} AND ".
            "`time` >= '{$timeStart}' AND `time` <= '{$timeEnd}' GROUP BY `t` ORDER BY `t`";


        //echo $query;
        $trade = DB::asArray($query, null, true);

        $result = array();
        $volumes = array();
        $gi = 0;
        $prev_close = -1;

        foreach ($trade as $i=>$item) {
            $time   = $item['t'] * $quantsec;
            if ($i > 0) {
                $d = $item['close_price'] - $prev_close;
                $result[]   = array($time, min($item['min_price'], $prev_close), $prev_close, $item['close_price'], max($item['max_price'], $prev_close));
                $volumes[]  = array($time, $item['buy_volumes'], $item['sell_volumes']);
            }
            $prev_close = $item['close_price'];
        }

        $last_orders = DB::line("SELECT ask_top, bid_top, ask_glass, bid_glass FROM _orders_{$market['name']} ".
                    "WHERE cur_in={$cur_in} AND cur_out={$cur_out} LIMIT 0, 1" );

        return array('trade'=>$result, 'volumes'=>$volumes, 'last_orders'=>lastOrders($cur_in, $cur_out));
    }

    $method = $request->getVar('method');
    $result = $method();
    
    
    header("Content-type:application/json");
    echo json_encode($result);
?>