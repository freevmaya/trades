<script>
    var pairsObject;
    var pairsClass = function(pairsList) {
        var This = this;
        var pairs = external.getPairs();
        var cur_pair = $.cookie('CURRENTPAIR') || '<?=$pair?>';

        for (var i=0; i<pairs.length;i++) {
            var opt = $('<option value="' + pairs[i] + '">' + pairs[i] + '</option>');
            if (cur_pair == pairs[i]) opt.prop('selected', 'true');
            pairsList.append(opt);
        }            
        
        pairsList.selectmenu({
            width: 120,
            select: ()=>{
                This.setCurPair($('#pairsList').val());
            }
        });

        pairListeners.push(function(pair, sell_min, buy_max) {
            $.each(pairsList.children('option'), function(i, ctrl) {
                var item = $(ctrl);
                if (item.text() == pair) item.prop('selected', 'true');
                else if (item.prop('selected')) item.prop('selected', 'false')
            });                
        })

        this.setCurPair = function(a_cur_pair) {
            cur_pair = a_cur_pair;
            $.cookie('CURRENTPAIR', cur_pair);
            reset_pair(cur_pair);
        }

        setTimeout(function() {
            This.setCurPair(cur_pair?cur_pair:pairs[0]);
        }, 200);
    };
        
    $(window).ready(function() {
        pairsObject = new pairsClass($('#pairsList'));
    });
</script>
<div class="pairs">
    <select id="pairsList"></select>
</div>
