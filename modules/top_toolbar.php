<script type="text/javascript">
	$(window).ready(function() {
		var toobar = $('.top_toolbar');
		var _prevv = {};
		var pair;
		var url = 'index.php?module=user_json';

		function setOrders(ask, bid, askvol, bidvol) {
			toobar.find('.ask').text(r(ask));
			toobar.find('.bid').text(r(bid));

			var allv = askvol + bidvol;
			var volb = bidvol/allv - askvol/allv;

			var volbLabel = toobar.find('.volbLabel');
			volbLabel.text(r(volb, 10));
			volbLabel.css('color', volb>=0?'#8e8eff':'red');
			_prevv[pair] = {ask, bid, askvol, bidvol};
		}

		function refreshOrders(data) {
			setOrders(parseFloat(data.ask_top), parseFloat(data.bid_top), 
					parseFloat(data.ask_glass), parseFloat(data.bid_glass));
		}

		function refreshTrades(data) {
			var bv = parseFloat(data.buy_volumes);
			var sv = parseFloat(data.sell_volumes);
			var vol = bv + sv;
			var tradb = (bv - sv) / vol;
			var tradeBalance = toobar.find('.tradeBalance');
			tradeBalance.text(r(tradb, 10));
			tradeBalance.css('color', tradb>=0?'#8e8eff':'red');
		}

		function onVolumes(a_data) {
			var v = a_data[a_data.length - 1];
			refreshTrades({
				buy_volumes: v[1],
				sell_volumes: v[2],
			});
		}

		function setBalance(v1, v2) {
			var bl = toobar.find('.balance span');
			$(bl[0]).text(r(parseFloat(v1)));
			$(bl[1]).text(r(parseFloat(v2)));
		}

		function onUserEvents(e) {
			if ((e.event == 'BALANCE') && (e.data[pair])) 
				setBalance(e.data[pair][0], e.data[pair][1]);
		}
		
		onEvent('MARKETPAIRORDERS', refreshOrders);
		onEvent('MARKETPAIRTRADES', refreshTrades);
		onEvent('EVENTRESPONSE', onUserEvents);
		onEvent('LASTORDER_RESPONSE', refreshOrders);
		onEvent('VOLUMES_RESPONSE', onVolumes);


		pairListeners.push((a_pair)=>{
			pair = a_pair;
			if (_prevv[pair]) setOrders(_prevv[pair].ask, _prevv[pair].bid, _prevv[pair].askvol, _prevv[pair].bidvol);

			$.getJSON(url, {method: 'getBalance', pair, token}, (a_data)=>{
				if (a_data.result) setBalance(a_data.result[0], a_data.result[1]); 
			}); 
        })
		var panik = toobar.find('.panik');
        panik.button();
        panik.click(()=>{
        	if (!panik.hasClass('active')) {
        		panik.addClass('active');
        		ui.message(locale.PANIKACTIVATE);
        	} else {
        	}
        });
	})
</script>
<div class="top_toolbar">
	<ul>
		<li>
			<?include('modules/sound.php');?>
		</li>
		<li>
	        <?include('modules/pairs.php');?>
		</li>
		<li>
			<span><?=$locale['ASK']?>: </span><i class="ask" title="<?=$locale['ASKPRICE']?>"></i>
		</li>
		<li>
			<span><?=$locale['BID']?>: </span><i class="bid" title="<?=$locale['BIDPRICE']?>"></i>
		</li>
		<li>
			<span><?=$locale['GLASS']?>: </span><i class="volbLabel" title="<?=$locale['RATEVOLORDERS']?>"></i>
		</li>
		<li>
			<span><?=$locale['TRADES']?>: </span><i class="tradeBalance" title="<?=$locale['RATEVOLTRADES']?>"></i>
		</li>
		<?if ($suser) {?>
		<li class="right">
			<span class="panik ui-button" title="<?=$locale['PANIKTITLE']?>"></span>
		</li>
		<li class="right">
			<span class="balance" title="<?=$locale['BALANCECAPTION']?>">
				<span></span>/<span></span>
			</span>
		</li>
		<?}?>
	</ul>
</div>