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
        var av = Math.abs(v);
        if ($.type(rn) == 'undefined') {
            rn = av>1000?1:av>10?100:av>0.1?10000:av>0.001?1000000:100000000;
        }
        return Math.round(v * rn) / rn;
    } else return v;
}

function reset_pair(pair, sell_min, buy_max) {
    for (var i=0; i<pairListeners.length; i++) pairListeners[i](pair, sell_min, buy_max);
}

function timeFormat(unixt) {
    return $.format.date(parseInt(unixt) * 1000, locale.DATEFORMAT);
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

function simpleDrag(elem, onStartDrag, onDrag, onEndDrag) {
    var This=this, spos;
    var ds = 0;

    elem.on('mousedown', (e)=>{
        if (ds == 0) {
            spos = new Vector(e.pageX, e.pageY);
            ds = 1;
        }
    });
    $(window).on('mouseup', (e)=>{
        if (ds > 0) {
            setTimeout(()=>{
                if ((ds == 2) && onEndDrag) onEndDrag();
                ds = 0;
            }, 100);
        }
    });

    $(window).on('mousemove', (e)=>{
        if (ds > 0) {
            var mpos = new Vector(e.pageX, e.pageY);
            if (ds == 1) {
                if (mpos.sub(spos).length() > 3) {
                    ds = 2;
                    if (onStartDrag) onStartDrag();
                }
            } else {
                if (onDrag) onDrag(mpos.sub(spos));
                spos = mpos;
            }
        }
    });

    this.getState = ()=>{
        return ds;
    }
}