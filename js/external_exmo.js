var external = new (function() {
    this.getPairs = function() {
        return ['BTC_USD', 'BTC_EUR', 'BTC_RUB', 'BTC_UAH', 'DASH_BTC', 'DASH_USD', 'DASH_RUB', 'ETH_BTC', 'ETH_LTC', 'ETH_USD', 'ETH_EUR', 'ETH_RUB', 'ETH_UAH', 'ETC_BTC', 'ETC_USD', 'ETC_RUB', 'LTC_BTC', 'LTC_USD', 'LTC_EUR', 'LTC_RUB', 'ZEC_BTC', 'ZEC_USD', 'ZEC_EUR', 'ZEC_RUB', 'XRP_BTC', 'XRP_USD', 'XRP_RUB', 'XMR_BTC', 'XMR_USD', 'XMR_EUR', 'BTC_USDT', 'ETH_USDT', 'USDT_USD', 'USDT_RUB', 'USD_RUB', 'DOGE_BTC', 'WAVES_BTC', 'WAVES_RUB', 'KICK_BTC', 'KICK_ETH', 'BCH_USD'];
    }

    this.commission = 0.002;
})();