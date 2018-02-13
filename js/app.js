var eventListener = [];
var pairListeners = [];
var valueListener = [];

function time() {
    return (new Date()).getTime();
}

function php_time() {
    return Math.round(time() / 1000);
}

Array.max = function( array ){
    return Math.max.apply( Math, array );
};

Array.min = function( array ){
    return Math.min.apply( Math, array );
};    

function fireValue(val, param) {
    $.each(valueListener, function(i, listener) {
        listener(val, param);
    });
}

function fireEvent(event, value) {
    $.each(eventListener, function(i, listener) {
        if (listener.event == event)
            listener.callback(value);
    });
}

function onEvent(event, callback) {
    eventListener.push({event: event, callback: callback});
}

function r(v, rn) {
    if ($.type(v) == 'number') {
        if ($.type(rn) == 'undefined') {
            rn = v>1000?1:v>10?1000:100000;
        }
        return Math.round(v * rn) / rn;
    } else return v;
}

function reset_pair(pair, sell_min, buy_max) {
    for (var i=0; i<pairListeners.length; i++) pairListeners[i](pair, sell_min, buy_max);
}

function eventsSupport(uid, market, pair) {
    var pushstream = new PushStream({
        host: window.location.hostname,
        port: window.location.port,
        modes: "eventsource",
        messagesPublishedAfter: 5,
        messagesControlByArgument: true
    });

    var mporder, mptrade, This = this;
    this.marketPair = (market, pair)=>{
        var no = market + 'orders-' + pair;
        var nt = market + 'trades-' + pair;

        if (no != mporder) {
            if (mporder) pushstream.removeChannel(mporder);
            if (mptrade) pushstream.removeChannel(mptrade);
            mporder = no;
            mptrade = nt;
            pushstream.addChannel(mporder);
            pushstream.addChannel(mptrade);
        }
    }

    function eventReceived(text, id, channel) {
        try {
            var data = $.parseJSON(text);
        } catch(e) {
            throw e.message;
        }
        if (channel == mporder)
            fireEvent("MARKETPAIRORDERS", data);
        else if (channel == mptrade) 
            fireEvent("MARKETPAIRTRADES", data);
        else fireEvent("EVENTRESPONSE", data);
        //console.log(id + ': ' + text);
    };

    pushstream.onmessage = eventReceived;
    pushstream.addChannel('userevents' + uid);
    this.marketPair(market, pair);

    pushstream.connect();

    pairListeners.push((pair)=>{
        This.marketPair(market, pair);
    });
}

$.fn.extend({
    blink: function() {
        this.each(function() {
            $(this).css('opacity', 0);
        });
    }
})