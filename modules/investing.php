<script type="text/javascript">
    var investing = new (function() {
        var layer, This = this;
        
        this.refresh = function() {
            var url = 'index.php?module=investing_json';
            var invl = $('.investing');

            $.getJSON(url, null, function(a_data) {
                layer.empty();
                $.each(a_data, function(pair, itm) {     
                    layer.append("<tr><td><a href=\"" + itm.url + "\" target=\"_blank\">" + pair + "</a></td>" + itm.item + "</tr>");
                });
                invl.css('opacity', 1);
            });
            invl.css('opacity', 0);
        }
        
        $(window).ready(function() {
            layer = $('#investing');
            This.refresh();
        });
    })();
</script>
<div class="investing">
    <table>
        <tr>
            <th>Пара</th>
            <th>5 мин.</th>
            <th>15 мин.</th>
            <th>час</th>
            <th>день</th>
            <th>месяц</th>
        </tr>
        <tbody id="investing">
        </tbody>
    </table>
    <input type="button" onclick="investing.refresh()" value="refresh">
</div>