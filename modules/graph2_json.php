<?
    session_start();    
    include_once('../include/utils.php');
    define('HOUR', 60 * 60);
    define('DATEFORMAT', 'Y-m-d H:i:s');

    $pair = explode('_', $request->getVar('pair', 'BTC_USD'));
    $cur_in = curID($pair[0]);
    $cur_out = curID($pair[1]);

    $utime = $request->getVar('startTime');
    $timeEnd = date(DATEFORMAT, $request->getVar('endTime'));
    $timeStart = date(DATEFORMAT, $utime);
    $quantum = $request->getVar('quantum', 1);

    $query = "SELECT UNIX_TIMESTAMP(time) as `time`, buy_price, sell_price FROM _trades WHERE cur_in={$cur_in} AND cur_out={$cur_out} AND ".
        "`time` >= '{$timeStart}' AND `time` <= '{$timeEnd}'"; 

    $trade = DB::asArray($query);

    $result = array();
    $gi = 0;

    foreach ($trade as $item) {
        $price = ($item['sell_price'] + $item['buy_price']) / 2;

        if ($gi % $quantum == 0) {
            if ($gi > 0) $result[] = array($time, $min, $open, $price, $max);
            $min        = $price;
            $max        = $price;
            $open       = $price;
            $time       = date('d.m H:i:s', $item['time']);
        }

        $min = min($price, $min);
        $max = max($price, $max);
        $gi += 1;
    }
    
    header("Content-type:application/json");
    echo json_encode($result);
?>