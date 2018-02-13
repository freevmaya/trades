var triggersCtrl = function(triggerLayer, tgsData, tmpls, allTgs, curPrice) {
	if ($.type(tgsData) == 'string') tgsData = $.parseJSON(tgsData);

	var selTgs = triggerLayer.find('.tsel');
	var resultArea = triggerLayer.find('.triggers');
	var listLayer = triggerLayer.find('.tparam');
	var This = this;
	var ntc = triggerLayer.find('.new-trigger');
	
//TRIGGER CONTROLS
	var control_value = function(tmpl, data) {
		var input = tmpl.find(".spinner" );
		if (data) input.val(data.value);

		function onChange() {refreshResult();}

		input.spinner({
			spinchange: onChange,
			change: onChange
		});
		onChange();

		this.getValue = ()=>{
			return input.val();
		}
	}

	var control_time = function(tmpl, data) {
		var input = tmpl.find(".spinner" );
		if (data) input.val(data.time);

		function onChange() {refreshResult();}

		input.spinner({
			spinchange: onChange,
			change: onChange
		});
		onChange();

		this.getValue = ()=>{
			return input.val();
		}
	}

	var control_slider = function(tmpl, data) {
		var This = this;
		var input = tmpl.find(".slider" );
		function updateText() {
			var v = This.getValue();
			tmpl.find(".value").text(v.value);
		}

		function onChange() {
			updateText();
			refreshResult();
		}

		var sdata = data?data.slider:{min:-1,max:1,value:0};

		input.slider($.extend({
			step: (sdata.max - sdata.min) / 100,
			slide: onChange,
			change: onChange
		}, sdata));

		this.getValue = ()=>{
			return {
				min: sdata.min,
				max: sdata.max,
				value: input.slider('value')
			}
		}
		onChange();
	}

	var control_range = function(tmpl, data) {
		var This = this, slr; 
		var k = (data.range && data.range.k)?data.range.k:(curPrice / 100 * 0.2);

		function updateText() {
			var v = This.getValue();
			tmpl.find(".amount").text(locale.FROM + ' ' + v.min + ' ' + locale.TO + ' ' + v.max);
		}

		function onChange() {
			refreshResult();
		}

		this.getValue = ()=>{
			return {
				min: r(slr.slider( "values", 0) * k),
				max: r(slr.slider( "values", 1) * k),
				k  
			};
		}

		var iobj = {
			range: true,
			min: -100,
			max: 100,
			values: [data?data.range.min/k:-50 * k, data?data.range.max/k:50 * k],
			slide: function( event, ui ) {
				updateText();
				onChange();
			}
		}

		slr = tmpl.find( ".slider-range" );
		slr.slider(iobj);
		updateText();
	}	

//-------------------------------------	

	ntc.click(()=>{
		ntc.css('height', 44);
	});

	//selTgs.remove();

	selTgs.empty();
	selTgs.append($('<option>'+ locale.SELECTTGTYPE +'</option>'));
	$.each(allTgs, function(type, label) {
		selTgs.append($('<option value="' + type + '">'+ label +'</option>'));
	})

	triggerLayer.find('.add').click(()=>{
		var type = selTgs.val();
		if (tparams[type]) {
			createControl(type, tparams[type].trigger);
			refreshResult();
		}
	});

	resultArea.css('display', 'none');

	this.getValue = ()=>{
		var res = {};
		listLayer.find('.trgitem').each((i, item)=>{
			var tdata = {}; var type = $(item).data('type');
			$.each(tparams[type].ctrls, (i, ctrl)=>{
				tdata[ctrl] = $(item).data(ctrl).getValue();
			});
			var t = $.type(res[type]);
			if (t=='object') res[type] = [res[type], tdata];
			else if (t=='array') res[type].push(tdata);
			else res[type] = tdata;
		});
		return res;
	}

	function refreshResult() {
		resultArea.val(JSON.stringify(This.getValue()));
	}

	function createControl(type, tdata) {
		if ($.type(tdata)=='array') 
			$.each(tdata, (i, ndata)=>{createControl(type, ndata)});
		else {
			var item = $('<div class="trgitem"><h4>' + allTgs[type] + '</h4><span class="ui-button-icon ui-icon ui-icon-closethick close" title="' + locale.REMOVETRG + '"></span></div>');
			item.data('type', type);
			var tp = tparams[type];
			var caps = locale.TGSCTRLS[type];
			$.each(tp.ctrls, (i, ctrl)=>{
				var tmpl = tmpls.find('.' + ctrl + '_tmpl').clone();
				if (caps)
					$.each(caps[ctrl], (n, cpt)=>{
						tmpl.find(n).text(cpt);
					});

				eval('var ctrlObj = new control_' + ctrl + '(tmpl, tdata)')
				item.data(ctrl, ctrlObj);
				item.append(tmpl);
			});

			item.find('.close').click(()=>{
				item.remove();
				refreshResult();
			});
			listLayer.append(item);
		}
	}
	
	function reset() {
		listLayer.empty();

		$.each(tgsData, createControl);

		refreshResult();
	}

	reset();
}