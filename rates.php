<?
    define('ROUNDNUM', 10000);
    define('REFRESHMIN', 15);

    $url = 'https://api.exmo.com/v1/';
    $cur = @($_GET['cur']);
    $refresh = isset($_GET['refresh']);
    
    function r($val, $roundNum=0) {
        $roundNum = $roundNum?$roundNum:ROUNDNUM;
        return round($val * $roundNum) / $roundNum;
    }
    
    function reversPair($pair) {
        $a = explode('_', $pair);
        return $a[1].'_'.$a[0];
    }
    
    function rgb($r, $g, $b) {
        $r = round($r/100 * 255);
        $g = round($g/100 * 255);
        $b = round($b/100 * 255);
        return "rgb($r, $g, $b)";
    }
    
    function getData($fileNameA, $queryA, $refresh=false) {
        $fileName = dirname(__FILE__).'/'.$fileNameA;
        $filetime = filectime($fileName);
        
        $REALDATA = $refresh || (((time() - $filetime) / 60) > REFRESHMIN);
        if ($REALDATA) $query = $queryA;
        else $query = $fileName;
        
        $str_cnt = file_get_contents($query);
        
        if ($REALDATA) file_put_contents($fileName, $str_cnt);
        return json_decode($str_cnt, true);
    }
     
//Получаем статистику
    $ticker = getData('ticker.json', $url.'ticker', $refresh);
    $pairs = array_keys($ticker);
    $curs = array();
    foreach ($pairs as $pair) {
        $pa = explode('_', $pair);
        for ($i=0;$i<2;$i++) if (!in_array($pa[$i], $curs)) $curs[] = $pa[$i]; 
    }
//Получаем сделки
    $content = getData('trades.json', $url.'trades/?pair='.implode(',', $pairs), $refresh);
    
//Получаем ordera
    $book = getData('order_book.json', $url.'order_book/?pair='.implode(',', $pairs).'&limit=1', $refresh);
    $data = array();
    
/*    foreach ($pairs as $pair) {
        if (!$cur || (strpos($pair, $cur) !== false)) {*/    
    foreach ($content as $pair=>$list) {
        if (!$cur || (strpos($pair, $cur) !== false)) {
            $sell_count = 0;
            $buy_count = 0;
            $sell_amount = 0;
            $buy_amount = 0;
            $pair_data = array('sell'=>array('summ'=>0, 'amount'=>0), 
                                'buy'=>array('summ'=>0, 'amount'=>0));
            foreach ($list as $order) {
                $type = $order['type'];
                $pair_data[$type]['summ'] += $order['price'] * $order['amount'];
                $pair_data[$type]['amount'] += $order['amount'];
                if ($type == 'sell') {
                    $sell_count++;
                    $sell_amount += $order['amount'];
                } else {
                    $buy_count++;
                    $buy_amount += $order['amount'];
                }
            }
            
            $t_pair = isset($ticker[$pair])?$pair:reversPair($pair);
            $tick = $ticker[$t_pair];
            $brec = $book[$t_pair];
            
            $data[$pair] = array(/*'sell'=>r($pair_data['sell']['summ']/$pair_data['sell']['amount']),// 'sell_amount'=>$pair_data['sell']['amount'], 
                                'buy'=>r($pair_data['buy']['summ']/$pair_data['buy']['amount']),// 'buy_amount'=>$pair_data['buy']['amount']
                                */
                                'SB'=>$sell_count.'/'.$buy_count,
                                'sellPercent'=>$sell_amount/($sell_amount + $buy_amount) * 100,
                                'sell_min'=>$tick['sell_price'],
                                'buy_max'=>$tick['buy_price']
                                );
        }
    }
    
    
    function dataCMD($i1, $i2) {
        return $i1['sellPercent'] - $i2['sellPercent']; 
    }
    uasort($data, 'dataCMD');
    
?>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" href='style.css' type='text/css' />
</head>
<body>
<div class="header">
    <div>
        <b style="color: blue">синий</b> - покупают, <b style="color: red">красный</b> - продают 
    </div>
</div>
<div class="exmo">
    <?//=implode(',', $pairs)?>
    <table>
        <?if ($cur) {?>  
        <tr>
            <td rowspan="5"><a href="?all_cur=1">R</a></td>
        </tr>
        <?}?>
        <tr>
            <?foreach ($data as $pair=>$item) {
                $pa = explode('_', $pair);
                $len = count($pair);
                $l = substr($pair, 0, 2);
                $c = substr($pair, 2, $len - 4);
                $r = substr($pair, $len - 4); 
            ?>
            <th><a href="?cur=<?=$pa[0]?>"><?=$l?></a><a onclick="parent.reset_pair('<?=$pair?>', <?=$item['sell_min']?>, <?=$item['buy_max']?>); return false;"><?=$c?></a><a href="?cur=<?=$pa[1]?>"><?=$r?></a></th>
            <?}?>
        </tr>
        <tr class="percent">
            <?foreach ($data as $item) {?>
            <td style="color: <?=rgb($item['sellPercent'], 0, 100 - $item['sellPercent'])?>"><?=$item['SB']?></td>
            <?}?>
        </tr> 
        <tr class="price">
            <?foreach ($data as $item) {?>
            <td><?=r($item['sell_min'])?></td>
            <?}?>
        </tr> 
        <tr class="price">
            <?foreach ($data as $item) {?>
            <td><?=r($item['buy_max'])?></td>
            <?}?>
        </tr>
    </table>
</div>
</body>
</html>