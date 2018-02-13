<?
    $pair = @($_GET['pair']);
    $query = 'https://api.exmo.com/v1/trades/?pair='.$pair;
    echo file_get_contents($query);
?>