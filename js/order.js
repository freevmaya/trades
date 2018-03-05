var Order = function(tdata) {
	tdata = utils.objToFloat(tdata, ['take_profit', 'stop_loss']);

	$.extend(this, tdata);

	var tgs = $.parseJSON(this.triggers);

	this.info = function() {
		var info = '';
		function pitem(type, tg) {
			if ($.type(tg)=='array') $.each(tg, (i, ndata)=>{pitem(type, ndata)});
			else {
				var rng;
				if (tg.value) info += (info?',':'') + locale.TRIGGERS[type] + ' <span>' + r(tg.value) + '</span>';
				else if ((rng = tg.range) || (rng = tg.cur_range)) 
					info += (info?',':'') + locale.TRIGGERS[type] + ' <span>' + r(rng.min) + '-' + r(rng.max) + '</span>';
				else if (rng = tg.range_percent) {
					info += (info?',':'') + locale.TRIGGERS[type] + ' <span>' + r(rng.min * 100) + '%-' + r(rng.max * 100) + '%</span>';
				}
			}
		}

		$.each(tgs, pitem);
		return info;
	}

	this.actionString = function() {
		return locale.ACTIONS[this.action] + ' ' + this.volume;
	}
}