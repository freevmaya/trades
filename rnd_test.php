<pre>
<?

    $arr = array();
    
    $countX = 5;
    $countY = 10000;

    for ($y = 0; $y<$countY; $y++) {
        $arr[$y] = array_fill(0, $countX, 0);
        $arr[$y][rand(0, $countX - 1)] = 1;
    }
    
    $selIndex = rand(0, $countX - 1);
    $hitCount = 0;
    for ($y = 0; $y<$countY; $y++) {
        if (rand(1, $countX) == 1) $selIndex = rand(0, $countX - 1);
        
        if ($arr[$y][$selIndex]) $hitCount++;
    }
    
    echo('Попаданий: '.round($hitCount/$countY * 100).'%');
?>
</pre>