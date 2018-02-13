<style type="text/css">
    .tradeblock {
        position: relative;
        width: 200px;
        margin: 0px auto;
    }

    #ppgarea {
        width: 20px;
        height: 200px;
        border: 1px solid gray;
    }

    #pp_order_div {
        width: 400px;
        height: 200px;
        border: 1px solid gray;
    }
    
    #params {
        width: 400px;
        height: 100px;
    }

    #ppgarea div {
        position: absolute;
    }
    
    #point {
        width: 5px;
        height: 5px;
        margin: 0px 9px;
        background-color: black;
    }
    
    #point .value {
        font-size: 8px;
        margin: -5px 0px 0px 10px;
    }
    
    #frame {
        width: 8px;
        height: 50px;
        margin: 0px 6px;
        border: 1px solid blue;
        cursor: pointer;
    }

    #info {
        margin: 0px auto;
        width: 200px;
        height: 100px;
        border: 1px solid gray; 
    }
</style>

<script type="text/javascript" src="js/pingpong.js"></script>
<script type="text/javascript">
    
    var PPClass = function(cur_time, min, max, a_value) {
    
        $.extend(this, new PinPongClass(cur_time, a_value))
        
        this.updateDisplay = function() {
            var data = this.getData();

            var lh = (max - min);
            var ppgarea = $('#ppgarea');
            var point = $('#point');
            var frame = $('#frame');
            var ph = point.height();
            var valueLabel = point.find('.value');
            var h = ppgarea.height();

            frame.css('height', data.spring/lh * h + ph);
            
            valueLabel.text(r(data.bid, 1000));
            
            var mtp = h - Math.round(h * (data.bid - min) / lh) - ph / 2;
            var mtf = h - Math.round(h * (data.framePos - min) / lh) - frame.height() / 2;  
            
            point.css('margin-top', mtp);
            frame.css('margin-top', mtf);
            frame.css('background', this.isBound()?'#F88':(this.isPurchases()?'#AAF':'#FFF'));
        }
    }

    function info(str) {
        $('#info').text(str);
    }

    var graphic = new (function() {
        var trade_list, tradeChars, min;

        this.purchase = null;    

        _chartsOnLoad.push(function() {
            tradeChars = new google.visualization.AreaChart(document.getElementById('pp_order_div'));
//            google.visualization.events.addListener(orderChars, 'select', selectHandler);
        });

        var refreshCHCount = 0;
        this.toChart = function(a_data, pingPong) {
            function minmaxCalc(list, p) {
                var minmax = [list[0][p], list[0][p]];  
                for (var i=1; i<list.length; i++) {
                    if (minmax[0] > list[i][p]) minmax[0] = list[i][p];
                    else if (minmax[1] < list[i][p]) minmax[1] = list[i][p];                        
                }
                return minmax;
            }

            var data = pingPong.getData();
            var bid = parseFloat(a_data.bid[0].price);
            trade_list.push([a_data.time, bid, parseFloat(a_data.ask[0].price)]);

            var minmax = minmaxCalc(trade_list, 1);
            if (min == -1) min = minmax[0];
            trade_list[trade_list.length - 1].push(this.purchase?bid:min);

            if (trade_list.length > 300) trade_list.splice(0, 1);

            if (refreshCHCount % 40 == 0) {
                var data = google.visualization.arrayToDataTable([['Time', 'bid', 'ask', 'buy']].concat(trade_list));
                    
                var options = {
                    title: 'Price',
                    hAxis: {title: 'Price',  titleTextStyle: {color: '#333'}},
                    vAxis: {minValue: minmax[0], maxValue: minmax[1]},
                    backgroundColor: {fill: 'white'},
                    chartArea: {},
                    height: 200,
                    legend: {position: 'top', maxLines: 4}
                };
                
                tradeChars.draw(data, options);
            }
            refreshCHCount++;
        }    

        this.clear = function() {
            trade_list = [];
            min = -1;
            this.purchase = null;
        }

        this.clear();
    })();

    function fromHistoryInitialize() {
        var pingPong;
        var start_time = $.time('2017-12-27T09:00:00');
        var end_time = $.time('2017-12-28T12:00:00');
        var trade_url = 'http://pjof.ru/trade/index.php?module=trade_json&start_time=' + start_time + '&end_time=' + end_time;
        var pause = false;
        var timer = 0;
        var map = [];
        var cur_data;
        var FRAMESPEED = 5;
        var paramsCtrl = $('#params');

        function createPingpong(cur_time, value) {
            <?if ($pair == 'BTC_USD') {?>
                pingPong = new PPClass(cur_time, 12000, 18000, value);
                pingPong.setBuyer(new orderMarketCreator('buy'));
                pingPong.setSeller(new orderMarketCreator('sell'));
                
                var data = $.extend(pingPong.getData(), {"ask":value,"bid":value,"framePos":value,"freePos":value,"trend":0,"rig":10,"minBuyTrend":-0.5,"maxSellTrend":0,"trendSmoon":0.1,"maxPurchases":1,"buyMaxPrice":1.02,"sellMinPrice":0.98,"minProfitPercent":0.017,"totalProfit":0,"kinetic":0,"spring":40,"friction":1,"buyVolume":{"min":0.001,"max":0.04},"tradeEnabled":false,
                    "topPrice": 16000, "topPriceZone": 0.4, "topPriceSmoon": 0.0001,
                    "allowed":{"buy":true,"sell":true}});
                pingPong.setData(data);

                /*map = [
                    [16992, 30, 0.5],
                    [16652, 50, 0.5],
                    [16331, 50, 0.5],
                    [15666, 30, 0.5],
                    [15342, 30, 0.5]
                ];*/
            <?} else {?>
                pingPong = new PPClass(list[1][1]);
                pingPong.setBuyer(new orderMarketCreator('buy'));
                pingPong.setSeller(new orderMarketCreator('sell'));
                
                pingPong.attr('rig', 50);
                pingPong.attr('spring', 1);
                pingPong.attr('buyVolume', {min: 0.3, max: 1});
            <?}?>            

            pingPong.setMap(map);
            pingPong.attr('tradeEnabled', false);
            pingPong.on('PURCHASE_BUY', function(a_purchase) {
                graphic.purchase = a_purchase;
            });
            pingPong.on('PURCHASE_SELL', function(a_purchase) {
                graphic.purchase = null;
            });
            paramsCtrl.val(JSON.stringify(pingPong.getData()));
        }

        var frameCount = 0;
        var list;

        function stop() {
            //paramsCtrl.val(JSON.stringify(pingPong.getData()));
            clearTimeout(timer);
            info('TOTAL PROFIT: ' + pingPong.getattr('totalProfit'));
            timer = 0;
        }

        function resume() {
            if (timer == 0) timer = setInterval(onFrame, FRAMESPEED);
        }

        function onFrame() {
            if ((frameCount >= list.length)) {
//                console.log(JSON.stringify(pingPong.getData()));                
                stop();
                return;
            }
            cur_data = list[frameCount];

            cur_data.time = parseInt(cur_data.time);
            var bid = ordersFloat(cur_data.bid);
            if (!pingPong) createPingpong(cur_data.time, bid[0].price);

            if (frameCount == 10) pingPong.attr('tradeEnabled', true);          //Ждем 10 запросов, только после этого начинаем торговлю
            pingPong.curValues(cur_data.time, bid, ordersFloat(cur_data.ask));
            graphic.toChart(cur_data, pingPong);

            prev_id = parseInt(cur_data.id);
            frameCount++;
        }

        $.getJSON(trade_url, null, function(a_data) {
            list = a_data;
            resume();
        });

        $('#ppgarea').click(function() {
            if (pause = !pause) stop();
            else resume();
        });

        this.replay = function() {
            var a_data = JSON.parse(paramsCtrl.val());
            if (a_data) pingPong.setData(a_data);
            pingPong.attr('tradeEnabled', false);
            pingPong.reset();
            frameCount = 0;
            pause = false;
            graphic.clear();
            resume();
        }
    }


    var player;
    $(window).ready(function() {
        player = new fromHistoryInitialize();
        //rndInitialize();
    });                               
</script>

<div class="block">
    <table class="tradeblock">    
        <tr>
            <td>
                <textarea id="params"></textarea>
                <input type="button" value="replay" onclick="player.replay()">
            </td>
            <td>
                <div id="info">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div id="pp_order_div">
                </div>
            </td>
            <td>
                <div id="ppgarea">
                    <div id="frame"></div>
                    <div id="point"><span class="value"></span></div>
                </div>
            </td>
        </tr>
    </table>
</div>