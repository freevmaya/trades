<?
    $pair = $request->getVar('pair', 'BTC_USD');
?>
<script type="text/javascript">
    var graph = new (function() {
        var chart, orders, hours=<?=($hour = sesVar('hours', 3))?$hour:3?>, decTime=0, decTimeCtrl, This = this;
        var gdata;
        this.cpair = '';
        this.all_amount = false;
        
        function selectHandler(e) {
            var select = chart.getSelection();
            if (select.length > 0) {
                var pos = select[0];
                fireValue(gdata[pos.row][pos.column], 'type-' + pos.column);
            }
        } 
        
        _chartsOnLoad.push(function() {
            chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
            orders = new google.visualization.AreaChart(document.getElementById('orders_div'));
            This.loadPair('<?=$pair?>');
            
            google.visualization.events.addListener(chart, 'select', selectHandler);            
        });
        
        function drawChart(response) {
            gdata = response.data;
            var data = google.visualization.arrayToDataTable(gdata);
            
            var options = {
                title: 'Динамика',
                hAxis: {title: 'Время',  titleTextStyle: {color: '#333'}},
                vAxis: {minValue: response.minmax[0], maxValue: response.minmax[1]},
                height: 200,
                legend: {position: 'top', maxLines: 4}
            };
            
            chart.draw(data, options);
        } 
        
        function minmaxCalc(list, p) {
            var minmax = [1000000000, 0]; 
            for (var i=1; i<list.length; i++) {
                if (minmax[0] > list[i][p]) minmax[0] = list[i][p];
                else if (minmax[1] < list[i][p]) minmax[1] = list[i][p];                        
            }
            return minmax;
        }
        
        function decList(list, p, dec) {
            var minmax = [1000000000, 0]; 
            for (var i=1; i<list.length; i++) list[i][p] -= dec;
            return list;
        }
        
        function drawOrders(response) {
            var rd = response.data;
            rd[0] = ['Время', 'Купить', 'Продать'];
            var mm1 = minmaxCalc(rd, 1);
            var mm2 = minmaxCalc(rd, 2);
            
            var min = Math.min(mm1[0], mm2[0]);
            var max = Math.max(mm1[1], mm2[1]);
            decList(rd, 1, min);
            decList(rd, 2, min);
            
            var data = google.visualization.arrayToDataTable(rd);
            
            var lcur = This.cpair.split('_');
            
            var options = {
                title: 'Настроения по объему ордеров ' + lcur[0] + ' +' + min,
                hAxis: {title: 'Время',  titleTextStyle: {color: '#333'}},
                vAxis: {minValue: 0, maxValue: max - min},
                height: 100,
                legend: {position: 'top', maxLines: 4}
            };
            
            orders.draw(data, options);
        }
        
        this.loadPair = function(pair, addhour, a_decTime, a_all_amount) {
            if (typeof a_all_amount !== "undefined") this.all_amount = a_all_amount;
            
            var url = 'index.php?module=graph_json';
            if (addhour) hours = Math.max(hours + addhour, 1);
            if (a_decTime) decTime = Math.max(decTime + a_decTime, 0);

            decTimeCtrl.val(decTime);
            
            $('#hours_label').text(hours);
            if (pair) url += '&pair=' + pair + '&hours=' + hours + '&decTime=' + decTime + '&all_amount=' + (this.all_amount?1:0);
            this.cpair = pair;
            
            $.getJSON(url, null, function(a_data) {
                drawChart(a_data.trade);
                drawOrders(a_data.orders);
                fireEvent('TRADEHISTORY_RESPONSE', a_data.trade);
            });        
        }
        
        $(window).ready(function() {
            pairListeners.push(function(pair, sell_min, buy_max) {
                This.loadPair(pair);
            })   
            
            function onRefresh() {
                if ($('#graph_list_refresh').prop("checked")) graph.loadPair(graph.cpair);
                else setTimeout(onRefresh, 5000); 
            }
            setTimeout(onRefresh, 2000);      

            decTimeCtrl = $('#decTime');
            decTimeCtrl.on('change', function() {
                decTime = parseFloat(decTimeCtrl.val());
                graph.loadPair(graph.cpair, 0, 0);
            })   
        });
    })();
</script>
<div>
    <div class="result" id="chart_div" style="margin-top:20px; width: 100%"></div>
    <div class="result" id="orders_div" style="margin-top:20px; width: 100%"></div>
    <input type="checkbox" id="graph_list_refresh">
    <input type="button" onclick="graph.loadPair(graph.cpair)" value="refresh">
    <input type="button" onclick="graph.loadPair(graph.cpair, 1)" value="+"><span id="hours_label"></span><input type="button" onclick="graph.loadPair(graph.cpair, -1)" value="-">
    <input type="button" onclick="graph.loadPair(graph.cpair, 0, 1)" value="<"><input type="text" id="decTime" value="" size="3">
    <input type="button" onclick="graph.loadPair(graph.cpair, 0, -1)" value=">">
    <input type="button" onclick="graph.loadPair(graph.cpair, 0, 0, !graph.all_amount)" value="all amount">
</div>