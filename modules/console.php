<script type="text/javascript">
	$(window).ready(()=>{
		var consoleLayer = $('.console');
		var itTml = $('.templates .item');
		var list = consoleLayer.find('.list');

		function parseData(data) {
			if ($.type(data) == 'string') {
				if (data[0] != '{') return data;
				data = JSON.parse(data);
			}
			return data.pair + (data.action?(', ' + data.action.type):'') + 
					', ' + (data.error?('error: ' + data.error):('price: ' + r(data.price))); 
		}

		function addItem(time, event, data, type) {
			var elem = itTml.clone();
			elem.find('.time').text(time);
			elem.find('.event').text(event);
			elem.find('.info').html(parseData(data));
			if (type) elem.addClass(type);
			list.append(elem);

			if (consoleLayer.css('display') == 'none') {
				consoleLayer.addClass('conshow');
				//consoleLayer.parent().css('height', $(window).height() * 0.14);
			}
		}

		function refresh() {
			var url = 'index.php?module=user_events_json';
			$.getJSON(url, {method: 'getUserEvents', token: token}, (list)=>{
				$.each(list, (i, item)=>{
					addItem(item.time, item.event, item.data, item.type);
				});
			});
		}

		onEvent('EVENTRESPONSE', (e)=>{
			if (['CONSOLE', 'ORDERSUCCESS', 'FAILORDER'].includes(e.event)) addItem(e.time, e.event, e.data, e.type);
		});

		refresh();
	});
</script>
<div class="console">
	<div class="panel">
		<button class="ui-button ui-widget ui-corner-all ui-button-icon-only" title="Button with icon only">
	    	<span class="ui-icon ui-icon-gear"></span>
	  	</button>
	</div>
	<div class="list">
	</div>
	<div class="templates">
		<div class="item">
			<span class="time"></span><span class="event"></span><span class="info"></span>
		</div>
	</div>
</div>