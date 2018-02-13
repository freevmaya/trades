<script type="text/javascript">
	var order = new (function() {
		var layer;
		var url = 'index.php?module=watch_orders_json';
		var tmpl;
		var item_tmpl;
		var pair;
		var This = this;

		function appendItem(item) {
			var elem = item_tmpl.clone();
			layer.append(elem);
			elem.addClass(item.state);
			elem.addClass(item.action);
			elem.find('h3').text(item.pair);
			elem.find('.info').text(item.action + ' ' + item.volume);
			elem.click(onItemClick);
			elem.data('data', item);
		}

		function onItemClick(e) {
			var data = $(e.currentTarget).data('data');
			This.edit(data);
		}

		function updateList(list) {
			layer.empty();
			$.each(list, function(i, item) {appendItem(item);});
		}

		function refreshList() {
			$.getJSON(url, {method: 'getList', pair: pair, token: token}, updateList);  
		}

		function error(msg) {
			alert('ERROR: ' + msg);
		}

		function checkResponse(data) {
			if (data.result) refreshList();
			else ui.error('<?=$locale['WENTWRONG']?>');			
		}

		this.deleteOrder = (id)=>{
			$.getJSON(url, {method: 'deleteOrder', id: id, token: token}, checkResponse);
		}

		this.edit = (item)=>{
			var t = item?'<?=$locale['EDITORDER']?>':'<?=$locale['NEWORDER']?>'
			var bt = {};
			if (item) bt = {
				'<?=$locale['DELETE']?>': ()=>{
					This.deleteOrder(item.id);
					dlg.dialog('close');
				}
			}
			var dlg = ui.dialog(t, tmpl.clone(), function() {
				var tg = dlg.find('#triggers').val();
				var fd = new FormData(dlg.find('form')[0]);
				try {
					var tgjn = $.parseJSON(tg);
				} catch (err) {
					error('<?=$locale['VALUEERROR']?>');
					return false;
				}

				if (tgjn.length==0) error('<?=$locale['VALUEEMPTY']?>');
				else {
					$.jsonPOST(url, {data: fd}, checkResponse);
					return true;
				}
				return false;
			}, ()=>{}, bt);

			if (item) fillDlg(dlg, item);
			else fillDlg(dlg, defOrder());

			dlg.find(".order-type input" ).checkboxradio();
			dlg.find(".spinner" ).spinner();
		}

		function fillDlg(dlg, item) {
			if (item) {
				for (var n in item) {
					var v = item[n];
					var ctrl = dlg.find('[name="' + n + '"]');
					if (ctrl.length > 0) ctrl.val(v);
					else {
						dlg.find('[name="' + n + '[]"]').each(function(i, ctrl) {
							ctrl = $(ctrl);
							if (ctrl.val() == v) ctrl.attr('checked', 1);
						});
					}
				}
			}
		}

		function defOrder() {
			return {pair: pair, 'action': 'buy', 'state': 'inactive', 'triggers': '[{}]'};
		}

		function setPair(a_pair) {
			if (pair != a_pair) {
				pair = a_pair;
				refreshList();
			}
		}

        pairListeners.push(function(a_pair, sell_min, buy_max) {
			setPair(a_pair);
        })   

		$(window).ready(()=>{
			layer = $('#worders');
			tmpl = $('#new_order_form').clone();
			item_tmpl = $('.templates .order_item').clone();
			$('#new_order_form').remove();
		});
	})();
</script>
<div>
	<h3><?=$locale['ORDERS']?></h3>
	<div id="worders">
	</div>
	<div class="clear"></div>
	<input type="button" value="<?=$locale['ADD']?>" onclick="order.edit(0)">
</div>
<div class="templates">
	<form method="POST" action="" id="new_order_form">
		<div>
			<h3><?=$locale['PAIR']?></h3>
			<div>
				<input type="text" name="pair" value="<?=$pair?>">
			</div>
			<div class="order-type">
				<fieldset>
				    <legend><?=$locale['SELECTACTION']?>: </legend>
				    <?$i=0;foreach ($locale['TYPEORDERS'] as $action=>$label) {$i++?>
				    <label for="action-<?=$i?>"><?=$label?></label>
				    <input type="radio" name="action[]" id="action-<?=$i?>" value="<?=$action?>">
				    <?}?>
			  	</fieldset>
			</div>
			<div class="order-type">
				<fieldset>
				    <legend><?=$locale['STATETITLE']?>: </legend>
				    <?$i=0;foreach ($locale['STATES'] as $state=>$label) {$i++?>
				    <label for="state-<?=$i?>"><?=$label?></label>
				    <input type="radio" name="state[]" id="state-<?=$i?>" value="<?=$state?>">
				    <?}?>
			  	</fieldset>
			</div>
			<h3><?=$locale['VOLUME']?></h3>
			<div>
				<input type="text" name="volume" id="volume" class="spinner">
			</div>
			<h3><?=$locale['TRIGGERS']?></h3>
			<div><textarea name="triggers" id="triggers"></textarea></div>
			<input type="hidden" name="method" value="updateOrder">
			<input type="hidden" name="token" value="<?=$suser['token']?>">
			<input type="hidden" name="id" value="">
		</div>
	</form>

	<div class="order_item ui-button ui-corner-all ui-corner-bottom ui-corner-right ui-corner-br">
		<h3></h3>
		<div class="info"></div>
	</div>
</div>