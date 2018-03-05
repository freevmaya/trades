<script type="text/javascript">
    var graph = new (function() {
        var chart_trades, chart_volumes, chart_test, This = this, ALLAMOUNT=48;
        var graph_area, test_layer, markers, minmax, method = $.cookie('GRAPHMETHOD') || 'asCandle', chart_type;
        var gdata, test_res={}, test_state=false, test_time, quant = 10;
        var worders;
        this.cpair = '';
        this.all_amount = false;
        var axisStyle = {fontSize: 9, color: 'white'};
        var rdata, vdata, times, area, selectprice, rowselected=false;
        
        function selectHandler(e) {
            var select = chart_trades.getSelection();
            if (select.length > 0) {
                var pos = select[0];
                fireEvent("GRAPHITEMSELECT", {data: rdata[pos.row], quant});
                rowselected = true;
                setTimeout(function() {rowselected=false}, 1000);
            }
        } 
        
        _chartsOnLoad.push(function() {
            chart_volumes = new google.visualization.AreaChart(document.getElementById('chart_volumes'));
            chart_test = new google.visualization.ScatterChart(document.getElementById('test_layer'));
            creteView();
        });

        function creteView() {
            if (chart_trades) chart_trades.clearChart();

            if ((chart_type != method) || !chart_trades) {
                if (method == 'asLine') chart_trades  = new google.visualization.LineChart(document.getElementById('chart_trades'));
                else chart_trades  = new google.visualization.CandlestickChart(document.getElementById('chart_trades'));
                chart_type = method;
                google.visualization.events.addListener(chart_trades, 'select', selectHandler);            
            }
        }

        var quant_i = [];
        onEvent('MARKETPAIRTRADES', (data)=>{
            This.refresh();
        });

        function tipsHtml(title, time, cap, price) {
            return '<div class="tooltip"><b>' + title + ': </b>' + 
                    timeFormat(time) + 
                    '<br><b>' + cap + '</b>: ' + price + '</div>';
        }

        function tipsAdd(arr, priceix=1, cap='') {
            var result = [];
            $.each(arr, (i, item)=>{
                var itm = item.slice(0, priceix + 1).concat([tipsHtml('<?=$locale['TIME']?>', item[0], cap, r(item[priceix]))]) 
                if (priceix + 1 < item.length) itm = itm.concat(item.slice(priceix + 1));
                result[i] = itm;
            });
            return result;
        }

        function ticks() {
            var count = Math.round(area.width / 80);
            var tcount = times[1] - times[0];
            var res = [];
            for (var i=0; i<count; i++) {
                var t = times[0] + Math.round(tcount * i/count);
                res.push({v:t, f: timeFormat(t)});

            }
            return res;
        }

        function cutFromTest(res) {
            var result = [];
            if (test_state)
                $.each(res, (i, itm)=>{
                    if (itm[0] <= test_time) result.push(itm);
                });
            else result = res.slice();
            return result;
        }
        
        this.asLine = (response)=>{
            creteView();

            minmax = utils.minmaxCalc(response, 1);
            markers.css(area);
            var variation = (minmax[1] - minmax[0]) * 0.2;
            minmax[2] = minmax[0] - variation; 
            minmax[3] = minmax[1] + variation; 
/*
            var adata = $.merge([['<?=$locale['TIME']?>', '<?=$locale['BUY']?>', '<?=$locale['SELL']?>']], response);
            var data = google.visualization.arrayToDataTable(adata);
*/
            data = new google.visualization.DataTable();

            data.addColumn('number', '<?=$locale['TIME']?>');
            data.addColumn('number', '<?=$locale['BUY']?>');
            data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
            data.addColumn('number', '<?=$locale['SELL']?>');
            data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
//            data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});

            response = cutFromTest(response); 
            var rdata = tipsAdd(tipsAdd(response, 1, '<?=$locale['BUY']?>'), 3, '<?=$locale['SELL']?>');
            data.addRows(rdata);
            
            var options = {
                hAxis: {titleTextStyle: axisStyle, textStyle: axisStyle, 
                        viewWindow: {min: times[0], max: times[1]},
                        ticks: ticks()
                },
                vAxis: {minValue: minmax[0], maxValue: minmax[1], titleTextStyle: axisStyle, textStyle: axisStyle,
                    viewWindow: {min: minmax[2], max: minmax[3]}
                },
                tooltip: {isHtml: true},
                height: area.height + 20,
                //legend: {position: 'top', maxLines: 8},
                curveType: 'function',
                chartArea: area
            };
            
            chart_trades.draw(data, options);
        }

        this.asCandle= (adata)=>{
            creteView();

            minmax = [Math.min.apply(Math, adata.map((v)=>{return v[2]})), Math.max.apply(Math, adata.map((v)=>{return v[3]}))];
            var variation = (minmax[1] - minmax[0]) * 0.2;
            minmax[2] = minmax[0] - variation; 
            minmax[3] = minmax[1] + variation; 
            markers.css(area);

            var rdata = adata.slice(); 
            $.each(rdata, (i, itm)=>{
                rdata[i][5] = tipsHtml('<?=$locale['TIME']?>', itm[0], '<?=$locale['PRICE']?>', r(itm[2]) + '-' + r(itm[3]));
            });
            //var data = google.visualization.arrayToDataTable(rdata, true);

            data = new google.visualization.DataTable();

            data.addColumn('number', '<?=$locale['TIME']?>');
            data.addColumn('number', '<?=$locale['PRICE']?>');
            data.addColumn('number', '<?=$locale['PRICE']?>');
            data.addColumn('number', '<?=$locale['PRICE']?>');
            data.addColumn('number', '<?=$locale['PRICE']?>');
            data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});

//            if (test_state) rdata = cutFromTest(rdata);
            if (rdata.length > 0) data.addRows(rdata);

            var options = {
                hAxis: {titleTextStyle: axisStyle, textStyle: axisStyle,
                    ticks: ticks()},
                vAxis: {minValue: minmax[0], maxValue: minmax[1], titleTextStyle: axisStyle, textStyle: axisStyle,
                    viewWindow: {min: minmax[2], max: minmax[3]}
                },
                height: area.height + 20,
                chartArea: area,
                legend: 'none',
                bar: {groupWidth: '80%'}, // Remove space between bars.
                tooltip: {isHtml: true},
                candlestick: {
                    fallingColor: {
                        strokeWidth: 1, 
                        stroke: '#000000',
                        fill: '#EE0000'
                    },
                    risingColor: {
                        strokeWidth: 1, 
                        stroke: '#000000',
                        fill: '#008800'
                    }
                }
            };

            chart_trades.draw(data, options);
        }

        this.drawVolumes = (volumes)=>{
//VOLUMES
            var vminmax = utils.minmaxCalc(volumes, 1);

            data = google.visualization.arrayToDataTable($.merge([['<?=$locale['TIME']?>', '<?=$locale['BUYVOLUME']?>', '<?=$locale['SELLVOLUME']?>']], volumes));
            
            var options = {
                hAxis: {textPosition: 'none'},
                vAxis: {minValue: vminmax[0], maxValue: vminmax[1], titleTextStyle: axisStyle, textStyle: axisStyle},
                height: 60,
                chartArea: {left: area.left, top: 0, width: area.width, height: 60},
                backgroundColor: 'rgba(0, 0, 0, 0)'
            };
            
            chart_volumes.draw(data, options);
        }

        this.drawTest = ()=>{
            if (test_res[this.cpair] && (test_res[this.cpair].length > 0)) {
/*
                var items = [['<?=$locale['TIME']?>', '<?=$locale['PRICE']?>']];
                $.each(test_res, (i, v)=>{items.push([v.time, parseFloat(v.ask_top)]);});
                var data = google.visualization.arrayToDataTable(items);
*/                

                var items = [];
                $.each(test_res[this.cpair], (i, v)=>{
                    
                    var p = parseFloat(v.price);
                    var tip = tipsHtml('<?=$locale['TIME']?>', v.time, '<?=$locale['PRICE']?>', p);
                    var itm;

                    switch (v.action.type) {
                        case 'buy': itm = [v.time, p, tip, 0, '', 0, ''];
                                    break;
                        case 'sell': itm = [v.time, 0, '', p, tip, 0, ''];
                                    break;
                        case 'message': itm = [v.time, 0, '', 0, '', p, tip];
                                    break;
                    }

                    items.push(itm);
                });

                data = new google.visualization.DataTable();

                data.addColumn('number', '<?=$locale['TIME']?>');
                data.addColumn('number', '<?=$locale['PRICE']?>');
                data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
                data.addColumn('number', '<?=$locale['PRICE']?>');
                data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
                data.addColumn('number', '<?=$locale['PRICE']?>');
                data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
                data.addRows(items);

                var options = {
                    hAxis: {
                        viewWindow: {min: times[0], max: times[1]},
                        textPosition: 'none',
                        gridlines: {color: 'transparent'}
                    },
                    vAxis: {
                        minValue: minmax[0], maxValue: minmax[1],
                        viewWindow: {min: minmax[2], max: minmax[3]},
                        textPosition: 'none'/*,
                        gridlines: {color: 'transparent'}*/
                    },
                    backgroundColor: { fill:'transparent' },
                    height: area.height + 20,
                    chartArea: area,
                    tooltip: {isHtml: true},
                    legend: 'none'
                };
                chart_test.draw(data, options);
            } else chart_test.clearChart();
        }

        this.refresh = ()=>{
            function chkLoadCharts() {
                if (chart_trades) This.loadPair(This.cpair);
                else setTimeout(chkLoadCharts, 200);               
            }

            chkLoadCharts();
        }
        
        this.loadPair = (pair, a_quant=null)=>{
            if (!a_quant) a_quant = quant;
            else quant = a_quant;

            var url = 'index.php?module=watch_graph_json';
            
            if (pair) url += '&pair=' + pair + '&quant=' + a_quant + '&all_amount=' + ALLAMOUNT;
            this.cpair = pair;

            var methods = {
                asCandle: function(a_data) {
                    if (a_data.trade.length > 0) {
                        quant_i = [];
                        var t = utils.arrToFloat(a_data.trade, [1, 2, 3, 4]);
                        times = [t[0][0], t[t.length - 1][0]];
                        rdata = t;//timeCnv(t);
                        vdata = utils.arrToFloat(a_data.volumes, [1, 2]);
                        This[method](rdata);
                        This.drawVolumes(vdata);
                        This.drawTest();
                        fireEvent('TRADEHISTORY_RESPONSE', a_data.trade);
                    }
                },
                asLine: function(a_data) {
                    if (a_data.trade.length > 0) {
                        quant_i = [];
                        var t = utils.arrToFloat(a_data.trade, [1, 2]);
                        times = [t[0][0], t[t.length - 1][0]];
                        rdata = t;//timeCnv(t);
                        vdata = utils.arrToFloat(a_data.volumes, [1, 2]);
                        This[method](rdata);
                        This.drawVolumes(vdata);
                        This.drawTest();
                        fireEvent('TRADEHISTORY_RESPONSE', a_data.trade);
                    }
                }
            }


            $.getJSON(url, {token: token, method: method}, methods[method]); 
        }

        this.setGraphMethod = function(a_method) {
            if (a_method != method) {
                method = a_method;
                $.cookie('GRAPHMETHOD', method);
                graph.loadPair(graph.cpair);
            }
        }

        this.toogleGraphMethod = function() {
            This.setGraphMethod((method=='asLine')?'asCandle':'asLine');
        }

        this.clearTest = function() {
            test_res[this.cpair] = [];
            chart_test.clearChart();
            test_layer.css('display', 'none');
            if (test_state) This.testAbort();
        }

        this.testAbort = function() {
            $.getJSON('index.php?module=user_json', {token: token, pair: this.cpair, method: 'testAbort'}, (res)=>{
                if (res.result == 1) This.testState(false);
                else ui.wentwrong();
            });
        }

        this.testState = function(a_state) {
            if (test_state != a_state) {
                test_state = a_state;
                if (test_state) {
                    if (test_layer.css('display') == 'none') {
                        var ct = $('#chart_trades');
                        test_layer.css({width: ct.width(), height: ct.height(), display: 'block'});
                    }
                }

                $('.graph').toggleClass('test', test_state);
                $('.graph .g-play').removeClass('red-border');
                redraw();
            }
        }

        this.toggleTest = function() {
            if (!test_state) {
                $.getJSON('index.php?module=user_json', {token: token, pair: this.cpair, method: 'testStart', start_time: times[0], end_time: times[1]}, (res)=>{
                    if (res.result != 1) ui.wentwrong();
                }); 
            } else this.testAbort();
        }

        this.test_START = function(data) {
            this.clearTest();
            this.testState(true);
        }

        this.test_PROCESS = function(data) {
            if (test_state) $('.graph .g-play').toggleClass('red-border');
            test_time = data.time;
            refreshTestLayer();
        }

        this.test_END = function(data) {
            this.testState(false);
        }

        function calcArea() {
            return {left: 40, top: 0, width: graph_area.width() - 45, height: 180};
        }

        function redraw() {
            This[method](rdata, vdata);
        }

        function refreshTestLayer() {
            redraw();
            This.drawTest();
        }

        function addTestMarker(order) {
            if (!test_res[order.pair]) test_res[order.pair] = [];
            test_res[order.pair].push(order);
            if (order.pair == This.cpair) refreshTestLayer();
            //test_layer.css('display', 'block');
        }

        function onUserEvents(e) {
            if (e.event == 'ORDERSUCCESS') {
                if (e.data.state == 'test') addTestMarker(e.data);
            } else if (e.event == 'TESTEVENT') {
                This['test_' + e.data.state](e.data);
            }
        }

        function onOrderResponse(list) {
            var is_test = false;
            $.each(list, (i, o)=>{if (o.state == 'test') is_test = true;})
            $('.graph').toggleClass('is-test', is_test);
            worders = list.slice();
            if (!is_test) {
                if (test_state) This.testAbort();
                else {
                    This.clearTest();
                    This.testState(false);
                }
            }
        }

        function resetSize() {
            area = calcArea();
        }

        function onResize() {
            resetSize();
            redraw();
        }

        $(window).on('resize', onResize);
        $(window).ready(()=>{
            pairListeners.push((pair, sell_min, buy_max)=>{
                This.cpair = pair;
                test_layer.css('display', (test_res[This.cpair] && (test_res[This.cpair].length > 0))?'block':'none');
                if (test_state) This.testAbort();

                This.refresh();
            })
            
            decTimeCtrl = $('#decTime');
            decTimeCtrl.on('change', ()=>{
                decTime = parseFloat(decTimeCtrl.val());
                This.refresh();
            });

            graph_area = $('#graph_area');
            test_layer = $('#test_layer');
            resetSize();
            markers = graph_area.find('.markers');
            var vline = markers.find('.vline');
            var hline = markers.find('.hline');
            var tline = markers.find('.tline');
            var buyTitle = vline.find('.buy');
            var sellTitle = vline.find('.sell');

            graph_area.click((e)=>{
                setTimeout(function() {if(!rowselected) {fireEvent("PRICEACCEPT", {price: selectprice})}}, 100);
            });

            graph_area.mouseover(()=>{markers.css('opacity', 1);});
            graph_area.mouseout(()=>{markers.css('opacity', 0);});

            graph_area.on('mousemove', (e)=>{
                if (minmax && (graph_area[0] == e.currentTarget)) {
                    var mpos = new Vector(e.pageX - markers.offset().left, e.pageY - markers.offset().top);
                    var d = minmax[3] - minmax[2], y = mpos.y + 26, ah = area.height, m0=minmax[0];

                    selectprice = m0 + (1 - y/ah) * d;
                    var sellprice = selectprice + selectprice * external.commission * 2;
                    var spy = (1 - (sellprice - m0)/d) * ah;
                    var h = y - spy;

                    var td = times[1] - times[0];
                    var loffset = 0;
                    var ftime = timeFormat(Math.round(times[0] + mpos.x/area.width * td));
                    var dv = 1;//area.width / ALLAMOUNT;

                    vline.css({'margin-top': mpos.y - h, height: h});
                    hline.css('margin-left', Math.round(mpos.x / dv) * dv);
                    tline.css({'margin-left': mpos.x, 'margin-top': area.height});

                    var m = minmax[1];
                    var o = m>1000?1:(m>1?1000:1000000);
                    buyTitle.text(locale.VLINE.buy + ' ' + r(selectprice, o));
                    sellTitle.text(locale.VLINE.sell + ' ' + r(sellprice, o));
                    tline.find('span').text(ftime);

                    buyTitle.css({'margin-top': h, 'margin-left': mpos.x - buyTitle.width() - 5});
                    sellTitle.css({'margin-top': -11, 'margin-left': mpos.x + 5});
                }
            });

            onEvent('EVENTRESPONSE', onUserEvents);
            onEvent('ORDERSRESPONSE', onOrderResponse);
        });
    })();
</script>
<div class="graph">
    <div class="panel">
        <span class="ui-button ui-button-icon-only g-type" onclick="graph.toogleGraphMethod()"><span class="ui-icon ui-icon-gear"></span></span>
        <span class="ui-button ui-button-icon-only g-play test-control" onclick="graph.toggleTest()"><span class="ui-icon ui-icon-play"></span><span class="ui-icon ui-icon-stop"></span></span>
        <span class="ui-button ui-button-icon-only g-clear test-control" onclick="graph.clearTest()"><span class="ui-icon ui-icon-cancel"></span></span>
    </div>
    <div id="graph_area">
        <div class="markers">
            <div class="line vline"><span class="sell"></span><span class="buy"></span></div>
            <div class="line hline"></div>
            <div class="line tline"><span></span></div>
        </div>
        <div class="result chart_area" id="test_layer"></div>
        <div class="result chart_area" id="chart_trades"></div>
        <div class="result chart_area" id="chart_volumes"></div>
    </div>
    <div class="buttons">
        <input type="button" onclick="graph.loadPair(graph.cpair, 10)" value="<?=$locale['TIMEINTERVAL1']?>">
        <input type="button" onclick="graph.loadPair(graph.cpair, 30)" value="<?=$locale['TIMEINTERVAL3']?>">
        <input type="button" onclick="graph.loadPair(graph.cpair, 60)" value="<?=$locale['TIMEINTERVAL4']?>">
        <input type="button" onclick="graph.loadPair(graph.cpair, 60 * 7)" value="<?=$locale['TIMEINTERVAL5']?>">
    </div>
</div>