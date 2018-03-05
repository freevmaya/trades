<?include_once('modules/mainmenu.php');?>
<?include_once('modules/top_toolbar.php');
	$api 			= DB::line("SELECT * FROM _apikey WHERE uid={$suser['uid']} AND market_id={$market['id']}");
	$apikeyName 	= md5('apikey'.$suser['token']);
	$secretkeyName 	= md5('secretkey'.$suser['token']);
?>

<script type="text/javascript">
	$(window).ready(()=>{
		var form = $('#apikeyForm');
		function onSend() {
			var apikey = $('#apikey').val();
			var apisecret = $('#apisecret').val();
			if ((apikey.length > 0) && (apisecret.length > 0)) {
				var fd = new FormData(form[0]);
				$.jsonPOST('index.php?module=user_json&method=apikey', {data: fd},
					(data)=>{
						if (data.result) ui.message('<?=$locale['CHANGESACCEPTED']?>');
						else ui.error('<?=$locale['WENTWRONG']?>');
					}
				);
			} else ui.error('<?=$locale['ERRORAPIKEY']?>');
		}
		form.on('submit', ()=>{
			onSend();
			return false;
		});

		$('.settings select').selectmenu({
			change: (event, ui_itm)=>{
				var fd = new FormData($('#account_type')[0]);
				$.jsonPOST('index.php?module=user_json&method=account', {data: fd},
					(data)=>{
						if (!data.result) ui.error('<?=$locale['WENTWRONG']?>');
					}
				);
			}
		});
	});
</script>
<div class="settings">
	<fieldset>
	    <legend class="cl-legibly"><?=$locale['APISETTING']?></legend>
		<form id="apikeyForm">
			<table>
				<tr>
					<td class="name">
						<label for="apikey"><?=$locale['APIKEY']?>: </label>
					</td>
					<td>
						<input type="text" id="apikey" name="<?=$apikeyName?>"title="<?=$locale['APIKEYTITLE']?>" value="<?=$api?$api['keyApi']:''?>">
					</td>
				</tr>
				<tr>
					<td class="name">
						<label for="apisecret"><?=$locale['APISECRET']?>: </label>
					</td>
					<td>
						<input type="text" name="<?=$secretkeyName?>" id="apisecret" title="<?=$locale['APISECRETTITLE']?>" value="<?=$api?$api['secretApi']:''?>">
					</td>
				</tr>
			</table>
			<div>
				<button class="ui-button ui-widget ui-corner-all"><?=$locale['OK']?></button>
			</div>
		</form>
	</fieldset>
	<fieldset>
	    <legend class="cl-legibly"><?=$locale['ACCOUNTTYPE']?></legend>
	    <form id="account_type">
	    	<select name="account_type">
	    		<?foreach ($locale['ACCOUNTYPES'] as $type=>$label) {?>
	    			<option value="<?=$type?>" <?=($account_type==$type?'selected':'')?>><?=$label?></option>
	    		<?}?>
	    	</select>
		</form>
	</fieldset>
</div>