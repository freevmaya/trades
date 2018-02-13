$.time = function(str) {
    return Math.round((new Date(str)).getTime() / 1000);
}

var orderLogClass = function() {
    var This = this;
    var list = [];
    this.log = function(type, volume, price) {
        list.push({
            time: $.time(),
            type: type,
            volume: volume,
            price: price
        });
        
        console.log(type + ', vol: ' + volume + ', price: ' + price);
    }
}

var orderLog = new orderLogClass();    

var orderCreatorMlt = function(type) {
    var This = this;
    this.createOrder = function(volume, price, onSuccess) {
        setTimeout(function() {
            orderLog.log(type, volume, price);
            onSuccess(volume, price);
        }, 10);
    }
    
    this.begin = function(orders, reqVolume, priceLimit, onSuccess) {
        var successSum = 0;
        var successVol = 0;
        function onOrder(volume, price) {
            successVol += volume;
            successSum += volume * price;
            
            count--;
            if (count == 0) {
                if (successSum > 0) {
                    var price = successSum / successVol;
                    purchase = {
                        volume: successVol,
                        price: price,    
                        time: $.time()               
                    }
                    onSuccess(purchase);
                }                
            }
        }
        var count = 0;
        var acc = 0;
        for (var i=0; i<orders.length; i++) {
            var order = orders[i];
            if ((order.price >= priceLimit.min) && (order.price <= priceLimit.max)) {
                if (acc + order.volume >= reqVolume) {
                    var vol = reqVolume - acc;
                    count++;
                    This.createOrder(vol, order.price, onOrder)
                    break;
                } 
                count++;
                This.createOrder(order.volume, order.price, onOrder);
                acc += order.volume;
            }
        }
    }
}


var orderMarketCreator = function(type) { // Содает ордера по цене рынка
    var This = this;
    this.createOrder = function(volume, price, onSuccess) {
        setTimeout(function() {
            onSuccess({
                        volume: volume,
                        price: 0,    
                        time: $.time()               
                    });
        }, 10);
    }
    
    this.begin = function(orders/*Должен быть NULL*/, reqVolume, priceLimit/*Должен быть NULL*/, onSuccess) {
        this.createOrder(reqVolume, 0, onSuccess);
    }
}

var PinPongClass = function(cur_time, a_value) {

    var purchase = [];
    var curBound, This = this, orders = {bid: [], ask: []}, buyer, seller, sprv;
    var listeners = {};
    var prev_time = cur_time;
    var time_delta;
    var TIMESTDQUANTUM=1/32;

    this.defaultData = function() {
        return {
            ask: 0, // Текущая макс. цена спроса
            bid: 0, // Текущая мин. цена предложений
            framePos: 0, // Текущая позиция рамки
            freePos: 0, // Текущая, свободная позиция цены
            trend: 0,   // Текущий тренд, положительно если восходящий
            rig: 0,     // Связь цены с рамкой, от 0 (связь минимальная) до 1
            trendStart: {top: 0, bottom: 0},
            minBuyTrend: 0, // Минимальный тренд для покупок
            maxSellTrend: 0, // Максимальный тренд для продаж
            trendSmoon: 0.07, // Сгладивание тренда
            maxPurchases: 1,    // Разрешеное количество покупок
            buyMaxPrice: 1.02,  // При покупке. Максиммальное увеличение цены, в заявке, по которой можно покупать 
            sellMinPrice: 0.98,  // При продаже. Минимальное уменьшение цены, в заявке, по которой можно продавать

            bottomPrice: 0, // Вместе с topPrice. TopPrice - TopPrice * topPriceZone начиная с какой цены, следует уменьшать объем покупки. 
            topPrice: 0,    // Ограничение по цене для покупок, работает если не ноль
            topPriceZone: 0, 
            topPriceSmoon: 0, // Если не нуль то сглаживание, подсраивания topPrice под текущую цену

            minProfitPercent: 0.017, // Минимальный процент продажи
            totalProfit: 0, // Суммарный доход
            kinetic: 0, // Кинетическая энергия рамки
            spring: 0,  // Размер рамки
            friction: 1,    // Сопортивление среды рамки
            buyVolume: {min: 0.05, max: 0.2}, // Минимальный и максимальный объем сделки
            tradeEnabled: true, // Разрешить или запретить торговать
            allowed: {  // Запретить покупать и/или продавать
                buy: true,
                sell: true
            },
            session: {
                startTime: cur_time?cur_time:$.time(),
                timeCount: 0
            }
        }
    }
    
    var _data = $.extend(this.defaultData(), {
        ask: a_value,
        bid: a_value,
        framePos: a_value,
        freePos: a_value,
        trendStart: {top: 0, bottom: 0}
    });

    this.on = function(event, listener) {
        if (!listeners[event]) listeners[event] = [];
        listeners[event].push(listener);
    }

    this.fireEvent = function(event, params) {
        $.each(listeners[event], function(i, listener) {
            listener(params);
        });
    }
    
    this.setBuyer = function(a_buyer) {
        buyer = a_buyer;
    }
    
    this.setSeller = function(a_seller) {
        seller = a_seller;
    }
    
    this.minProfit = function(id) {
        return purchase[id].volume * purchase[id].price * _data.minProfitPercent;
    }
    
    this.clearPurchase = function(id) {
        purchase = [];
    }
    
    this.getPurchase = function() {
        return purchase;
    }

    this.isPurchases = function() {
        return purchase.length > 0;
    }

    function addPurchase(a_total) {
        purchase.push(a_total);
        console.log('TOTAL BYE VOL: ' + a_total.volume + ', PRICE: ' + a_total.price);
        This.fireEvent('PURCHASE_BUY', a_total);
    }

// Карта уровней. Например один элемент [16652, 50, 0.5] - уровень на цене 16652, шириной 50 сила 0.5 (от 0 до 1, где 1 - полное отражение)
    var map = [];
    this.setMap = function(a_map) {
        map = a_map;
    }

    this.getMap = function() {
        return map;
    }
    
    this.reset = function(cur_time=0, a_value=0) {
        if (!a_value) a_value = _data.bid;
        prev_time = cur_time; 
        curBound = null;
        orders = {bid: [], ask: []};
        sprv = 0;
        $.extend(_data, {
            ask: a_value,
            bid: a_value,
            framePos: a_value,
            freePos: a_value,
            kinetic: 0,
            totalProfit: 0,
            session: {
                startTime: cur_time?cur_time:$.time(),
                timeCount: 0
            }
        });
    }
    
    this.setData = function(a_data) {
        this.clearPurchase();
        if (a_data.purchase) {
            $.each(a_data.purchase, (i, pitem)=>{addPurchase(pitem);})
            delete(a_data.purchase);
        }
        _data = $.extend({}, a_data);
    }
    
    this.getData = function() {
        return $.extend({}, _data, {purchase: purchase});
    }
    
    this.attr = function(attr, value) {
        _data[attr] = value;
    }
    
    this.getattr = function(attr) {
        return _data[attr];
    }
    
    this.curProfit = function(id) {
        var result = 0;
        if (purchase[id]) {
            var acc = 0;
            var sum = 0;
            for (var i=0; i<orders.ask.length; i++) {
                var order = orders.ask[i];
                if (acc + order.volume >= purchase[id].volume) {
                    sum += order.price * (purchase[id].volume - acc);
                    result = sum - purchase[id].price * purchase[id].volume;
                    break;
                } 
                acc += order.volume;
                sum += order.price * order.volume;
            }
            if (result == 0) console.log('Insufficient orders');
        } 
        return result;  
    }
    
    this.createSellOrder = function(volume, price) {
        console.log('SELL create order: VOL: ' + volume + ', PRICE: ' + price);
        return true;
    }
    
    this.buy = function(power=1) { // power от 0 до 2
        if ((buyer && _data.allowed.buy) && (power > 0)) {
            var bv = _data.buyVolume;
            var reqVolume = bv.min + (bv.max - bv.min) / 2 * Math.min(power, 2); // Расчитываем объем покупки
            if ((_data.topPrice > 0) && (_data.bid > _data.bottomPrice)) {
                reqVolume *= 1 - (_data.bid - _data.bottomPrice) / (_data.topPrice - _data.bottomPrice); 
            }

            if (reqVolume >= bv.min) {
                buyer.begin(orders.bid, reqVolume, {min: _data.bid, max: _data.bid * _data.buyMaxPrice}, function(a_total) {
                    if (a_total.price == 0) a_total.price = _data.bid;
                    addPurchase(a_total);
                });
            } else console.log('Too small amount of purchase');
        } else console.log('not allowed buy');
    } 
    
    this.sell = function(id) {
        if (seller &&  _data.allowed.sell) {
            seller.begin(orders.ask, purchase[id].volume, {min: _data.ask * _data.sellMinPrice, max: _data.ask}, function(a_total) {
                if (a_total.price == 0) a_total.price = _data.ask;
                var profit = ((a_total.volume * a_total.price) - (a_total.volume * purchase[id].price));
                console.log('TOTAL SELL VOL: ' + a_total.volume + ', PRICE: ' + a_total.price + 
                            ', PROFIT: ' + profit);
                This.fireEvent('PURCHASE_SELL', a_total);
                _data.totalProfit += profit;
                if (a_total.volume >= purchase[id].volume) {
                    purchase.splice(id, 1);
                } else {
                    purchase[id].volume -= a_total.volume;
                }
            });
        } else console.log('Not allowed to sell');
    }
    
    this.isSell = function() {
        if (_data.tradeEnabled && (purchase.length > 0)) {
            for (var i = 0; i < purchase.length; i++) {
                if ((This.curProfit(i) > This.minProfit(i)) && (_data.trend < _data.maxSellTrend)) return true;
            }
        }
        return false;
    } 
    
    this.isBuy = function() {
        return _data.tradeEnabled && 
                (_data.trend >= _data.minBuyTrend) && 
                (purchase.length < _data.maxPurchases) &&
                ((_data.topPrice == 0) || (_data.topPrice > _data.bid));
    }
    
    function onBound(bound, repulse=1) {
        if (curBound != bound) {
            curBound = bound;
            if ((bound == -1) && (purchase.length > 0)) {
                for (var i = 0; i < purchase.length; i++) {
                    if (This.isSell(i)) This.sell(i);
                } 
            } else if ((bound == 1) && (This.isBuy())) This.buy(repulse);
        }
    }

    this.repulse = function(price, trend) {
        var kf=1, i, delta, ad, iv, sv, d1, d2;
        if (map) {
            for (i = 0; i < map.length; i++) {
                iv = map[i][1];
                delta = price - map[i][0];
                ad = Math.abs(delta);
                sv = map[i][2];
                if (ad < iv) {
                    d1 = 1 - (iv - ad) / iv * sv;
                    d2 = 1 + (iv - ad) / iv * sv;

                    if (delta > 0) { // если цена над линией
                        if (trend < 0) // и движение вниз
                            kf *= d1        // тормозим
                        else kf *= d2       // ускоряем
                    } else { // Или цена под линией
                        if (trend > 0) // и движение вверх
                            kf *= d1         // тормозим
                        else kf *= d2        // ускоряем
                    }
                }
            }

        }
        return kf;
    }

    this.isBound = function() {
        return sprv >= 1;
    }

    this.doChangeTrend = function(direct, price) {
        _data.trendStart[direct] = price;
        fireEvent('CHANGE_TREND', {direct: direct, price: price});
    }

    this.procTopPrice = function(bid_price) {
        if (_data.topPrice > 0) {
            if (_data.topPrice < bid_price)  {
                _data.topPrice = bid_price;
            } else if (_data.topPriceSmoon > 0) _data.topPrice += (bid_price - _data.topPrice) * _data.topPriceSmoon; // Плавно регулируем границу покупок

            _data.bottomPrice = _data.topPrice - _data.topPrice * _data.topPriceZone;
        }
    }

    this.curValues = function(cur_time, a_bid, a_ask) {
        if (prev_time) {
            time_delta = cur_time - prev_time;
            prev_time = cur_time;
        } else {
            prev_time = cur_time;
            return;
        }

        if ((a_bid.length > 0) && (a_ask.length > 0)) {
            orders.bid = a_bid;
            orders.ask = a_ask;
            var bid_price = orders.bid[0].price;
            var pulse = (bid_price - _data.bid) * time_delta * TIMESTDQUANTUM; 
            var newTrend = _data.trend + (pulse - _data.trend) * _data.trendSmoon;
            if ((_data.trend >= 0) && (newTrend < 0)) {
                this.doChangeTrend('bottom', bid_price);
            } else if ((_data.trend <= 0) && (newTrend > 0)) {
                this.doChangeTrend('top', bid_price);
            }

            this.procTopPrice(bid_price);

            _data.trend = newTrend;

            var alen = _data.bid - _data.framePos;
            var slen = Math.min(Math.abs(alen), _data.spring) * (alen>1?1:-1);
            var repulse = this.repulse(bid_price, _data.trend);
            
            sprv = (Math.abs(slen) * repulse) / _data.spring;
            
            var delta = pulse - _data.kinetic;
            
            if (this.isBound()) {
                if (alen > 0) {
                    if (_data.kinetic < pulse) {
                        _data.kinetic = pulse;
                        _data.framePos = _data.bid - _data.spring;
                        onBound(1, repulse);
                    }
                } else {
                    if (_data.kinetic > pulse) {
                        _data.kinetic = pulse;
                        _data.framePos = _data.bid + _data.spring;
                        onBound(-1, repulse);
                    }
                }
            } else if (_data.rig > 0) _data.kinetic += delta / _data.rig;
            
            _data.freePos += _data.kinetic;
            _data.framePos = _data.freePos;// * (1 - _data.rig) + bid_price * _data.rig;
            _data.kinetic *= _data.friction;
            if (_data.buyMaxPrice < bid_price) _data.buyMaxPrice = bid_price;
            
            _data.bid = bid_price; 
            _data.ask = orders.ask[0].price; 
            _data.session.timeCount = cur_time - _data.session.startTime;


            this.updateDisplay();
        }
    }
    
    this.updateDisplay = function() {
    }
}

function cnvOrders(orders) {
    var result = [];
    $.each(orders, function(i, order) {
        result.push({
            price: parseFloat(order[0]),
            volume: parseFloat(order[1]),
            sum: parseFloat(order[2])
        })
    })
    return result;
}

function ordersFloat(orders) {
    var result = [];
    $.each(orders, function(i, order) {
        result.push({
            price: parseFloat(order.price),
            volume: parseFloat(order.volume),
            sum: parseFloat(order.sum)
        })
    })
    return result;
}