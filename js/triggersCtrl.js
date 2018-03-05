var minmax;
onEvent('PAIRMINMAX', (a_minmax)=>{
	minmax=a_minmax;
});

var triggersCtrl = function(triggerLayer, tgsData, tmpls, allTgs, curPrice) {
	if ($.type(tgsData) == 'string') tgsData = $.parseJSON(tgsData);

	var selTgs = triggerLayer.find('.tsel');
	var resultArea = triggerLayer.find('.triggers');
	var listLayer = triggerLayer.find('.tparam');
	var This = this;
	var ntc = triggerLayer.find('.new-trigger');
	var ifocus;

//TRIGGER CONTROLS
	var control_value = function(tmpl, data) {
		var input = tmpl.find(".spinner" ), This=this;
		if (data) input.val(data.value);

		function onChange() {refreshResult();}

		input.spinner({
			spinchange: onChange,
			change: onChange
		});

		input.on('focus', ()=>{ifocus=This;});
		//input.on('blur', ()=>{setTimeout(()=>{if (ifocus==This) ifocus=null;}, 500)});

		onChange();

		this.getValue = ()=>{
			return input.val();
		}

		this.setValue = (val)=>{
			input.val(val);
			refreshResult();
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
		var k = (data.range && data.range.k)?(utils.strToFloat(data.range.k, minmax.max)):(curPrice / 100 * 0.2);

		function updateText() {
			var v = This.getValue();
			tmpl.find(".amount").text(locale.FROM + ' ' + v.min + ' ' + locale.TO + ' ' + v.max);
		}

		function onChange() {
			refreshResult();
		}

		this.getValue = ()=>{
			return {
				min: r(slr.slider( "values", 0)),
				max: r(slr.slider( "values", 1)),
				k
			};
		}

		var iobj = {
			range: true,
			min: -k,
			max: k,
			values: [data?utils.strToFloat(data.range.min, minmax.max):-k * 0.5, 
					data?utils.strToFloat(data.range.max, minmax.max):k * 0.5],
			slide: function( event, ui ) {
				updateText();
				onChange();
			}
		}

		slr = tmpl.find(".slider-range");
		slr.slider(iobj);
		updateText();
	}

	var control_range_percent = function(tmpl, data) {
		var This = this, slr; 
		function updateText() {
			var v = This.getValue();
			tmpl.find(".amount").text(locale.FROM + ' ' + Math.round(v.min * 100) + '% ' + locale.TO + ' ' + Math.round(v.max * 100) + '%');
		}

		this.getValue = ()=>{
			return {
				min: slr.slider( "values", 0) / 100,
				max: slr.slider( "values", 1) / 100
			};
		}

		var iobj = {
			range: true,
			min: -100,
			max: 100,
			values: [data?data.range_percent.min * 100:-50, data?data.range_percent.max * 100:50],
			slide: function( event, ui ) {updateText();refreshResult();}
		}

		slr = tmpl.find(".slider-range");
		slr.slider(iobj);
		updateText();
	}	

	var control_cur_range = function(tmpl, data) {
		var This = this, slr;

		function updateText() {
			var v = This.getValue();
			tmpl.find(".amount").text(locale.FROM + ' ' + v.min + ' ' + locale.TO + ' ' + v.max);
		}

		function onChange() {
			refreshResult();
		}

		this.getValue = ()=>{
			return {
				min: r(slr.slider( "values", 0)),
				max: r(slr.slider( "values", 1))
			};
		}

		var vs = [minmax.min, minmax.max];
		if (data && data.cur_range) {
			eval("vs[0]=" + data.cur_range.min);
			eval("vs[1]=" + data.cur_range.max);
		}

		var iobj = {
			range: true,
			min: minmax.min * 0.95,
			max: minmax.max * 1.05,
			values: vs,
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

	this.applyPrice = (price)=>{
		if (ifocus) ifocus.setValue(r(price));
	}

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