<?
    $pair = $request->getVar('pair', 'BTC_USD');
?>
<script type="text/javascript">
    var CRITERIA = -0.2, TRAGESTORAGELENGTH=100;
    var ORDERSEXPIRE = 1000;
    var order_list = new (function() {
        var orderChars, tradeChars, cpair, This = this, order_price, ordData, pairs=[], pairIndex=0, refreshTimer, orderPair, wait=0, order_list_refresh;
        var cache = {}, trade = {}, critList, libras = {};
        _chartsOnLoad.push(function() {
            orderChars = new google.visualization.AreaChart(document.getElementById('order_list_div'));
            tradeChars = new google.visualization.AreaChart(document.getElementById('storage_list_div'));
            This.cpair = $.cookie('CURRENTPAIR') || '<?=$pair?>';
            var cpairs = $.cookie('ORDER_PAIRS');
            if (cpairs) {pairs = cpairs;
                $.each(pairs, (i, pair)=>{
                    addPairControl(pair);
                });
            } else addPair(This.cpair);
            google.visualization.events.addListener(orderChars, 'select', selectHandler);
            //audio.play(); 
        });
        
        function addPair(pair) {
            if (pairs.indexOf(pair) == -1) {
                pairs.push(pair);
                addPairControl(pair);
                $.cookie('ORDER_PAIRS', pairs);
            }
        }
        
        function removePair(pair) {
            var index = pairs.indexOf(pair);
            if (index > -1) {
                pairs.splice(index, 1);

                var itm = critList.find('.' + pair);
                if (itm) itm.remove();
                $.cookie('ORDER_PAIRS', pairs);
            }
        }
        
        function selectHandler(e) {
            var select = orderChars.getSelection();
            if (select.length > 0) {
                var pos = select[0];
//                fireValue(ordData[pos.column][0], 'type-' + pos.column);
            }
        } 
        
        function minmaxCalc(list, p) {
            var minmax = [list[0][p], list[0][p]]; 
            for (var i=1; i<list.length; i++) {
                if (minmax[0] > list[i][p]) minmax[0] = list[i][p];
                else if (minmax[1] < list[i][p]) minmax[1] = list[i][p];                        
            }
            return minmax;
        }
        
        function drawChart(response, min, max) {
            var data = google.visualization.arrayToDataTable(response);
            
            var options = {
                title: 'Orders',
                hAxis: {title: 'Price',  titleTextStyle: {color: '#333'}},
                vAxis: {minValue: min, maxValue: max},
                height: 150,
                legend: {position: 'top', maxLines: 4}
            };
            
            orderChars.draw(data, options);
        }       
        
        function cnvf(arr) {
            $.each(arr, function(i, itm) {
                $.each(itm, function(n, num) {
                    itm[n] = parseFloat(num);
                });            
            });
            return arr;
        }
        
        function cnv(arr, offset, rev, accum=true) {
            var acum = {};  acuma = [];
            var min = parseFloat(arr[0][0]);
            var max = parseFloat(arr[arr.length - 1][0]);
            var step = (max - min) / 10;
            
            var idx = min;
            var sum = 0;            
            cnvf(arr);
            
            while (true) {
                var idx_max = idx + step;
                var i = 0;
                while (i < arr.length) {
                    var itm = arr[i];
                    var cur_price = itm[0];
                    if ((cur_price >= Math.min(idx, idx_max)) && 
                        ((cur_price < Math.max(idx, idx_max)))) {
                        var s_idx = idx.toString();
                        if (!acum[s_idx]) {
                            acum[s_idx] = 1;
                            acuma.push([s_idx, 0, 0]);
                            if (!accum) sum = 0;
                        }
                        
                        sum += itm[1];
                        acuma[acuma.length - 1][1 + offset] = sum;
                    }
                    i++;
                }
                idx = idx_max;
                if (rev) {
                    if (idx < max) break;
                } else if (idx > max) break;  
            }
            
            if (rev) acuma.reverse();
            return acuma;
        }
        
        this.isRefresh = function() {
            return order_list_refresh.prop("checked");
        }
        
        this.checkRefresh = function () {
            if (!This.isRefresh()) {
                order_list.loadorder_list(This.cpair);
                stopRefresh();
            } else startRefresh();
            
            $.cookie('ORDER_LIST_REFRESH', This.isRefresh());
        }
        

        function varavg(list, count_k, index) {
            var inc    = (count_k>0)?1:-1;
            var countl = list.length;
            var i      = (count_k>0)?0:(list.length-1);
            var count  = countl * count_k;
            if (countl > 1) { 
                var accum  = 0;
                var n      = Math.abs(count) + 1;
                var an     = 2 / n;
                var d      = an / (n - 1);
                while ((i >= 0) && (i < countl)) {
                    accum += parseFloat(list[i][index]) * an;
                    i += inc;
                    an -= d;
                }
            } else accum = parseFloat(list[0][index]); 
            return accum;
        }     
        
        function refreshStorage(pair) {
        /*
            var a = trade[This.cpair];
            var minTime = a[0][0];
            var maxTime = a[a.length - 1][0];
            var count = 200;
    */            
        
            var data = google.visualization.arrayToDataTable([['Time', 'Price']].concat(trade[pair]));
            
            var options = {
                title: 'Price AVG',
                hAxis: {title: 'Price',  titleTextStyle: {color: '#333'}},
                height: 150,
                legend: {position: 'top', maxLines: 4}
            };
            
            tradeChars.draw(data, options);
        }
        
        function priceChange(tradeList, count=0, startIndex=0) {
            function convolution(list) {
                var result = []; n=0;
                if (list.length > 1) {
                    for (var i=startIndex; i<list.length - 1; i++) {
                        n = Math.floor(i / 2);
                        result[n] = list[i + 1] / list[i];
                    }
                    
                    if (result.length == 1) result = result[0];
                    else result = convolution(result);
                }  else result = 1;
                return result;         
            }
            
            if (count == 0) count = tradeList.length - startIndex; 
            else if (count < 1) count = Math.ceil(tradeList.length * count) - startIndex;
            
            var tmp = [];
            for (var i=startIndex; i<startIndex+count;i++) tmp.push(tradeList[i][1])
            return convolution(tmp) - 1;            
        }
        
        function pushTrade(pair, time, price) {
            if (!trade[pair]) trade[pair] = [];
            else if (trade[pair].length > TRAGESTORAGELENGTH) {
                trade[pair].splice(0, 1);
            }
            trade[pair].push([time, price]);
        }
        
        function responseData(pair, ordData, storage) {
            var price_change = 0;
            var ask = cnv(ordData.ask, 1, false); 
            var bid = cnv(ordData.bid, 0, true);
            
            var libra = varavg(ask, 0.7, 2)/varavg(bid, -0.7, 1) - 1; 
            if (!libras[pair]) libras[pair] = {};
            libras[pair][time()] = libra;
            
            var result = [['Price', 'Ask', 'Bid']].concat(bid.concat(ask));
            var min = Math.min(ask[0][2], bid[0][1]); 
            var max = Math.max(ask[ask.length - 1][2], bid[bid.length - 1][1]);
            
            drawChart(result, min, max);
            var price = (ordData.ask[0][0] + ordData.bid[0][0]) / 2;
            order_price.text(price, 1000000);
            
            var lctrl = $('#libra');
            lctrl.css('color', (libra <= 0)?'red':(libra<=1?'blue':'green'));
            
            $('#order_price_buy').text(ordData.bid[0][0]);
            $('#order_price_sell').text(ordData.ask[0][0]);
            orderPair.text(pair);
            
            if (storage) {
                var dt =  (new Date());
                pushTrade(pair, dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds(), price);
                
                price_change = [priceChange(trade[pair]), 
                                priceChange(trade[pair], 0.5), 
                                priceChange(trade[pair], 0.25)];
            }
            
            var text = 'ask/bid: ' + r(libra, 1000);
            if (price_change) text +=  ', change: ' + r(price_change[0], 100) + ', ' +  + r(price_change[1], 100) + ', ' +  + r(price_change[2], 100); 
            lctrl.text(text);
            //$('title').text(pair + ' ' + libra);
            refreshStorage(pair);
            
            if (trade[pair].length > 2)
                checkCrit(pair, libra, price_change[0]);
            return libra;
        } 
        
        this.loadFromCache = function(pair) {
            if (cache[pair]) responseData(pair, cache[pair]);
        }
        
        this.getCache = function(pair) {
            var a_data = cache[pair];
            if (a_data && (a_data.expire >= time())) return a_data;
            else return null;
        }
        
        this.setCache = function(pair, a_data, expireMLS=1000) {
            a_data.expire = time() + expireMLS;
            cache[pair] = a_data;
        } 
        
        this.loadorder_list = function(pair) {
            if (wait == 0) {
                external.getOrders(pair, function(ordData) {
                    if (ordData) {
                        This.setCache(pair, ordData, ORDERSEXPIRE);
                        if (This.isRefresh() && (pairs.indexOf(pair) == -1)) return;
                        responseData(pair, ordData, true);
                    }                
                });
            } else fireEvent('WAITSTATE', wait); 
        }
        
        function addPairControl(pair) {
            itm = $('<a class="PAIR ' + pair + '" onclick="order_list.selecCurrentPair(\'' + pair + '\')">' + pair + '</a>');
            var dmlClick = false;
            itm.click(function() {
                setTimeout(function() {
                    if (!dmlClick) This.loadFromCache(pair);
                }, 500);
                
                fireEvent('SELECTPAIR', pair);
            });
            
            function removeItem() {
                dmlClick = true;
                removePair(pair);
            }
            
            itm.dblclick(removeItem);
            itm.on("touchstart",function(e){
                if(!dmlClick){
                  dmlClick=setTimeout(function(){
                      dmlClick=null;
                  }, 300);
                } else {
                  clearTimeout(dmlClick);
                  dmlClick=null;
                  removeItem();
                } 
                e.preventDefault()
            });
            critList.append(itm);        
        }
        
        function checkCrit(pair, libra, priceChange) {
            var itm = critList.find('.' + pair);
            var Class = (libra < CRITERIA)?'crit':((libra <= CRITERIA + 1)?'warn':'ok');
            orderPair.attr('class', Class);
            
            if (itm) {
                if (itm.hasClass(Class)) return;
                
                itm.removeClass('crit');
                itm.removeClass('warn'); 
                itm.removeClass('ok');
                
                var opacity = 0.4 + Math.max(0, Math.min(0.5 + priceChange * 100, 1)) * 0.6;
                itm.addClass(Class);
                itm.css('opacity', 0);
                var i=0;
                var timer = setInterval(function() {
                    i++;
                    itm.css('opacity', (i % 2) * opacity);
                    if (i >= 11) {
                        itm.css('opacity', opacity);
                        clearTimeout(timer);
                    }
                }, 500);
            }
            
            if (libra < 0) fireEvent('PAIRSTATE', 'crit');
            else if (libra <= 1) fireEvent('PAIRSTATE', 'warn');
            else fireEvent('PAIRSTATE', 'ok');
        }
        
        function clearPairs() {
            critList.find('.PAIR').remove();
            pairs = [];       
            addPair(This.cpair);
            pairIndex = 0;
        }
        
        function enterFrame() {
            order_list.loadorder_list(pairs[pairIndex]);
            pairIndex = (pairIndex + 1) % pairs.length;
        }
        
        function startRefresh() {
            refreshTimer = setInterval(enterFrame, 5000);
            enterFrame();   
        } 
        
        function stopRefresh() {
            if (refreshTimer) { 
                //clearPairs();
                clearTimeout(refreshTimer);
                refreshTimer = 0;
            }
        }
        
        this.selecCurrentPair = function(pair) {
            if (wait == 0) {
                this.setCurrentPair(pair);
                wait = setTimeout(function() {
                    clearTimeout(wait);
                    wait = 0;
                }, 5000);
            }
        }
        
        this.setCurrentPair = function(pair) {
            This.cpair = pair;
            $.cookie('CURRENTPAIR', pair);
            if (!This.isRefresh()) {
                clearPairs();
            } else {
                if (pairs.indexOf(pair) == -1) addPair(pair);
            }
            order_list.loadorder_list(order_list.cpair);
        }
        
        this.clearCrit = function() {
            clearPairs();
        }
        
        $(window).ready(function() {
            function startCharts() {
                if (orderChars) This.checkRefresh();
                else setTimeout(startCharts, 500);    
            }
        
            orderPair = $('#order_pair');
            order_price = $('#order_price');
            critList = $('#critList');
            order_list_refresh = $('#order_list_refresh');
            
            if ($.cookie('ORDER_LIST_REFRESH')) order_list_refresh.prop('checked', true);
            
            $('.order_price').click(function(e) {
                fireValue($(e.target).text());
            });
             
            pairListeners.push(function(pair, sell_min, buy_max) {
                This.setCurrentPair(pair);                
            })
            
            startCharts();
        });         
    })();
</script>
<div
    <div class="graph_list">
        <div>
            <div class="result" id="order_list_div"></div>
            <div class="result" id="storage_list_div"></div>
        </div>
    </div>
    <div id="critList">
        <a class="clear" onclick="order_list.clearCrit()">x</a>
    </div>
    <div class="block">
        <div class="libra" id="libra"></div>
        <div><h3 id="order_pair"></h3><a class="order_price" id="order_price_buy" href="#"></a><a class="order_price" id="order_price" href="#"></a><a class="order_price" id="order_price_sell" href="#"></a></div>
    </div>
    <div class="block">
        <input type="checkbox" id="order_list_refresh" onchange="order_list.checkRefresh()"><input type="button" onclick="order_list.loadorder_list(order_list.cpair)" value="refresh">
    </div>
</div>