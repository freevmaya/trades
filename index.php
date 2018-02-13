<?
    define('DBPREF', '');
    define('DEFAULTPAIR', 'BTC_USD');
    define('CHECKEVENTPERIOD', 10000);
    define('DEFAULTMARKET', 'exmo');
    define('DATEFORMAT', 'Y-m-d H:i:s');
    session_start();
    
    $lang = 'ru';    

    include_once('include/engine.php');
    include_once('include/utils.php');
    include_once('include/courses.php');
    include_once('include/'.$lang.'/locale.php');
    include_once('include/events.php');
    include_once('/home/exmo.inc');

    $dbname = 'trade';
    $request = new Request();
    $charset = 'utf8';
    $curs = array();
    $pair = $request->getVar('pair', DEFAULTPAIR);
    $suser = isset($_SESSION['USER'])?$_SESSION['USER']:null;

    $theme = $request->getSVar('theme', 'dark');
    if (!($market = DB::line("SELECT * FROM _markets WHERE name='".$request->getSVar('market', DEFAULTMARKET)."'")))
        $market = DB::line("SELECT * FROM _markets WHERE name='".DEFAULTMARKET."'");

    $themePath = "themes/{$theme}/";
    
    if ($module = $request->getVar('module')) {
        if ($module == 'user_json')
            include('modules/'.$module.'.php');
        else {
            if ($suser['token'] == $request->getVar('token')) {
                $events = new Events();
                include('modules/'.$module.'.php');
            } else echo '{"error":"Inactive session"}';
        }
    } else {
?>
<html>
<head>
<title>Exchange client</title>
<meta name="viewport" content="width=device-width, initial-scale=1"> 

<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>-->
<script src="//code.jquery.com/jquery-1.12.4.js"></script>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> 
<script type="text/javascript" src="js/external_<?=$market['name']?>.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>  
<script type="text/javascript" src="js/hmac-sha512.js"></script> 
<script type="text/javascript" src="js/jquery.md5.js"></script> 
<script type="text/javascript" src="js/vector.js"></script>
<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript" src="js/app.js"></script>
<script type="text/javascript" src="js/pushstream.js"></script>
<script type="text/javascript" src="js/tparams.js"></script>
<script type="text/javascript" src="js/triggersCtrl.js"></script>
<script type="text/javascript" src="js/order.js"></script>
<script type="text/javascript" src="js/df.min.js"></script>
<script type="text/javascript" src="js/<?=$lang?>/locale.js"></script>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href='<?=$themePath?>style.css?1' type='text/css' />
<link rel="stylesheet" href="<?=$themePath?>jquery-ui.min.css">
<link rel="stylesheet" href="<?=$themePath?>jquery-ui.theme.min.css">

<script type="text/javascript">
    var login_button;
    var curs = <?=json_encode($curs)?>;
    var ui;
    <?if ($suser) {?>
    var token = '<?=$suser['token']?>';
    <?}?>

    $.cookie.json = true;
    
    $(window).ready(function() {
        var pair;
        pairListeners.push((a_pair)=>{pair=a_pair});
        var g_cur = $('#g_cur');
        $.each(curs, function(i, val) {
            g_cur.append('<option>' + val + '</option>');
        });        
        $('#curpair').text('<?=$pair?>');

        onEvent('MARKETPAIRTRADES', (data)=>{
            if (pair) document.title = r(data.sell_price) + '\u25BA' + pair;
        });
    });
    
    var _chartsOnLoad = [];    
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(function() {
        $.each(_chartsOnLoad, function(i, listener) {
            listener();
        });
    });          
/*    
    function cnvData(form) {
        //$('#psvd').val(md5($('#psvd').val()));
    }
*/    

    ui = new (function() {
        var dialog, onComplete, This = this;

        function close() {dialog.dialog( "close" )}

        $(window).ready(function() {
            dialog = $( "#dialog" );
            dialog.find('.ui-close').click(close);
            dialog.find('.ui-ok').click(()=>{
                if (onComplete) {
                    if (onComplete()) close();
                } else close();
            });
            $("input[type=submit], input[type=button], .widget a, button" ).button();
            //$(document).tooltip();
        });

        $.jsonPOST = function(url, params, onSuccess) {

            params = $.extend(params, {
                url: url,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function(data) {
                    if ($.type(data) == 'string') data = $.parseJSON(data);
                    if (data.error) ui.error(data.error);
                    else onSuccess(data);
                }
            })

            $.ajax(params);            
        }

        this.error = (msg)=>{
            This.dialog('<?=$locale['ERROR']?>', '<p>' + msg + '</p>');
        }

        this.wentwrong = ()=> {
            This.error(locale.WENTWRONG);
        }

        this.message = (msg)=>{
            This.dialog('<?=$locale['MESSAGE']?>', '<p>' + msg + '</p>');
        }

        this.dialog = (title, content, a_onComplete, a_onCancel=null, buttons=null, modal=true)=>{
            onComplete = a_onComplete;
            dialog.attr('title', title);
            var ct = dialog.find('.content');
            ct.empty();
            if ($.type(content) == 'string')
                ct.html(content);
            else ct.append(content);

            var bts =  $.extend({
                "<?=$locale['OK']?>": function() {
                    if (a_onComplete != null) {
                        if (a_onComplete()) $(this).dialog( "close" );
                    } else $(this).dialog( "close" );
                }
            }, buttons);

            if (a_onCancel != null) {
                bts["<?=$locale['CANCEL']?>"] = function() {
                    a_onCancel();
                    $(this).dialog( "close" );
                }
            }

            dialog.dialog({
                modal: modal,
                buttons: bts
            });
            return dialog;
        }
    })();

<?if ($suser) {?>
    new eventsSupport(<?=$suser['uid']?>, '<?=$market['name']?>', '<?=$pair?>');
<?}?>        
</script>    
</head>
<body>
<div class="content">
<?
if ($suser) {
    $page = $request->getVar('page', 'orders');
    include('pages/'.$page.'.php');
} else include('pages/login.php');
?>
</div>
<div class="templates">
    <div id="dialog">
        <div class="content">
        </div>
    </div>
</div>
</body>
</html>
<?}?>