<?
    if ($pair = @($_GET['pair'])) {
        $query = 'https://api.exmo.com/v1/order_book/?pair='.$pair;
        echo file_get_contents($query);
    }
?>