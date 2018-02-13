<script type="text/javascript">
    var trade = new (function() {
        var kommPercent = 0.2;
        var currency = [];
        var cur_index = {};
        var This = this;
        var cur_val;
        var sell_min;
        var sell_max;
        
        this.trade_recalc = function() {
            cur_val = parseFloat($('#price').val());
            $.cookie('TCURVAL', cur_val);
            sell_min.val(trade_recalcA(cur_val, 0));
            sell_max.val(trade_recalcA(cur_val, parseFloat($('#prof').val())));
            //fullPrices();
        } 
        
        function trade_recalcA(A, B) {
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
            var A = parseFloat(sell_min.val());
            var C = kommPercent * 2;
            $('#price').val(A - (A / 100 * C));
        }
        
        $(window).ready(function() {
            sell_min = $('#sell_min');
            sell_max = $('#sell_max');
            pairListeners.push(function(pair, sell_min, buy_max) {
                if (sell_min) {
                    $('#price').val(sell_min);
                    This.trade_recalc();
                }
            })
            
            valueListener.push(onValue);
            if (cur_val = $.cookie('TCURVAL')) {
                $('#price').val(cur_val);
                This.trade_recalc();
            }
        });
    })();
</script>
<div class="calc">
    <table>
        <tr>
            <td class="name">Прибыль %:</td><td><input type="text" value="2" size="3" onchange="trade.trade_recalc()" id="prof"></td>
        </tr>
        <tr>
            <td class="name">Покупаем:</td><td><input type="text" value="" size="18" onchange="trade.trade_recalc()" id="price"></td>
        </tr>
        <tr><td class="name">Продаем:</td><td><input type="text" value="" size="10" onchange="trade.trade_recalcSell()" id="sell_min" style="margin-right:2px"><input type="text" value="" size="10" id="sell_max"></td>
        </tr>
    </table>
</div>