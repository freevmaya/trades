<?
?>
<script type="text/javascript">
/*            
            var cur;
            function fullPrices() { 
                if (cur_curName && cur_val) {
                    var idxs = cur_index[cur_curName];
                    $.each(idxs, function(i, pair) {
                        var input = $('#price_' + pair);
                        var pa = pair.split('_');
                        var cnv = 0;
                        if (pa[0] == cur_curName) cnv = 1/pairPrices[pair].buy;
                        else cnv = pairPrices[pair].sell;
                        
                        input.val(r(trade_recalcA(cur_val * cnv), 100000000));
                    });
                }
            }      
                  
            function fullPriceList() {
                var plist = $('#price_list');
                plist.empty();
                var idxs = cur_index[cur_curName];
                $.each(idxs, function(i, pair) {
                    var rec = $('<tr><td title="' + getPrice(pair, cur_curName) + '">' + pair + '</td><td><input type="text" value="" size="20" id="price_' + pair + '"></td></tr>');
                    plist.append(rec); 
                });
                fullPrices();
            }
        
            function onCurChange(cur_name) {
                //var pair = pairPrices[cur_index[cur_name]];
                cur_curName = cur_name;
                $('#in_currency').text(cur_curName);
                fullPriceList();
            }
        
            function onClick(e) {
                var t = $(e.target);
                if (cur != t) {
                    if (cur) cur.removeClass('cur');
                    cur = t;
                    cur.addClass('cur');
                    onCurChange(cur.text());
                }
            } 
            
            $.each(currency, function(i, curName) {
                var elem = $('<div class="currency">' + curName + '</div>');
                cur_list.append(elem);
                elem.click(onClick);
            });
*/             

    var trade = new (function() {
        var kommPercent = 0.2;
        var currency = [];
        var cur_index = {};
        var This = this;
        var buy_sel, sell_sel, cur_val;
        
        
        $.each(pairPrices, function(i, price) {
            var p = i.split('_');
            $.each(p, function(n, c) {
                if (currency.indexOf(c) == -1) currency.push(c);
                if (!cur_index[c]) cur_index[c] = [];
                cur_index[c].push(i);
            });
        });
        
        this.trade_recalc = function() {
            cur_val = parseFloat($('#price').val());
            cur_val = convert(cur_val);
            $('#sell').val(trade_recalcA(cur_val));
            //fullPrices();
        } 
        
        function convert(val) {
            var bVal = buy_sel.val();
            var sVal = sell_sel.val();
            if (bVal != sVal) {
                var i1 = bVal + '_' + sVal;
                var i2 = sVal + '_' + bVal;
                if (pairPrices[i1]) {
                    val = val * pairPrices[i1].buy;
                } else if (pairPrices[i2]) {
                    val = val * pairPrices[i2].sell;
                }
            }
            return val;
        }
        
        function getPrice(pair, currency) {
            var pa = pair.split('_');
            var cnv = 0;
            if (pa[0] == currency) 
                return pairPrices[pair].buy;
            return pairPrices[pair].sell;
        }
        
        function trade_recalcA(A) {
            var B = parseFloat($('#prof').val());
            var F = 1;
            var C = kommPercent * 2;
            
            var N = F - (F / 100 * C);
            var X = A + (A / 100 * B); 
            var Y = Math.pow(N / F, 2);
            return X / Y;
        }
        
        function onValue(val, type) {
            $('#price').val(val);
            trade.trade_recalc();
        }
        
        this.trade_recalcSell = function() {
            var A = parseFloat($('#sell').val());
            var C = kommPercent * 2;
            $('#price').val(A - (A / 100 * C));
        }
        
        $(window).ready(function() {
            pairListeners.push(function(pair, sell_min, buy_max) {
                $('#price').val(sell_min);
                This.trade_recalc();
            })
            
            buy_sel = $('#buy_currency');
            sell_sel = $('#sell_currency');
            $.each(currency, function(i, curr) {
                buy_sel.append($('<option value="' + curr + '">' + curr + '</option>'));
                sell_sel.append($('<option value="' + curr + '">' + curr + '</option>'));
            });  
            
            valueListener.push(onValue);
        });
    })();
</script>
<div class="calc">
    <table>
        <tr>
            <td>Прибыль %:</td><td><input type="text" value="2" size="5" onchange="trade.trade_recalc()" id="prof"></td>
        </tr>
        <tr>
            <td>Покупаем <select id="buy_currency" onchange="trade.trade_recalc()"></select>:</td><td><input type="text" value="" size="24" onchange="trade.trade_recalc()" id="price"></td>
        </tr>
        <tr><td>Продаем <select id="sell_currency" onchange="trade.trade_recalc()"></select>:</td><td><input type="text" value="" size="24" onchange="trade.trade_recalcSell()" id="sell"></td></tr>
        <tr>
            <td colspan="2">
                <table>
                    <tbody id="price_list">
                    </tbody>
                </table>                
            </td>            
        <tr>
        <tr class="separator"><td></td><td></td></tr>
    </table>
</div>