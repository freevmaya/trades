<?
function parseExmoTrades($data, $pair, $prev_trade=null) {
	$result = null;
	if (isset($data[$pair]) && (is_array($data[$pair]))) {
		$result = $prev_trade?$prev_trade:['buy_price'=>0, 'sell_price'=>0, 'buy_volumes'=>0, 'sell_volumes'=>0];
        $a_buy_price    = 0;
        $a_sell_price   = 0;
        $a_buy_volumes  = 0;
        $a_sell_volumes = 0;
        $sell_count     = 0;
        $buy_count      = 0;
        foreach ($data[$pair] as $i=>$item) {
            $t = $item['type']; 
            if ($t == 'sell') {
                if (($i == 0) || ($a_sell_price < $item['price'])) $a_sell_price = $item['price'];
                $a_sell_volumes += $item['quantity'];
                $sell_count++;
            } else {
                if (($i == 0) || ($a_buy_price < $item['price'])) $a_buy_price = $item['price'];
                $a_buy_volumes += $item['quantity'];
                $buy_count++;
            }
        }
        $result['buy_price']    = ($buy_count>0)?$a_buy_price:$result['buy_price'];
        $result['sell_price']   = ($sell_count>0)?$a_sell_price:$result['sell_price'];
        $result['buy_volumes']  = ($buy_count>0)?$a_buy_volumes:$result['buy_volumes'];
        $result['sell_volumes'] = ($sell_count>0)?$a_sell_volumes:$result['sell_volumes'];
    }
    return $result;
}
?>