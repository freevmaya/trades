<?
    class orderBook {
        function __construct() {
        }
        
        protected function checkOrders(&$order1, &$order2) {
            return ($order1[0] == $order2[0]) && ($order1[1] == $order2[1]) && ($order1[2] == $order2[2]);
        }
        
        protected function indexOf(&$list, &$order) {
            $count = count($list); 
            for ($i=0; $i<$count; $i++) {
                if ($this->checkOrders($list[$i], $order)) return $i;
            }
            return -1;
        }
        
        protected function checkOrderList(&$list1, &$list2) {
            $equal = 0;
            $count = count($list1); 
            for ($i=0; $i<$count; $i++) {
                if ($this->indexOf($list2, $list1[$i]) > -1) $equal++;
            }
            return $equal;
        }
        
        protected function cmdOrderList(&$curList, &$prevList, $oldOrderKof) {
            $result = null;
            if ($count = count($curList)) {
                if ($oldOrderKof < 1)
                    $prevCount = $this->checkOrderList($curList, $prevList); // Сколько ордеров найдено в списке из предшестующего запроса
                
                if (($oldOrderKof == 1) || ($prevCount/$count <= $oldOrderKof)) { 
                    // Если появилось новых ордеров больше чем задано в $oldOrderKof. 1 - принимаем всегда, 0 - принимаем только если все ордера новые 
                    
                    $curPrice = $curList[0][0];
                    $prevPrice = $prevList[0][0];
                    $result = array(
                        'price'=>$curPrice,                                             // Цена в свежей заявке
                        'delta_cur'=>$curPrice - $curList[count($curList) - 1][0],      // Отражает крутость наращивания цен в заявках
                        'delta_prev'=>$prevPrice - $prevList[count($prevList) - 1][0]   // Отличие цен в свежей заявке и в последней из предшествующего запроса, отражает рост или падение цены  
                    );            
                    // Скорость наращивания или падения цены, в процентах к цене
                    $result['priceSpeed'] = ($result['delta_cur']/$curPrice - $result['delta_prev']/$prevPrice) * 100;    
                }
            }
            return $result; 
        }          
        
        // $pairs - валютные пары
        // $speedLimit ['ask':0.1, 'bid': 0.1] - коф. задающие скорость изменения цены, по сравнению с пред. запросом
        // $oldOrderKof  - коэффицент отражающий как быстро появляются новые ордера
        
        public function exec($pairs, $speedLimit, $listcount, $oldOrderKof) {
            $checkdepth = ceil($listcount / 2);
            
            $url = 'https://api.exmo.com/v1/order_book/?pair='.implode(',', $pairs).'&limit='.$listcount;
            $fileName = 'data/order_book.json';
            
            $result = array();
            
            if ($str_data = @file_get_contents($url)) {
                $list = json_decode($str_data, true);
                if ($storage = @file_get_contents($fileName)) { // Получаем созраненые ордера с предыдущего запроса
                    $prev = json_decode($storage, true);
                    foreach ($list as $pair=>$item) {
                        foreach ($speedLimit as $type=>$speed) {
                            $cmdData = $this->cmdOrderList($item[$type], $prev[$pair][$type], $oldOrderKof);
                            $chekLimit = ($speed > 0)?($cmdData['priceSpeed'] > $speed):($cmdData['priceSpeed'] < $speed);
                            if ($cmdData && $chekLimit) {
                                if (!isset($result[$pair])) $result[$pair] = array();
                                $result[$pair][$type] = $cmdData;
                            } 
                        }
                    }
                }
                
                $file = fopen($fileName, 'w+');
                fwrite($file, $str_data);
                fclose($file);
            } 
            return $result;       
        }        
    }
?>