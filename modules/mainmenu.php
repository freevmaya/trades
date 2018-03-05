<?
	$markets = DB::asArray("SELECT id, name FROM _markets WHERE active=1");
?>
<script type="text/javascript">
	function logout() {
		$.jsonPOST('index.php?module=user_json&method=logout', {},
			(data)=>{
				if (data.result) location.reload();
				else ui.dialog();
				//else 
			}
		);		
	}

	$(window).ready(function() {
		var menu = $( "#menu" ); c = 'waitmenu';
		menu.menu();

		function isShow() {
			return !menu.hasClass(c);
		}

		function vis(v) {
			if (v) menu.removeClass(c); else menu.addClass(c);
		}

		function onShow() {
			if (!isShow()) vis(1);
		}

		menu.click(onShow);
		menu.mouseenter(onShow);

		$(window).click((e)=>{
			if (!$.contains(menu[0], e.target) && (menu[0] != e.target) && isShow()) vis(0);
		});
		
	});
</script>
<ul class="mainmenu waitmenu" id="menu">
	<li><a class="item" href="<?=lk(['page'=>'allcoin'])?>"><?=$locale['SMALLCOINS']?></a></li>
	<li><a class="item" href="<?=lk(['page'=>'orders'])?>"><?=$locale['ORDERS']?></a></li>
	<li><a class="item" href="<?=lk(['page'=>'calc'])?>"><?=$locale['TCALC']?></a></li>
	<li></li>
	<li><a class="item" href="<?=lk(['page'=>'settings'])?>"><?=$locale['SETTINGS']?></a></li>
	<li><div class="item"><?=$locale['MENUMARKETCAP']?></div>
		<ul>
		<?foreach ($markets as $mk) {?>
			<li><a class="item<?=($mk['id']=$market['id']?' current':'')?>" href="<?=lk(['market'=>$mk['name']])?>"><?=$mk['name']?></a></li>
		<?}?>
		</ul>
	</li>
	<li><a class="item" onclick="logout()"><?=$locale['LOGOUT']?></a></li>
	<li><span class="ui-icon ui-icon-grip-dotted-vertical me-handle"></span></li>
</ul>