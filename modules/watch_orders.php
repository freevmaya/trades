<script type="text/javascript">
	var order = new (function() {
		var layer;
		var url = 'index.php?module=watch_orders_json';
		var tmpl;
		var item_tmpl;
		var pair;
		var This = this;
		var allTriggers = <?=json_encode($locale['TRIGGERS'])?>;
		var price=1, priory={active:1, test:2, process:3, success:4, inactive:5, fail: 6};
		var lastDlg;

		onEvent('MARKETPAIRORDERS', function(data) {
			price = parseFloat((parseFloat(data.ask_top) + parseFloat(data.bid_top)) / 2);
		});

		onEvent('GRAPHITEMSELECT', function(e) {
			var ca = pair.split('_');
			if (e.data.length == 3) {
				var avg = (e.data[1] + e.data[2]) / 2;
				This.edit({pair: pair, action: 'buy', state: 'active', volume: '50%/' + ca[1], triggers: {stop: {value: r(avg)}}});
			} else {
				var cd = e.data[3] - e.data[2];
				var avg = (e.data[3] + e.data[2]) / 2;
				var vr = 0.2;
				This.edit({pair: pair, action: 'buy', state: 'active', volume: '50%/' + ca[1], triggers: {
					candle: {
						range: {
			        		min: cd<0?(cd * (1 + vr)):(cd * (1 - vr)),
			        		max: cd>0?(cd * (1 + vr)):(cd * (1 - vr)),
			        		k: Math.abs(cd) / 100 * 2
			        	},
			        	time: 30 * e.quant
					},
					stop: {value: r(avg)}
				}});
			}
		});

		onEvent('PRICEACCEPT', function(e) {
			if (lastDlg) lastDlg.setPrice(e.price);
			else {
				var ca = pair.split('_');
				This.edit({pair: pair, action: 'buy', state: 'active', volume: '50%/' + ca[1], triggers: {stop: {value: r(e.price)}}});
			}
		});


		function appendItem(item) {
			var elem = item_tmpl.clone();
			var info = elem.find('.info');
			var action = elem.find('.action');
			item = new Order(item);

			layer.append(elem);
			elem.addClass(item.state);
			elem.addClass(item.action);
			if (item.state=='test') {
				elem.find('.state')
					.text('t')
					.attr('title', locale.BEGINTESTTITLE);
			}
			action.html(item.actionString());
			info.html(item.info());
			elem.click(onItemClick);
			elem.data('data', item);
		}

		function onItemClick(e) {
			var data = $(e.currentTarget).data('data');
			This.edit(data);
		}

		function updateList(list) {
			layer.empty();
			list.sort((a, b)=>{return priory[a.state]-priory[b.state]});
			$.each(list, function(i, item) {appendItem(item);});
			fireEvent('ORDERSRESPONSE', list);
		}

		function refreshList() {
			$.getJSON(url, {method: 'getList', pair: pair, token: token, market: '<?=$market['name']?>'}, (a_data)=>{
				if (a_data.data) updateList(a_data.data);
				if (a_data.minmax) fireEvent('PAIRMINMAX', utils.objToFloat(a_data.minmax, ['min', 'max']));
			});  
		}

		function error(msg) {
			alert('ERROR: ' + msg);
		}

		function checkResponse(data) {
			if (!(data.data && data.data.result)) ui.error('<?=$locale['WENTWRONG']?>');			
		}

		this.deleteOrder = (id)=>{
			$.getJSON(url, {method: 'deleteOrder', id: id, token: token}, checkResponse);
		}

		this.edit = (item)=>{
			var This = this, dlg, triggers;

			var t = item.id?'<?=$locale['EDITORDER']?>':'<?=$locale['NEWORDER']?>'
			var bt = {};

			if (item) bt = {
				'<?=$locale['DELETE']?>': ()=>{
					This.deleteOrder(item.id);
					dlg.dialog('close');
				}
			}

			lastDlg = dlg = ui.dialog(t, tmpl.clone(), function() {
				var tg = dlg.find('.triggers').val();
				var fd = new FormData(dlg.find('form')[0]);
				try {
					var tgjn = $.parseJSON(tg);
				} catch (err) {
					error('<?=$locale['VALUEERROR']?>');
					return false;
				}

				if (tgjn.length==0) error('<?=$locale['TRIGGERSEMPTY']?>');
				else {
					$.jsonPOST(url, {data: fd}, checkResponse);
					return true;
				}
				return false;
			}, ()=>{}, bt, false);

			dlg.dialog({close: (event, ui)=>{lastDlg=null}});

			dlg.setPrice = (price)=>{triggers.applyPrice(price)}

			if (!item) item = defOrder();

			var currency = (pair.split('_'))[0];

			var exlayer = dlg.find(".extends_layer");
			exlayer.find("legend").click(showExtend);

			utils.fillPairs(dlg.find('[name="pair"]'), item.pair);			
			utils.fillDlg(dlg, item);
			triggers = new triggersCtrl(dlg.find(".triggers_layer"), item.triggers, $('.trigger_templates'), allTriggers, price);

			dlg.find('select').selectmenu();
			dlg.find(".spinner" ).spinner();
			if (item.state == 'success') {
				dlg.find('.success_report').text(item.state_time);
			}

			var act = dlg.find('[name="action"]');
			act.selectmenu({select: (e, ui)=>{updateAction(ui.item.value);}});

			function showExtend() {
				exlayer.find('.hiddePanel').css('height', 83);				
			}

			function updateAction(action) {
				var b = action=='buy';
				dlg.removeClass(b?'sell':'buy');
				dlg.addClass(b?'buy':'sell');
				if (b && ((item.take_profit > 0) || (item.stop_loss))) showExtend();
			}

			updateAction(item.action);

			dlg.toCenter();
		}

		function defOrder() {
			return {pair: pair, action: 'buy', state: 'inactive', triggers: {stop: {value: 0}}};
		}

		function setPair(a_pair) {
			if (pair != a_pair) {
				pair = a_pair;
				refreshList();
			}
		}

		function onUserEvents(e) {
			if (!e.data.pair || (e.data.pair == pair)) {

				if ((['ORDERSUCCESS', 'UPDATEORDER', 'DELETEORDER', 'FAILORDER'].indexOf(e.event) > -1)) {
					if (e.data.state != 'test') refreshList();
				} 

				if (['ORDERSUCCESS', 'FAILORDER'].indexOf(e.event) > -1) {
					if (e.data.state) fireEvent('ALERT', (e.data.state=='success')?'ok':'warn');
				}
			}
		}

        pairListeners.push(function(a_pair, sell_min, buy_max) {
			setPair(a_pair);
        })   

		$(window).ready(()=>{
			layer = $('#worders');
			tmpl = $('.new_order_form').clone();
			item_tmpl = $('.templates .order_item').clone();
			tmpl.remove();

			onEvent('EVENTRESPONSE', onUserEvents);
		});
	})();
</script>
<div>
	<h3><?=$locale['ORDERS']?></h3>
	<table id="worders"></table>
	<div class="clear"></div>
	<input type="button" value="<?=$locale['ADD']?>" onclick="order.edit(0)">
</div>
<div class="templates">
	<form method="POST" action="" class="new_order_form">
		<div>
			<h3><?=$locale['PAIR']?></h3>
			<div>
				<select name="pair"></select>
			</div>
			<h3><?=$locale['ACTION']?></h3>
			<div class="order-type">
				    <select name="action">
				    <?$i=0;foreach ($locale['TYPEORDERS'] as $action=>$label) {$i++?>
				    	<option value="<?=$action?>"><?=$label?></option>
				    <?}?>
					</select>
			</div>
			<h3><?=$locale['VOLUME']?></h3>
			<div>
				<input type="text" name="volume" id="volume" class="spinner">
			</div>
			<h3><?=$locale['TRIGGERS_LABEL']?></h3>
			<div class="triggers_layer">
				<div class="tparam">
				</div>
				<fieldset class="new-trigger hiddePanel">
				    <legend><?=$locale['ADDTRIGGER']?></legend>
					<select class="tsel" name="tsel">
					</select>
					<input type="button" value="+" style="width: 18px;" class="add"></input>
				</fieldset>
				<textarea name="triggers" class="triggers"></textarea>
			</div>
			<div class="extends_layer">
				<fieldset class="hiddePanel">
				    <legend><?=$locale['EXTENDSLAYER']?></legend>
				    <table>
				    	<tr><td><?=$locale['TAKEPROFIT']?></td><td><input type="text" name="take_profit" class="spinner" size="10"></td></tr>
				    	<tr><td><?=$locale['STOPLOSS']?></td><td><input type="text" name="stop_loss" class="spinner" size="10"></td></tr>
				    </table>
				</fieldset>
			</div>
			<h3><?=$locale['STATETITLE']?></h3>
			<div>
			    <select name="state">
			    <?$i=0;foreach ($locale['STATES'] as $state=>$label) {$i++?>
			    	<option value="<?=$state?>"><?=$label?></option>
			    <?}?>
				</select>	
				<div class="success_report cl-mean"></div>
			</div>
			<input type="hidden" name="method" value="updateOrder">
			<input type="hidden" name="token" value="<?=$suser['token']?>">
			<input type="hidden" name="id" value="">
		</div>
	</form>

	<div class="trigger_templates">
		<div class="value_tmpl">
			<fieldset>
			    <legend class="cl-mean"></legend>
			    <input type="text" class="spinner value">
		  	</fieldset>
		</div>
		<div class="range_tmpl range">
			<fieldset>
				<legend class="cl-mean"></legend>
				<div class="caption"></div>
				<div class="amount"></div>
				<div class="slider-range"></div>
 		  	</fieldset>
		</div>
		<div class="range_percent_tmpl range">
			<fieldset>
				<legend class="cl-mean"></legend>
				<div class="caption"></div>
				<div class="amount"></div>
				<div class="slider-range"></div>
 		  	</fieldset>
		</div>
		<div class="cur_range_tmpl range">
			<fieldset>
				<legend class="cl-mean"></legend>
				<div class="caption"></div>
				<div class="amount"></div>
				<div class="slider-range"></div>
 		  	</fieldset>
		</div>
		<div class="time_tmpl">
			<fieldset>
			    <legend class="cl-mean"></legend>
			    <input type="text" class="spinner value">
		  	</fieldset>
		</div>
		<div class="slider_tmpl">
			<fieldset>
			    <legend class="cl-mean"></legend>
				<div class="caption"></div>
				<div class="value"></div>
			    <div class="slider"></div>
		  	</fieldset>
		</div>
	</div>

	<div>
		<table>
			<tr class="order_item">
				<td class="state ui-button ui-corner-left tx"></td>
				<td class="action ui-button tx"></td>
				<td class="ui-button ui-corner-right tx">
					<div class="info"></div>
				</td>
			</tr>
		</table>
	</div>
</div>