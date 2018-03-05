<?
    define('REFRESHMIN', 15);    

    GLOBAL $utils_cur_ids; 
    $utils_cur_ids = [];
    
    function curID($cur_sign) {
        GLOBAL $utils_cur_ids;
        if (!isset($utils_cur_ids[$cur_sign])) {
            $query = "SELECT * FROM ".DBPREF."_currency";
            $cur_rec = DB::asArray($query);

            foreach ($cur_rec as $item)
                $utils_cur_ids[$item['sign']] = $item['cur_id'];
        }

        if (isset($utils_cur_ids[$cur_sign])) return $utils_cur_ids[$cur_sign];
        else {
            DB::query("INSERT INTO ".DBPREF."_currency (`sign`, `name`) VALUES ('{$cur_sign}', '{$cur_sign}')");
            return $utils_cur_ids[$cur_sign] = DB::lastID();
        }
    }

    function curSign($cur_id) {
        $query = "SELECT sign FROM _currency WHERE cur_id={$cur_id}";
        $rec = DB::line($query, null, MYSQLI_BOTH);
        return $rec[0];
    }
    
    function sesVar($name, $defVal='', $reset=false) {
        GLOBAL $_SESSION, $request;
        
        if ($rval = $request->getVar($name)) return $_SESSION[$name] = $rval;
        else if (!$reset || isset($_SESSION[$name])) return $_SESSION[$name];
        else return $_SESSION[$name] = $defVal;
    }

    function pairIDs($pair) {
        $pairA   = explode('_', $pair);
        return ['cur_in'=>curID($pairA[0]), 'cur_out'=>curID($pairA[1])];
    }

    function getMPID($market_id, $pair_in, $pair_out) {
        return $market_id.sprintf("%'.03d", $pair_in).sprintf("%'.03d", $pair_out);
    }
    
    function r($val, $round) {
        return round($val * $round)/$round;
    }
      
    function getCachedData($fileName, $queryA, $refresh=false) {
        $filetime = @filectime($fileName);
        
        $REALDATA = $refresh || (((time() - $filetime) / 60) > REFRESHMIN);
        if ($REALDATA) $query = $queryA;
        else $query = $fileName;
        
        $str_cnt = file_get_contents($query);
        
        if ($REALDATA) {
            $file = fopen($fileName, 'w+');
            fwrite($file, $str_cnt);
            fclose($file);
        }
        return json_decode($str_cnt, true);
    } 
    
    function cnvValue($val, $maxVal=1) {
        if (is_string($val) && (strpos($val, "%") > -1)) return floatval(str_replace("%", '', $val)) / 100 * $maxVal;
        else return floatval($val);
    }

    function lk($params=null) {
        $result = "index.php";
        if ($params) {
            $pa = '';
            foreach ($params as $n=>$v) {
                $pa .= ($pa?'&':'?').$n.'='.$v;
            }
            $result .= $pa;
        }
        return $result;
    }
?>