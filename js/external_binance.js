var external = new (function() {
    this.commission = 0.001;
    var tload = false, This = this, BINANCEURL='https://www.binance.com/', cpair;

    this.initialize = function() {
    	/*
    	setInterval(()=>{
    		if (!tload && This.cpair) This.getTrades(200, This.onResponseTrades);
    	}, 100)

    	pairListeners.push((pair, sell_min, buy_max)=>{
            This.cpair = pair;
        })
        */
    }

    this.onResponseTrades = ()=>{

    }

    this.getOrders = (limit, onComplete)=>{
    	
    }

    this.getTrades = (limit, onComplete)=>{
    	/*
    	tload = true;
    	url = BINANCEURL + 'api/v1/trades?symbol=' + This.cpair.replace('_', '') + '&limit=' + limit;
    	$.ajax({
    		url: url,
			type: 'GET',
			crossDomain: true,
			dataType: 'jsonp',
			success: function(data) {
		    	tload = false;
				console.log(data);
			}
		});
		*/
    }
})();