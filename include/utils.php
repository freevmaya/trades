<?
    define('REFRESHMIN', 15);
    
    function curID($cur_sign) {
        $query = "SELECT * FROM ".DBPREF."_currency WHERE `sign`='$cur_sign'";
        $cur_rec = DB::line($query);
        if ($cur_rec) return $cur_rec['cur_id'];
        else {
            DB::query("INSERT INTO ".DBPREF."_currency (`sign`, `name`) VALUES ('{$cur_sign}', '{$cur_sign}')");
            return DB::lastID();
        }        
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
    
    function sesVar($name, $defVal='', $reset=false) {
        GLOBAL $_SESSION, $request;
        
        if ($rval = $request->getVar($name)) return $_SESSION[$name] = $rval;
        else if (!$reset || isset($_SESSION[$name])) return $_SESSION[$name];
        else return $_SESSION[$name] = $defVal;
    }

    function cnvValue($val, $maxVal=1) {
        if (is_string($val)) return floatval(str_replace("%", '', $val)) / 100 * $maxVal;
        else return $val;
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