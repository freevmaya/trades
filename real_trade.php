<?
    define('DBPREF', '');
    define('ROUNDNUM', 1000000);
    define('DEFAULTPAIR', 'BTC_USD');
    session_start();
    
    include_once('/home/vmaya/games/include/engine2.php');
    include_once(INCLUDE_PATH.'/_edbu2.php');    
    include_once('include/utils.php');
    include_once('include/courses.php');
    include_once('/home/exmo.inc');
    
    $dbname = 'trade';
    $request = new Request();
    $charset = 'utf8';
    $curs = array();
    $pair = $request->getVar('pair', DEFAULTPAIR);
/*    
    $ticker = getCachedData('data/ticker.json', 'https://api.exmo.com/v1/ticker/');
    $prices = array();
    foreach ($ticker as $pairIdx=>$item) {
        $prices[$pairIdx] = array('buy'=>$item['buy_price'], 'sell'=>$item['sell_price']); 
    }
*/    
    
    if ($module = $request->getVar('module')) {
        include('modules/'.$module.'.php');
    } else {
    
?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1"> 

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> 
<script type="text/javascript" src="js/external.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>  
<script type="text/javascript" src="js/hmac-sha512.js"></script>

<link rel="stylesheet" href='style.css?1' type='text/css' />
<script type="text/javascript">
    var curs = <?=json_encode($curs)?>;
    var pairListeners = [];
    var valueListener = [];
    var eventListener = [];
    $.cookie.json = true;
    
    function time() {
        return (new Date()).getTime();
    }
    
    function fireValue(val, param) {
        $.each(valueListener, function(i, listener) {
            listener(val, param);
        });
    }
    
    function fireEvent(event, value) {
        $.each(eventListener, function(i, listener) {
            if (listener.event == event)
                listener.callback(value);
        });
    }
    
    function onEvent(event, callback) {
        eventListener.push({event: event, callback: callback});
    }
    
    function r(v, rn) {
        rn = rn?rn:<?=ROUNDNUM?>;
        return Math.round(v * rn) / rn;
    }
    
    function reset_pair(pair, sell_min, buy_max) {
        for (var i=0; i<pairListeners.length; i++) pairListeners[i](pair, sell_min, buy_max);
    }
    
    $(window).ready(function() {
        var g_cur = $('#g_cur');
        $.each(curs, function(i, val) {
            g_cur.append('<option>' + val + '</option>');
        });        
        $('#curpair').text('<?=$pair?>');
    });
    
    var _chartsOnLoad = [];    
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(function() {
        $.each(_chartsOnLoad, function(i, listener) {
            listener();
        });
    });          
    
    function cnvData(form) {
        //$('#psvd').val(md5($('#psvd').val()));
    }             
</script>    
</head>
<body>
<div class="trade_body">
    <div class="header">
        <?include_once('modules/sound.php');?>
    </div>
    <div>
        <?include_once('modules/trade.php');?>
    </div>
    <div>
        <?//include_once('modules/investing.php');?>
        <?//include_once('modules/autor.php');?>
        <?//include_once('modules/convert.php');?>
        <?include_once('modules/pingpong_real.php');?>
    </div>
    <!--<a href="?login=1" id="login_button">войти</a>-->
</div>
</body>
</html>
<?}?>