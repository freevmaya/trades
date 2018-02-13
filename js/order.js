var Order = function(tdata) {
	tdata = utils.objToFloat(tdata, ['take_profit', 'stop_loss']);

	$.extend(this, tdata);

	var tgs = $.parseJSON(this.triggers);

	this.info = function() {
		var info = '';
		function pitem(type, tg) {
			if ($.type(tg)=='array') $.each(tg, (i, ndata)=>{pitem(type, ndata)});
			else {
				if (tg.value) info += (info?',':'') + locale.TRIGGERS[type] + ' <span>' + tg.value + '</span>';
				else if (tg.range) info += (info?',':'') + locale.TRIGGERS[type] + ' <span>' + tg.range.min + '-' + tg.range.max + '</span>';
			}
		}

		$.each(tgs, pitem);
		return locale.ACTIONS[this.action] + ' ' + this.volume + ' ' + info;
	}
}