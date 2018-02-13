<?
    define('DBPREF', '');
    define('ROUNDNUM', 10000);
    define('PERPAGE', 4);
    define('DEFAULTPAIR', 'BTC_USD');
    define('DATEFORMAT', 'd.m.Y H:i:s');
    
    include_once('/home/vmaya/games/include/engine2.php');
    include_once(INCLUDE_PATH.'/_edbu2.php');    
    include_once('include/utils.php');
    include_once('include/courses.php');
    
    
    $dbname = '_math';
    $request = new Request();
    $charset = 'utf8';
    $pair = $request->getVar('pair', DEFAULTPAIR);
    
    $page = $request->getVar('page', 0);
    $start = $page * PERPAGE; 
    
    $list = DB::asArray("SELECT * FROM _forecast WHERE date >= NOW() - INTERVAL 1 DAY ORDER BY date DESC LIMIT {$start},".PERPAGE);
?>

<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> 
<link rel="stylesheet" href='style.css' type='text/css' />
<style type="text/css">
    .list table {
        display: none;
    }
    .list table td {
        padding: 2px 5px;
        font-size: 13px;
    }
    
    tr.sell td {
        color: #AAA;
    }
    
    tr.buy td {
        background: #EEF;
    }
    
    .list-show {
        height: 10px;
        background: #AAA;
        cursor: pointer;
    }
    
    .date {
        color: #E88;
    }
    
    .pair {
        color: blue;
    }
</style>
<script type="text/javascript">
    function getTable(litem) {
        return $(litem).parent().find('table');
    }
    function onClick(e) {
        var t = getTable(e.target);
        var setd = (t.css('display')=='table')?'none':'table';
        t.css('display', setd);
    }
    $(window).ready(function() {
        $('.list-show').each(function(i, item) {
            $(item).click(onClick);
        });
    });
</script>
</head>
<body>
<?if ($page > 0) {?><a href="?page=<?=max(0, $page - 1)?>">back</a>-<?}?>
<a href="?page=<?=($page + 1)?>">next</a>
<table>
    <tr>
    </tr>
    <?
        foreach ($list as $item) {
    ?>
    <tr>
        <td class="date"><?=date(DATEFORMAT, strtotime($item['date']))?></td>
        <td class="pair"><?=$item['pair']?></td>
        <td><?=$item['result']?></td>
    </tr>
    <tr>
        <td colspan="3" class="list">
            <div class="list-show"></div>
            <table>
        <?
            foreach (json_decode($item['input'], true) as $trade) {
                $date = date(DATEFORMAT, $trade['date']);                
                echo "<tr class=\"{$trade['type']}\"><td>{$date}</td><td>{$trade['type']}</td><td>{$trade['quantity']}</td><td>{$trade['price']}</td></tr>\n";
            }                         
        ?>
            </table>
        </td>
    </tr>
    <?}?>
</table>
</body>
</html>