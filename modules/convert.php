<script>
    var cnv = new (function() {
        var curTiker, kom = 0.2;
        /*
        var field = {
            sell: 'sell_price',
            buy: 'buy_price'
        }
        */
        
        var field = {
            sell: 'sell_price',
            buy: 'buy_price'
        }
        
        function cnv(val, k) {
            var C = kom * 2;
            var nval = k * val;
            return nval - (nval / 100 * C);
        }
        
        function findToLine(source, dest, nval, equal=false) {
            var list = [];
            $.each(curTiker, (pair, data)=>{
                var desc = '', kp = 0;
                var pa = pair.split('_');
                if (!equal) {
                    if ((pa[0] == source) && (pa[1] != dest)) {
                        kp = data[field.sell];
                        ret = pa[1];
                    } else if ((pa[1] == source) && (pa[0] != dest)) {
                        kp = 1/data[field.buy];
                        ret = pa[0];
                    }
                } else {
                    if ((pa[0] == source) && (pa[1] == dest)) {
                        kp = data[field.sell];
                        ret = pa[1];
                    } else if ((pa[1] == source) && (pa[0] == dest)) {
                        kp = 1/data[field.buy];
                        ret = pa[0];
                    }
                }
                if (kp) {
//                    console.log(dest + ' => ' + source + ' = ' + nval + '; ' + source + ' => ' + ret + ' = ' + cnv(nval, kp));
                    list.push({
                        dest: ret,
                        value: cnv(nval, kp)
                    });
                }
            });
            return list;
        }
        
        function findTo(source, value) {
            $.each(curTiker, (pair, data)=>{
                var pa = pair.split('_');
                var kp = 0;
                var dest = '';
                if (pa[0] == source) {
                    kp = data[field.sell];
                    dest = pa[1];
                } else if (pa[1] == source) {
                    kp = 1/data[field.buy];
                    dest = pa[0];
                } 
                if (kp) {
                    var nval = cnv(value, kp);
                    var list = findToLine(dest, source, nval, true);
                    if (list.length)
                       console.log(list); 
                    /*
                    var result = [];
                    $.each(list, function(i, item) {
                        var next = findToLine(item.dest, source, item.value, true);
                        if (next.length > 0) {
                            $.each(next, function(n, nitm) {
                                var k = nitm.value / value;
                                if (k > 1.002) {
                                    result.push({
                                        price: kp, 
                                        dest: dest,
                                        dealer: item.dest,
                                        value: nitm.value 
                                    });
                                }
                            });
                        }
                    });
                    if (result.length > 0) 
                        console.log(result);
                    */
                }
            });
        }
        
        this.findCnv = function(source) {
            var currency = $('#source_pair').val();
            var value = parseFloat($('#cnv_value').val());
            if ((currency != '-') && !isNaN(value) && value) {
                external.getTiker((tiker)=>{
                    curTiker = tiker;
                    findTo(currency, value);
                });
            }
        }
    })();
    
    $(window).ready(function() {
        var list = $('#source_pair');
        external.getCurrency(function(currency) {
            list.append($('<option value="-">-</option>'));
            for (var i=0; i<currency.length;i++) {
                list.append($('<option value="' + currency[i] + '">' + currency[i] + '</option>'));
            }          
        });
    });    
</script>
<div class="block calc">
    <table>
        <tr>
            <td class="name">Исходник:</td>
            <td><select id="source_pair"></select></td>
        </tr>
        <tr>
            <td class="name">Сумма:</td>
            <td><input type="text" value="0" id="cnv_value" size="10"><input type="button" value="." onclick="cnv.findCnv()"></td>
        </tr>
    </table>
</div>