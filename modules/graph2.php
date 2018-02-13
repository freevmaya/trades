<?
    $pair = $request->getVar('pair', 'BTC_USD');
?>
<script type="text/javascript">
    var TIMEQUANT = 10; //Квант времени = 10 сек.

    var gpDataModule = function() {
        this.getGraphData = function(pair, startTime, endTime, quantum, onResponse) {
            var url = 'index.php?module=graph2_json';

            url += '&pair=' + pair + '&startTime=' + startTime + '&endTime=' + endTime + '&quantum=' + quantum;
            
            $.getJSON(url, null, function(a_data) {
                onResponse(a_data);
            });                
        }
    }

    var graphView = function(pair, elementName, dm) {
        var count, quantum; // count - сколько свечей в видимой части, quantum - период одной свечи, должно быть кратно 10 сек
        var endTime, gd = {min: 0, max: 0, data: null};
        var This = this;
        var loadState = false;
        var axisStyle = {fontSize: 9};
        var element;
        var gpos = {right: 0, bottom: 0, rload: 0, scalex: 1, scaley: 1};

        function resetPeriod(a_endTime, a_quantum, a_count) {
            count = a_count;
            quantum = a_quantum;

            var d = quantum * TIMEQUANT;
            endTime = Math.ceil(a_endTime / d) * d;
            This.refresh();
        }

        function startTimeCalc() {
            return endTime - count * quantum * TIMEQUANT;
        }

        function rOffset() {
            return -Math.ceil(gpos.right);
        }

        function offsetGraph(delta) {
            if (!loadState) {
                gpos.bottom += delta.y * (gd.max - gd.min) / element.height() * gpos.scaley;
                gpos.right -= delta.x / element.width() * count;
                redraw();
            }
        }

        function updateSizeGraph() {
            var mcr = rOffset();
            if (mcr > gpos.rload) This.refresh(function() {
                gpos.rload = mcr;
            });
        }

        function redraw() {
            element.empty();
            var h = element.height();
            var data = google.visualization.arrayToDataTable(gd.data, true);

            var yc = (gd.max - gd.min) / 2;
            var ymm = {
                min: gpos.bottom + gd.min + yc - yc * gpos.scaley,
                max: gpos.bottom + gd.min + yc + yc * gpos.scaley,
            }

            var xmm = {
                min: gpos.right - count + gd.data.length,
                max: gpos.right + gd.data.length
            }

            var options = {
                legend: 'none',
                hAxis: {
                    showTextEvery: Math.ceil(gd.data.length / 12),
                    textStyle: axisStyle,
                    minValue: xmm.min,
                    maxValue: xmm.max,
                    viewWindow: xmm
                },
                vAxis: {
                    minValue: ymm.min,
                    maxValue: ymm.max,
                    viewWindow: ymm,
                    gridlines: {
                        count: 10
                    },
                    textStyle: axisStyle
                },
                candlestick: {
                    fallingColor: { strokeWidth: 0, fill: '#a52714' }, // red
                    risingColor: { strokeWidth: 0, fill: '#03AA33' }   // green
                },
                chartArea: {
                    left:80,top:0, width:'100%', height: '90%'
                },
                legend: {
                    maxLines: 5
                }
            };

            var chart = new google.visualization.CandlestickChart(element[0]);
            chart.draw(data, options);
        }

        function cnvGD(val, i, arr) {
            return [val[0], parseFloat(val[1]), parseFloat(val[2]), parseFloat(val[3]), parseFloat(val[4])];
        }

        this.refresh = (onComplete=null)=>{
            loadState = true;
            dm.getGraphData(pair, startTimeCalc(), endTime, quantum, function(response) {
                loadState = false;
                if (response.length) {
                    gd.data = response.map(cnvGD);
                    gd.min = Array.min(gd.data.map((it)=>{return it[1]}));
                    gd.max = Array.max(gd.data.map((it)=>{return it[4]}));
                    redraw();
                    if (onComplete != null) onComplete();
                }
            })
        }

        function controlInit() {
            var pp = null;
            function onDown(e) {
                pp = new Vector(e.pageX, e.pageY);
            }

            function onUp() {
                if (pp) {
                    pp = null;
                    updateSizeGraph();
                }
            }

            function onMove(e) {
                if (pp) {
                    var delta = (new Vector(e.pageX, e.pageY)).sub(pp);
                    pp = new Vector(e.pageX, e.pageY);
                    offsetGraph(delta);
                }
            }

            element = $(elementName);
            element.mousedown(onDown);
            element.mouseup(onUp);
            element.mousemove(onMove);
        }

        this.zoom = (mz)=>{
            gpos.scaley *= mz;
            redraw();
        }

        this.init = ()=>{
            resetPeriod(php_time(), 6, 5);
        }

        $(window).ready(function() {
            controlInit();
        });
    };

    var graph2 = new graphView('<?=$pair?>', '#graph_div', new gpDataModule());

    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(function() {
        graph2.init();
    });
</script>
<div>
    <div class="result" id="graph_div" style="margin-top:20px; width: 100%; height: 300px;"></div>
    <input type="button" onclick="graph2.refresh()" value="refresh">
    <input type="button" onclick="graph2.zoom(0.9)" value="+">
    <input type="button" onclick="graph2.zoom(1.1)" value="-">
</div>