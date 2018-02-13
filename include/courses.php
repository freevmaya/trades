<?
    class Courses {
        private $curNames;
        private $ACur;
        private $NCur;    
        private $values;                       
        function __construct() {
            $this->curNames = DB::asArray('SELECT * FROM  `_currency`');
            $this->ACur = array();
            $this->NCur = array();
            foreach ($this->curNames as $cur) {
                $this->ACur[$cur['sign']] = $cur['cur_id'];
                $this->NCur[$cur['cur_id']] = $cur['sign'];
            }
              
            $count = pow(count($this->curNames), 2);  
            $query = "SELECT * FROM _exmo WHERE cur_in ORDER BY time  DESC, cur_in, cur_out LIMIT 0, $count";
            $this->values = DB::asArray($query);
            foreach ($this->values as $i=>$item) $this->values[$i]['price'] = ($item['sell_price'] + $item['buy_price']) / 2;
        }   
        
        public function currencyID($curName) {
            foreach ($this->curNames as $cur) if ($cur['sign'] == $curName) return $cur['cur_id'];
        }     
                
        public function findPair($cur_id, $pair_id, $stack = 0) {
            
            foreach ($this->values as $item) {
                if (($item['cur_in'] == $pair_id) && ($item['cur_out'] == $cur_id)) {
                    return $item['price'];
                }  
                if (($item['cur_out'] == $pair_id) && ($item['cur_in'] == $cur_id)) {
                    return $item['price'];
                }
            }
            
            $stack++;
            if ($stack < 2) {
                foreach ($this->values as $item) {
                    if ($item['cur_in'] == $cur_id) {
                        if ($res = $this->findPair($item['cur_out'], $pair_id, $stack)) {
                            return $res / $item['price'];
                        }
                    } 
                    if ($item['cur_out'] == $cur_id) {
                        if ($res = $this->findPair($item['cur_in'], $pair_id, $stack)) {
                            return $res / $item['price'];
                        }
                    }
                } 
            }
            return 0;
        }
        
        public function courseTo($baseCurID) {
            $currency = array();
            foreach ($this->curNames as $cur) {
                if ($baseCurID != $cur['cur_id']) {
                    $name = $this->NCur[$cur['cur_id']];
                    $currency[$name] = $this->findPair($cur['cur_id'], $baseCurID);
                }  
            }
            return $currency;
        }       
    }
?>