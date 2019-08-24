<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>menu</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php
$tz = date_default_timezone_get();
?><!-- <?php printf("TZ=[%s]",$tz); ?>  --><?php  
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
?>
<form id="form1" name="form1" method="post" action="">
		<table width="18%">
				<tr>
						<td width="5%" class="th-edit">H.P.France Sales Report for <?php printf("%s",$whoami['nickname']); ?></td>
<?php
	if($whoami['shop']){
?>
						<td width="2%" class="td-edit"><a href="daily.php" target="mainFrame">日報入力</a></td>
						<?php
	}
?>
						<td width="6%" class="td-edit"><a href="dailyreport.php" target="mainFrame">レポート(日報)</a></td>
						<td width="6%" class="td-edit"><a href="termreport.php" target="mainFrame">レポート(期間集計)</a></td>
<?php
	if($whoami['perm']&PERM_MASTER_EDIT){
?>
						<td width="6%" class="th-edit">マスター保守</td>
						<td width="6%" class="td-edit">
								<label>
								<script type="text/javascript">
function openMaster(master)
{
	var file = master.value;
	if(file){
		var target = window.parent['frames']['mainFrame'];
		target.location = file;
		target.focus();
	}
}
								</script>
								<select name="master" id="master" onchange="openMaster(this)">
										<option value="">-- select --</option>
<?php
	$master = array(
		array('name'=>'イベント','file'=>'ev-v2.php'),
		array('name'=>'ショップ','file'=>'shop.php'),
		array('name'=>'事業部','file'=>'division.php'),
		array('name'=>'エリア','file'=>'area.php'),
		array('name'=>'テナント','file'=>'tenant.php'),
		array('name'=>'スタッフ','file'=>'staff.php'),
		array('name'=>'ブランド','file'=>'brand.php'),
		array('name'=>'Maker/Shipper','file'=>'maker.php'),
		array('name'=>'カテゴリー','file'=>'category.php'),
		array('name'=>'国','file'=>'nation.php'),
		array('name'=>'地域','file'=>'narea.php'),
		array('name'=>'通貨','file'=>'currency.php'),
		array('name'=>'シーズン','file'=>'season.php'),
		array('name'=>'展示会(HPF主催)','file'=>'exhibition.php'),
	);
	for($a=0,$b=count($master); $b--; $a++){
?>
										<option value="<?php printf("%s",$master[$a]['file']); ?>"><?php printf("%s",$master[$a]['name']); ?></option>
<?php
	}
?>
								</select>
								<input onclick="openMaster(this.form.elements['master'])" type="button" name="edit" id="edit" value="再表示" />
						</label></td>
						<td width="2%" class="td-edit"><a href="alog.php" target="mainFrame">alog</a></td>
						<td width="2%" class="td-edit"><a href="rlog.php" target="mainFrame">rlog</a></td>
						<?php
	}
?>
<?php
	if($whoami['perm']&PERM_DAILY_EDIT){
?>
						<td width="2%" class="td-edit"><a href="dedit.php" target="mainFrame">売上データの編集</a></td>
<?php
	}
?>
<?php
	if($whoami['perm']&PERM_ORDER_EDIT){
?>
						<td width="2%" class="td-edit"><a href="purchase.php" target="mainFrame">発注データの編集</a></td>
<?php
	}
?>
<?php
	if($whoami['perm']&PERM_VIEW_PAYPLAN){
?>
						<td width="2%" class="td-edit"><a href="payplan.php" target="mainFrame">支払予定</a></td>
<?php
	}
?>
				</tr>
		</table>
</form>
<?php
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</body>
</html>
