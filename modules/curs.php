<?   
    $CurObj = new Courses();    
    $currency = $CurObj->courseTo($CurObj->currencyID('EUR'));
    //echo $query;
?>
<div class="curs">
    <?foreach ($currency as $sign=>$course) {?>
        <div class="currency">
            <h3><?=$sign?></h3>
            <span><?=$course?></span>
        </div>
    <?}?>
</div>