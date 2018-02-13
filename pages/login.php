<script type="text/javascript">
	$(window).ready(()=>{
		var form = $('#loginForm');
		function onLogin() {
			var passw = $('#password').val();
			var name = $('#name').val();
			if ((passw.length > 0) && (name.length >= 5)) {
				passw = $.md5(passw);
				$('#password').val(passw);

				var fd = new FormData(form[0]);
				$.jsonPOST('index.php?module=user_json&method=login', {data: fd},
					(data)=>{
						if (data.result) document.location.reload();
						else ui.error('<?=$locale['LOGINERROR']?><br>passwd: ' + passw);
					}
				);
			} else ui.error('<?=$locale['ERRORUSERPASSWD']?>');
		}

		function onRegister() {
			ui.message('<?=$locale['DEVFUNC']?>');
		}

		form.on('submit', ()=>{
			onLogin();
			return false;
		});

		$('#register').click(onRegister);
	});
</script>
<div id="loginLayer" class="login">
	<div>
		<form id="loginForm">
			<div>
				<label for="name"><?=$locale['NAME']?>: </label><input type="text" id="name" name="name"title="<?=$locale['NAMETITLE']?>">
			</div>
			<div>
				<label for="password"><?=$locale['PASSWORD']?>: </label><input type="password" name="password" id="password" title="<?=$locale['PASSWORDTITLE']?>">
			</div>
			<div>
				<button class="ui-button ui-widget ui-corner-all"><?=$locale['LOGIN']?></button>
				<a class="ui-button ui-widget ui-corner-all" id="register"><?=$locale['REGISTER']?></a>
			</div>
		</form>
	</div>
</div>
<div id="registerLayer" class="login">
	<div>
		<form id="registerForm">
			<div>
				<label for="r_name"><?=$locale['NAME']?>: </label><input type="text" id="r_name" name="name"title="<?=$locale['NAMETITLE']?>">
			</div>
			<div>
				<label for="r_password"><?=$locale['PASSWORD']?>: </label><input type="password" name="password" id="r_password" title="<?=$locale['PASSWORDTITLE']?>">
			</div>
			<div>
				<button class="ui-button ui-widget ui-corner-all"><?=$locale['OK']?></button>
			</div>
		</form>
	</div>
</div>