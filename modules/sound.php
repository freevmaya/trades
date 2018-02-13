<script>
    var sound;
    $(window).ready(function() {
        sound = new (function() {
            var list = {}; 
            var soundEnabled = $('#soundEnabled');
            var This = this;
            
            if ($.cookie('soundEnabled')) 
                soundEnabled.prop('checked', true);
            
            soundEnabled.on('change', function() {
                $.cookie('soundEnabled', This.isEnabled());
            });
            
            this.isEnabled = function() {
                return soundEnabled.prop('checked');
            }
            
            this.play = function(name) {
                if (list[name] && This.isEnabled()) list[name].play();
            }
            
            list.crit = new Audio('sounds/critical.mp3');
            list.warn = new Audio('sounds/warn.mp3');
            list.ok = new Audio('sounds/beep_01.mp3');
            
            list.crit.volume = 0.4;
            list.warn.volume = 0.2;
            
            onEvent('ALERT', function(stateName) {
                This.play(stateName);
            });
        })();
        
    });
</script>
<span>
    <input type="checkbox" id="soundEnabled">
</span>