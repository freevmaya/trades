var notify = (function() {
	
	$(window).ready(()=>{
		setTimeout(()=>{
			if (Notification.permission == 'granted') {
				navigator.serviceWorker.getRegistration().then(function(reg) {
					reg.showNotification('Hello world!');
				});
			} else {
				/*
				var dlg = ui.dialog(locale.MESSAGE, locale.PUSHAPPREQUIRE, ()=>{
					dlg.dialog('close');
				}, ()=>{

				}, null, true);
				*/
			}
		}, 1000);
	});
})();