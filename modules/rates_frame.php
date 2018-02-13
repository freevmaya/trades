<script type="text/javascript">
    var RATESURL = 'rates.php';
    var f_rates;
    
    function r_refresh() {
        f_rates.css('opacity', 0);
        f_rates[0].src = RATESURL + '?refresh=1&rnd=' + Math.round(Math.random() * 1000000);
    }
    
    $(window).ready(function() {
        f_rates = $('#f_rates');
        f_rates.attr('src', RATESURL);
        f_rates.on('load', function() {
            f_rates.css('opacity', 1);
        });
    });                       
</script>  
<div class="rates">
    <iframe id="f_rates"></iframe>
    <input type="button" value="Обновить" onclick="r_refresh()">
</div>
