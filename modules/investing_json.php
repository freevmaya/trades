<?
    setlocale(LC_CTYPE, array('ru_RU.utf8', 'ru_UA.utf8')); 
    setlocale(LC_ALL, array('ru_RU.utf8', 'ru_UA.utf8')); 

    //$pairs = array('BTC_USD'=>'btc-usdt');
    $pairs = array('BTC_USD'=>'btc-usdt', 'BCH_USD'=>'bch-usd?cid=1055161', 'ETH_USD'=>'eth-usd?cid=1031681',
                'DASH_USD'=>'dash-usd?cid=1031685', 'ZEC_USD'=>'zec-usd?cid=1031856', 'XMR_USD'=>'xmr-usd?cid=1054883', 'XRP_USD'=>'xrp-usd?cid=1054878', 
                'ZEC_USD'=>'zec-usd?cid=1031856', 'ETC_USD'=>'etc-usd');
    
    function curl_get($url, array $get = NULL, array $options = array()) {     
        
        $userAgent = 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0';
        $defaults = array( 
            CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get), 
            CURLOPT_HEADER => 0,
            CURLOPT_USERAGENT=> $userAgent,
            CURLOPT_RETURNTRANSFER => TRUE, 
            CURLOPT_TIMEOUT => 4 
        ); 
        
        $ch = curl_init(); 
        curl_setopt_array($ch, ($options + $defaults)); 
        if( ! $result = curl_exec($ch)) {
            trigger_error(curl_error($ch)); 
        } 
        curl_close($ch); 
        return $result; 
    }             
    
    $url = 'https://ru.investing.com/currencies/';
    $pattern = '/technicalSummaryTbl([\s\w\d"=А-Яа-я\/чсёьц\.,ртухыю\>\-<]+)<\/table>/';
    $result = array();
            
    foreach ($pairs as $pair=>$param) {
        //$context = file_get_contents($url.$param);
         
        $cur_url = $url.$param;
        $content = curl_get($cur_url);
                        
        $list = array();        
        preg_match_all($pattern, $content, $list);
        
        if (isset($list[1])) {
            preg_match_all('/Резюме<\/td>([\s\w\d"=А-Яа-я\/чсёьц\.,ртухыю\>\-<]+)<\/tr>/', $list[1][0], $resume);
            if (isset($resume[1])) {
                $result[$pair] = array('item'=>$resume[1][0], 'url'=>$cur_url);
            }                                
        }
    }                                               
    
    echo json_encode($result);        
?>