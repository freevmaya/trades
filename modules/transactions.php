<?
    $pair = $request->getVar('pair', 'BTC_USD');
?>
<script type="text/javascript">
    var trans = new (function() {
        var tranChars, cpair, This = this;
        _chartsOnLoad.push(function() {
            tranChars = new google.visualization.AreaChart(document.getElementById('trans_div'));
            This.loadTrans('<?=$pair?>');
        });
        
        function minmaxCalc(list, p) {
            var minmax = [1000000000, 0]; 
            for (var i=1; i<list.length; i++) {
                if (minmax[0] > list[i][p]) minmax[0] = list[i][p];
                else if (minmax[1] < list[i][p]) minmax[1] = list[i][p];                        
            }
            return minmax;
        }
        
        function drawChart(response, min, max) {
            var data = google.visualization.arrayToDataTable(response);
            
            var options = {
                title: 'Динамика сделок',
                hAxis: {title: 'Время',  titleTextStyle: {color: '#333'}},
                vAxis: {minValue: min, maxValue: max},
                height: 150,
                legend: {position: 'top', maxLines: 4}
            };
            
            tranChars.draw(data, options);
            $('#trans_div').css('opacity', 1);
        }
        
        this.loadTrans = function(pair) {
            var is_sell;
            This.cpair = pair;
            
            var a_data = [];
            
            $('#trans_div').css('opacity', 0);
            $.getJSON('index.php?module=trades_json&pair=' + This.cpair, null, function(a_data) {
                var trades = a_data[pair];
                
                if (trades) {
                    trades.reverse();
                    var line = {min: trades[0].date, max: trades[trades.length - 1].date};
                    var step = Math.ceil((line.max - line.min) / 40); 
                    var itime = line.min;
                    var cur = {sell: 0, buy: 0};
                    var r_trades = [];
                    var min = 1000000000; max = 0;
                    
                    $.each(trades, function(i, item) {
                        var val = parseFloat(item.quantity);
                        if (val < min) min = val;
                        else if (val > max) max = val;
                        
/*                        
                        var is_sell = item.type == 'sell';
                        r_trades.push([item.date, is_sell?0:val, is_sell?val:0]);
*/                        
                        if (is_sell = item.type == 'sell') cur.sell += val;
                        else cur.buy += val;
                          
                        if (itime + step <= item.date) {
                            var d = (new Date(itime));
                            r_trades.push([itime, cur.buy, cur.sell]);
                            cur = {sell: 0, buy: 0};
                            itime += step;
                        }
                    });
                }

                var result = [['Время', 'Покупка', 'Продажа']].concat(r_trades);                
                drawChart(result, min, max);
            });   
        }

    
        $(window).ready(function() {
            pairListeners.push(function(pair, sell_min, buy_max) {
                trans.loadTrans(pair);
            })        
        });         
    })();
</script>
<div>
    <div class="result" id="trans_div" style="margin-top:20px; width: 400px"></div>
    <input type="button" onclick="trans.loadTrans(trans.cpair)" value="refresh">
    <input type="button" onclick="trans.loadTrans(trans.cpair, 1)" value="+"><span id="hours_label"></span><input type="button" onclick="trans.loadTrans(trans.cpair, -1)" value="-">
</div>