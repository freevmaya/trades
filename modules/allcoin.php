<table>
	<tr>
	</tr>
<?
	$query = "SELECT * FROM _coinmarket WHERE `price_usd` > 0.1 AND `price_usd` < 2 GROUP BY `symbol`, `last_updated` ORDER BY `percent_change_1h` DESC, `symbol`, `price_usd`,`last_updated` LIMIT 0, 100";

	//echo $query;
	$list = DB::asArray($query);

	$cur_coin = '';
	foreach ($list as $item) {
		if ($cur_coin != $item['symbol']) {
			if ($cur_coin) outCoint($stat);
			$cur_coin = $item['symbol'];
			$stat = array_merge($item, ['volumes'=>[], 'prices'=>[]]);
		} else {
			$stat['volumes'][] = $item['24h_volume_usd'];
			$stat['prices'][] = $item['price_usd'];
		}
	}

	function outCoint($coin) {
		if ($vols = $coin['volumes']) {
		$vi = count($vols) - 1;
?>
	<tr>
		<td><?=$coin['symbol']?></td>
		<td><?=$vols[$vi]?> <?=round(($vols[0] - $vols[$vi])/max($vols) * 100)?>%</td>
		<td><?=implode(', ', $coin['prices'])?></td>
		<td><?=$coin['percent_change_1h']?></td>
	</tr>
<?		
		}
	}
?>
</table>