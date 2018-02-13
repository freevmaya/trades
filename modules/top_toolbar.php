<script type="text/javascript">
	$(window).ready(function() {
		var toobar = $('.top_toolbar');
		var _prevv = {};
		var pair;

		function setOrders(ask, bid, askvol, bidvol) {
			toobar.find('.ask').text(r(ask));
			toobar.find('.bid').text(r(bid));

			var volb = bidvol - askvol;
			var volbLabel = toobar.find('.volbLabel');

			volbLabel.text(r(volb, 10));
			volbLabel.css('color', volb>=0?'#8e8eff':'red');

			_prevv[pair] = {ask, bid, askvol, bidvol};
		}

		function refreshOrders(data) {
			var vol = data.ask_volumes + data.bid_volumes;
			setOrders(parseFloat(data.ask_price), parseFloat(data.bid_price), data.ask_volumes/vol, data.bid_volumes/vol);
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

		onEvent('MARKETPAIRORDERS', refreshOrders);
		onEvent('MARKETPAIRTRADES', refreshTrades);

		pairListeners.push((a_pair)=>{
			pair = a_pair;
			if (_prevv[pair]) setOrders(_prevv[pair].ask, _prevv[pair].bid, _prevv[pair].askvol, _prevv[pair].bidvol);
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
		<li style="float:right">
			<span class="panik ui-button" title="<?=$locale['PANIKTITLE']?>"></span>
		</li>
		<?}?>
	</ul>
</div>