<style type="text/css">
    #ppgarea {
        margin: 0px auto;
        width: 100px;
        height: 300px;
        border: 1px solid gray;
    }
    
    #ppgarea div {
        position: absolute;
    }
    
    #point {
        width: 5px;
        height: 5px;
        margin: 0px 48px;
        background-color: black;
    }
    
    #point .value {
        font-size: 11px;
        margin: -5px 0px 0px 10px;
    }
    
    #frame {
        width: 8px;
        height: 50px;
        margin: 0px 46px;
        border: 1px solid blue;
        cursor: pointer;
    }
</style>

<script type="text/javascript" src="js/pingpong.js"></script>
<script type="text/javascript">
    
    var PPClass = function(min, max, a_value) {
    
        $.extend(this, new PinPongClass(a_value));
        
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

    function Initialize() {
        var pingPong;
        
        function createPP(value) {
            var map = [];

            /*map = [
                    [16992, 30, 0.5],
                    [16652, 50, 0.5],
                    [16331, 50, 0.5],
                    [15666, 30, 0.5],
                    [15342, 30, 0.5]
                ];*/

            pingPong = new PPClass(12000, 18000, value);
            pingPong.setBuyer(new orderCreator('buy'));
            pingPong.setSeller(new orderCreator('sell'));
            
            pingPong.attr('rig', 20);
            pingPong.attr('spring', 40);
            pingPong.attr('buyVolume', {min: 0.005, max: 0.03});
            
            pingPong.setMap(map);
            pingPong.attr('tradeEnabled', false);
        }
        
        var loadState = false;

        function cnvOrders(orders) {
            var result = [];
            $.each(orders, function(i, order) {
                result.push({
                    price: parseFloat(order[0]),
                    volume: parseFloat(order[1]),
                    sum: parseFloat(order[2])
                })
            })
            return result;
        }

        frameIndex = 0;
        function onFrame() {
            if (!loadState) {
                external.getOrders('<?=$pair?>', function(a_data) {
                    if (!pingPong) createPP(parseFloat(a_data.bid[0][0]));

                    if (frameIndex == 20) pingPong.attr('tradeEnabled', true); // 20 циклов в холостую, для настройки
                    pingPong.curValues(cnvOrders(a_data.bid), cnvOrders(a_data.ask));

                    loadState = false;
                    frameIndex++;
                });
                setTimeout(function() {
                    loadState = false;
                }, 8000);
                loadState = true;
            }
        }
        setInterval(onFrame, 1000);
        onFrame();
    }
    
    $(window).ready(function() {
        Initialize();
    });                               
</script>

<div class="block">
    <div id="ppgarea">
        <div id="frame"></div>
        <div id="point"><span class="value"></span></div>
    </div>
</div>