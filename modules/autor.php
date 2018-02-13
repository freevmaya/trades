<script>
    $(window).ready(function() {
        var autor = new (function() {
            var This = this;
            var api_keyCtrl = $('#api_key');
            var api_secretCtrl = $('#api_secret');
            var ordersPlanCtrl = $('#autor_orderPlan');
            var api_key, api_secret, balances, reserved;
            
            api_keyCtrl.on('change', function() {
                api_key = api_keyCtrl.val();
                login();
            });
            api_secretCtrl.on('change', function() {
                api_secret = api_secretCtrl.val();
                login();
            });
            
            onEvent('SELECTPAIR', function(pair) {
                resetOrderPlan(pair);
            });
            
            onEvent('RESPONSE_ORDERS', function(a_data) {
                var curr = a_data.pair.split('_')[0];
                if (balances && (balances[curr] > 0)) {
                    resetOrderPlan(a_data.pair);
                }
            });
            
            function getOrderPlan(pair) {
                var res = ordersPlanCtrl.find('.' + pair);
                return (res.length > 0)?res:null;
            }
            
            function sendOrders(pair, type, plan) {
            }
            
            function panikOrders(pair, type='sell') {
                var volume = parseFloat(getOrderPlan(pair).find('.volume').text());
                var field = (type=='sell')?'bid':'ask';
                function _sell(a_orders) {
                    order_list.setCache(pair, a_orders, ORDERSEXPIRE);
                    var plan = [];
                    var decsum = volume;
                    var i = 0;
                    while ((decsum > 0) && (i < a_orders[field].length)) {
                        var itm = a_orders[field][i];
                        console.log(itm);
                        var orderVolume = 0;
                        if (itm[1] < decsum) {
                            decsum -= itm[1];
                            orderVolume = itm[1];
                        } else {
                            orderVolume = decsum;
                            decsum = 0;
                        }
                        plan.push({
                            volume: orderVolume,
                            price: parseFloat(itm[0])
                        });
                        i++;
                    }       
                    
                    external.sendOrders(pair, plan, type, function(result, list) {
                        if (result) {
                            alert('send ok!');
                        }
                        console.log(list);
                        setTimeout(function() {
                            This.refreshUserInfo();
                        }, 1000);
                    });             
                }
                var orders = order_list.getCache(pair);
                if (!orders) {
                    external.getOrders(pair, _sell);
                } else _sell(orders);
            }
            
            function createOrderPlan(pair, curr, volume) {
                var op = $('<div class="order_plan ' + pair + '"><span class="pair">' + pair + '</span><span class="volume">' + volume + '</span><input type="button" value="panik"></div>');
                op.find('input').on('click', function() {
                    panikOrders(pair, 'sell');
                });
                ordersPlanCtrl.append(op);
            }
            
            function resetOrderPlan(pair) {
                var curr = pair.split('_')[0];
                var op = getOrderPlan(pair);
                var volume = balances[curr];
                if (volume == 0) console.log('Currency ' + curr + ' not found'); 
                if (!op) createOrderPlan(pair, curr, balances[curr]);
                else op.find('.volume').val(balances[curr]);
            }
            
            function userInfoSuccess(a_data) {
                balances = a_data.balances; 
                reserved = a_data.reserved;
                var pairs = external.getPairs();
                $.each(balances, function(cur, volume) {
                    var pair = cur + '_USD';
                    if (pairs.indexOf(pair) > -1) {
                        if (volume > 0) resetOrderPlan(pair, cur, volume);
                        else {
                            var op = getOrderPlan(pair);
                            if (op) op.remove();
                        } 
                    }
                });               
            }
            
            this.refreshUserInfo = function(onComplete) {
                external.userInfo(function(a_data) {
                    if (a_data.uid) userInfoSuccess(a_data);
                    if (onComplete) onComplete(a_data);
                });
            }
            
            function onTimeout() {
                if (order_list.isRefresh()) This.refreshUserInfo();
            }
            
            function login() {
                if (api_key && api_secret) {
                    external.config.key = api_key;
                    external.config.secret = api_secret;
                    This.refreshUserInfo(function(a_data) {
                        if (a_data.uid) {
                            $.cookie('ATUSER_KEY', api_key);
                            setInterval(onTimeout, 3000);
                            $('#autorize').remove();
                        } else if (a_data.error) alert(a_data.error);
                    });
                }
            }
            
            api_keyCtrl.val(api_key = $.cookie('ATUSER_KEY'));
        })();
    });
</script>
<div class="block" id="autorize">
    <h3>Autorize</h3>
    <input type="text" id="api_key"><br>
    <input type="text" id="api_secret">
</div>
<div class="block">
</div>
<div id="autor_orderPlan">
</div>