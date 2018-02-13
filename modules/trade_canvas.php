<?
    $tv_pair = str_replace('_', '', $request->getVar('pair', 'BTC_USD'));
?>
<div>
    <div class="result" id="tradingview" style="margin-top:20px;">
        <!-- TradingView Widget BEGIN -->
        <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
        <script type="text/javascript">
        new TradingView.widget({
          "width": 980,
          "height": 610,
          "symbol": "<?=$tv_pair?>",
          "interval": "D",
          "timezone": "Etc/UTC",
          "theme": "Light",
          "style": "1",
          "locale": "ru",
          "toolbar_bg": "#f1f3f6",
          "enable_publishing": false,
          "allow_symbol_change": true,
          "hideideas": true
        });
        </script>
        <!-- TradingView Widget END -->
        
    </div>
</div>