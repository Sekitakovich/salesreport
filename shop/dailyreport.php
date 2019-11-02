<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>report - daily</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<?php
$debug = true;
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  $whoami = getStaffInfo($handle); //var_dump($whoami);
	pg_query($handle,"begin");
	pg_query($handle,"LOCK daily IN EXCLUSIVE MODE");
	
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	default:
/*
	このモードでは今日が昨日なのである
*/
	$query = sprintf("SELECT cast(now()+'-1 day' as date) as today");
	$qr = pg_query($handle,$query);
	$qo = pg_fetch_array($qr);
	$today = $qo['today'];
	$ta = explode("-",$today); // array

	function sortFunc00($a,$b)
	{
		if($a['dw']==$b['dw']){
			return($b['aw']-$a['aw']);
		}
		else{
			return($b['dw']-$a['dw']);
		}
	}
	function sortFuncaw($a,$b)
	{
		if($a['aw']==$b['aw']){
			return($b['dw']-$a['dw']);
		}
		else{
			return($b['aw']-$a['aw']);
		}
	}
	function sortFunc01($a,$b)
	{
		return($b['result']-$a['result']);
	}
	function sortFunc02($a,$b)
	{
		return($b['rsum']-$a['rsum']);
	}
	function sortFunc03($a,$b)
	{
		if($b['win']==$a['win']){
			return(0);
		}
		else{
			return($b['win']>$a['win']? 1:-1);
		}
	}
	function sortFunc04($a,$b)
	{
		if($b['ppy']==$a['ppy']){
			return(0);
		}
		else{
			return($b['ppy']>$a['ppy']? 1:-1);
		}
	}
	function sortFunc05($a,$b)
	{
		return($b['mvalue']-$a['mvalue']);
	}
	function sortFunc06($a,$b)
	{
		return($b['target']-$a['target']);
	}
	function sortFunc07($a,$b)
	{
		return($b['cavg']-$a['cavg']);
	}
	function sortFunc08($a,$b)
	{
		return(strtotime($b['odate'])-strtotime($a['odate']));
	}
//
	function sortFuncMember($a,$b)
	{
		return($b['member']-$a['member']);
	}
	function sortFuncMsum($a,$b)
	{
		return($b['msum']-$a['msum']);
	}
	function sortFuncMlot($a,$b)
	{
		return($b['mlot']-$a['mlot']);
	}
	function sortFuncMyen($a,$b)
	{
		return($b['myen']-$a['myen']);
	}
	function sortFuncMlsum($a,$b)
	{
		return($b['mlsum']-$a['mlsum']);
	}
	function sortFuncMysum($a,$b)
	{
		return($b['mysum']-$a['mysum']);
	}
//
	function sortFuncT($a,$b)
	{
		if($b['tw'] != $a['tw']){
			return($b['tw']-$a['tw']);
		}
		else{
			return(strcmp($a['name'],$b['name']));
		}
	}
//
	$__oList = array(
		array('name'=>'売上','func'=>'sortFunc01'),
		array('name'=>'売上累計','func'=>'sortFunc02'),
		array('name'=>'達成率','func'=>'sortFunc03'),
		array('name'=>'昨対比','func'=>'sortFunc04'),
		array('name'=>'月予算','func'=>'sortFunc05'),
		array('name'=>'日割予算','func'=>'sortFunc06'),
		array('name'=>'客単価','func'=>'sortFunc07'),
		array('name'=>'顧客カード','func'=>'sortFuncMember'),
		array('name'=>'顧客カード累計','func'=>'sortFuncMsum'),
		array('name'=>'顧客買上数','func'=>'sortFuncMlot'),
		array('name'=>'顧客買上数累計','func'=>'sortFuncMlsum'),
		array('name'=>'顧客買上金額','func'=>'sortFuncMyen'),
		array('name'=>'顧客買上金額累計','func'=>'sortFuncMysum'),
		array('name'=>'開店時期','func'=>'sortFunc08'),
		array('name'=>'事業部','func'=>'sortFunc00'),
		array('name'=>'エリア','func'=>'sortFuncaw'),
		array('name'=>'テナント','func'=>'sortFuncT'),
	);
//
//
		$query = sprintf("SELECT max(date_part('year',age(ps))) from shop where vf=true");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		
		$query = sprintf("select min(yyyymmdd),max(yyyymmdd) from daily");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$__dMin = explode("-",$qo['min']); $__dMin = $__dMin[0];
		$__dMax = explode("-",$qo['max']); $__dMax = $__dMax[0];

		if($whoami['perm']&PERM_REPORT_FULL){
			$query = sprintf("select id from division where vf=true order by weight desc");
			$qr = pg_query($handle,$query);
			$qs = pg_num_rows($qr);
			$dset = array();
			$dopt = $qs;
			for($a=0; $a<$qs; $a++){
				$qo = pg_fetch_array($qr,$a);
				$dset[] = $qo['id'];
			}
			$query = sprintf("select id from area where vf=true order by weight desc");
			$qr = pg_query($handle,$query);
			$qs = pg_num_rows($qr);
			$aset = array();
			$aopt = $qs;
			for($a=0; $a<$qs; $a++){
				$qo = pg_fetch_array($qr,$a);
				$aset[] = $qo['id'];
			}
//
			$query = sprintf("select id from tenant where vf=true order by weight desc");
			$qr = pg_query($handle,$query);
			$qs = pg_num_rows($qr);
			$tset = array();
			$topt = $qs;
			for($a=0; $a<$qs; $a++){
				$qo = pg_fetch_array($qr,$a);
				$tset[] = $qo['id'];
			}
//
		}
		else{
			$dset = getPGSQLarray($whoami['dset']); $dopt = count($dset);
			$aset = getPGSQLarray($whoami['aset']); $aopt = count($aset);
			$tset = getPGSQLarray($whoami['tset']); $topt = count($tset);
		}
		if(isset($_REQUEST['exec'])){
			$ps = $_REQUEST['ps'];
			$order = $_REQUEST['order'];
			$__dset = isset($_REQUEST['dset'])? $_REQUEST['dset']:array();
			$__aset = isset($_REQUEST['aset'])? $_REQUEST['aset']:array();
			$__tset = isset($_REQUEST['tset'])? $_REQUEST['tset']:array();
			$division = $_REQUEST['division'];
			$area = $_REQUEST['area'];
			$desc = $_REQUEST['desc'];
			$fromOpen = $_REQUEST['fromOpen'];
			$tenant = $_REQUEST['tenant'];
		}
		else{
			$ps = $ta;
			$order = 0; // 内田要望
			$__dset = ($whoami['dcheck']=='t')? $dset:array();
			$__aset = ($whoami['acheck']=='t')? $aset:array();
			$division = 0;
			$area = 0;
			$desc = 't';
			$fromOpen =1;
			$__tset = ($whoami['tcheck']=='t')? $tset:array();
			$tenant = 0;
		}
		$__psS = implode("-",$ps);
?>
<p class="title1">レポート(日報)
		
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	var a;
	var b;
	var elm;

	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else{
		return true;
	}
}
		</script>
		<script language="JavaScript" type="text/javascript">
function cbAlter(which)
{
	var a;
	var b;
	var elm = document.getElementsByTagName('INPUT');
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute('elmtype')==which){
			elm[a].checked = elm[a].checked? false:true;
		}
	}
	document.menu.submit();
}
		</script>
</p>
<form action="<?php printf("%s",$_SERVER['PHP_SELF']); ?>" method="get" enctype="application/x-www-form-urlencoded" name="menu" target="_self" id="menu" onsubmit="return checkTheForm(this)">
		<table width="43%">

				<tr>
						<td width="5%" class="th-edit">対象日</td>
						<td width="24%" class="td-edit"><label>
						<select name="ps[0]" id="ps[0]" onchange="leapAdjust(this.form,'ps')">
								<?php
for($a=$__dMin; $a<=$__dMax; $a++){
	$selected=sprintf("%s",$a==$ps[0]? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
								<?php
}
?>
						</select></label><label>
年
<select name="ps[1]" id="ps[1]" onchange="leapAdjust(this.form,'ps')">
		<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$ps[1]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label><label>
月
<select name="ps[2]" id="ps[2]">
		<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$ps[2]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label>
日
<label>
<input name="exec" type="hidden" id="exec" value="go" />
<input type="submit" name="show" id="show" value="更新" />
</label></td>
						<td width="24%" class="th-edit">&nbsp;</td>
						<td width="24%" class="td-edit">&nbsp;</td>
				</tr>
<?php
		if($whoami['perm']&PERM_REPORT_FULL){
?>
				<tr elmtype="full">
						<td class="th-edit">事業部</td>
						<td class="td-edit"><label>
<select name="division" id="division" onchange="this.form.submit()">
		<option value="0">-- 全て --</option>
		<?php
		$query = sprintf("select * from division where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$division? $__XHTMLselected:"");
			$dName=getDivisionName($handle,$qo['id']);
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
		<?php
		}
?>
</select>						
以下</label></td>
						<td class="th-edit">エリア</td>
						<td class="td-edit"><select name="area" id="area" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$query = sprintf("select * from area where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$area? $__XHTMLselected:"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
								<?php
		}
?>
						</select>
						以下</td>
				</tr>
				<tr elmtype="full">
						<td class="th-edit">テナント</td>
						<td class="td-edit"><select name="tenant" id="tenant" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$query = sprintf("select * from tenant where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$tenant? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit">&nbsp;</td>
				</tr>
<?php
	}
	else{
?>
<?php
	if($dopt){
?>
				<tr elmtype="adsets">
						<td class="th-edit">事業部</td>
						<td class="td-edit"><?php
		for($a=0,$b=count($dset); $b--; $a++){
			$checked = sprintf("%s",in_array($dset[$a],$__dset)? $__XHTMLchecked:"");
			$dName=getDivisionName($handle,$dset[$a]);
?>
										<label>
										<input elmtype="dset" name="dset[]" type="checkbox" id="dset[]" onclick="this.form.submit()" value="<?php printf("%d",$dset[$a]); ?>" <?php printf("%s",$checked); ?> />
										<?php printf("%s",$dName); ?> 以下</label>
										<br />
										<?php
		}
?></td>
						<td class="th-edit">選択</td>
						<td class="td-edit"><label>
								<input type="button" name="reverse" onclick="cbAlter('dset')" id="reverse" value="反転" />
						</label></td>
				</tr>
<?php
	}
?>
<?php
	if($aopt){
?>
				<tr elmtype="adsets">
						<td class="th-edit">エリア</td>
						<td class="td-edit"><?php
		for($a=0,$b=count($aset); $b--; $a++){
			$checked = sprintf("%s",in_array($aset[$a],$__aset)? $__XHTMLchecked:"");
			$dName=getAreaName($handle,$aset[$a]);
?>
										<label>
										<input elmtype="aset" name="aset[]" type="checkbox" id="aset[]" onclick="this.form.submit()" value="<?php printf("%d",$aset[$a]); ?>" <?php printf("%s",$checked); ?> />
										<?php printf("%s",$dName); ?> 以下</label>
										<br />
										<?php
		}
?></td>
						<td class="th-edit">選択</td>
						<td class="td-edit"><input type="button" name="reverse" onclick="cbAlter('aset')" id="reverse" value="反転" /></td>
				</tr>
<?php
	}
?>
<?php
	if($topt){
?>
				<tr elmtype="adsets">
						<td class="th-edit">テナント</td>
						<td class="td-edit"><?php
		for($a=0,$b=count($tset); $b--; $a++){
			$checked = sprintf("%s",in_array($tset[$a],$__tset)? $__XHTMLchecked:"");
			$query = sprintf("select * from tenant where id='%d'",$tset[$a]);
			$tr = pg_query($handle,$query);
			$to = pg_fetch_array($tr);
			$tName = $to['name'];
?>
										<label>
										<input elmtype="tset" name="tset[]" type="checkbox" id="tset[]" onclick="this.form.submit()" value="<?php printf("%d",$tset[$a]); ?>" <?php printf("%s",$checked); ?> />
										<?php printf("%s",$tName); ?></label>
										<br />
										<?php
		}
?></td>
						<td class="th-edit">選択</td>
						<td class="td-edit"><input type="button" name="reverse" onclick="cbAlter('tset')" id="reverse" value="反転" /></td>
				</tr>
<?php
	}
?>
<?php
	}
?>
				<tr>
						<td class="th-edit">オプション</td>
						<td class="td-edit"><label>比較表作成の際の閾年数
								<select name="fromOpen" id="fromOpen" onchange="this.form.submit()">
<?php
	for($a=1; $a<=10; $a++){
		$selected = sprintf("%s",$a==$fromOpen? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
<?php
	}
?>
								</select>
						年</label></td>
						<td class="th-edit">並べ替え</td>
						<td class="td-edit"><select name="order" id="order" onchange="this.form.submit()">
								<?php
	for($a=0,$b=count($__oList); $b--; $a++){
		$selected = sprintf("%s",$a==$order? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$__oList[$a]['name']); ?></option>
								<?php
	}
?>
						</select>
								<label>
								<input name="desc" type="radio" id="desc" onclick="this.form.submit()" value="t" <?php printf("%s",$desc=='t'? "checked":"");?> />
								降順</label>
								<label>
								<input name="desc" type="radio" id="desc" onclick="this.form.submit()" value="f" <?php printf("%s",$desc=='f'? "checked":"");?> />
						昇順</label></td>
				</tr>
		</table>
</form>
<?php
		$ut = strtotime(implode("-",$ps)); // 対象日のunix time
		$mdE = date("t",$ut); // 最終日を得る
		$dow = date("w",$ut); // 曜日

		$data = array();
		$ds = 0;

		$qq = array();
		$qq[] = sprintf("select shop.*,division.weight as dw,area.weight as aw,tenant.weight as tw,division.id as did,area.id as aid,date_part('year',age('%s',shop.ps)) as ys",$__psS);
		$qq[] = sprintf("from shop join division on shop.division=division.id join area on shop.area=area.id join tenant on shop.tenant=tenant.id");
		$qq[] = sprintf("where shop.vf=true");
//
		if($whoami['perm']&PERM_REPORT_FULL){
			if($division){
				$tree = divisionTree($handle,$division); $tree[]=$division;
				$qq[] = sprintf("and division.id in (%s)",implode(",",$tree));
			}
			if($area){
				$tree = areaTree($handle,$area); $tree[]=$area;
				$qq[] = sprintf("and area.id in (%s)",implode(",",$tree));
			}
			if($tenant){
				$qq[] = sprintf("and shop.tenant='%d'",$tenant);
			}
		}
		else{
			$ppp = array();
			if(count($__dset)){
				$tree = array();
				for($a=0,$b=count($__dset); $b--; $a++){
					$kkk = divisionTree($handle,$__dset[$a]);
					$kkk[] = $__dset[$a];
					$tree=array_merge($tree,$kkk);
				}
				$tree = array_unique($tree); // 重複する要素を排除
				$ppp[] = sprintf("division.id in (%s)",implode(",",$tree));
			}
	//
			if(count($__aset)){
				$tree = array();
				for($a=0,$b=$aopt; $b--; $a++){
					if($val=$__aset[$a]){
						$kkk = areaTree($handle,$val);
						$kkk[] = $val;
						$tree=array_merge($tree,$kkk);
					}
				}
				$tree = array_unique($tree); // 重複する要素を排除
				$ppp[] = sprintf("area.id in (%s)",implode(",",$tree));
			}
			if(count($__tset)){
				$ppp[] = sprintf("tenant.id in (%s)",implode(",",$__tset));
			}
			if(count($ppp)){
				$qq[] = sprintf("and (%s)",implode(" or ",$ppp));
			}
			else $qq[] = sprintf("and shop.id=0"); // 選択肢がない場合の苦肉の策(まずいよこれ)
		}
//
		$qq[] = sprintf("and (('%s' between shop.ps and shop.pe) or (shop.ps=shop.pe and '%s'>=shop.ps))",$__psS,$__psS);
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php if($debug) printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
		$ds = $qs;
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$id = $qo['id'];
			$name = $qo['name'];
			$did = $qo['did'];
			$aid = $qo['aid'];
			$dw = $qo['dw'];
			$aw = $qo['aw'];
			$tw = $qo['tw'];
			$ys = $qo['ys']; // 開店日からの通算年
			$oDate = $qo['ps']; // 開店日
			$cDate = $qo['pe']; // 閉店日
			$dname=getDivisionName($handle,$did);
			$aname=getAreaName($handle,$aid);
// 月次売上目標
			$query = sprintf("select sum(target) as mvalue,sum(last) as lvalue from daily where vf=true and shop='%d' and yyyymmdd between '%d-%d-%d' and '%d-%d-%d'",$id,$ps[0],$ps[1],1,$ps[0],$ps[1],$mdE);
			$vr = pg_query($handle,$query);
?><!-- <?php if($debug) printf("Query(%d) = [%s]",$vr,$query); ?> --><?php
			$vo = pg_fetch_array($vr);
			$mvalue = $vo['mvalue'];
			$lvalue = $vo['lvalue'];
// この日の売上、昨年のそれ、目標
			$query = sprintf("select * from daily where vf=true and shop='%d' and yyyymmdd='%d-%d-%d'",$id,$ps[0],$ps[1],$ps[2]);
			$vr = pg_query($handle,$query);
?><!-- <?php if($debug) printf("Query(%d) = [%s]",$vr,$query); ?> --><?php
			$vo = pg_fetch_array($vr);
			$result = $vo['result'];
			$target = $vo['target'];
			$last = $vo['last'];
			$book = $vo['book'];
			$booktotal = $vo['booktotal']; // 2019-10-30
			$apay = $vo['apay']; // 2019-10-30
			$atotal = $vo['atotal']; // 2019-10-30
			$member = $vo['member'];
			$visitor = $vo['visitor']; $welcome = $vo['welcome']; // 2018-04-01
			$comment = $vo['note'];
			$open = $vo['open'];
			$entered = $vo['entered']; // 入力されていたらtrue
			$etime = $vo['etime']; // 入力日時
			
			$mlot = $vo['mlot'];
			$myen = $vo['myen'];
			
// この日までの月間売上累計
			$query = sprintf("select sum(last) as lsum,sum(result) as rsum,sum(target) as tsum,sum(book) as bsum,sum(visitor) as vsum,sum(welcome) as wsum,sum(member) as msum,sum(mlot) as mlsum,sum(myen) as mysum from daily where vf=true and shop='%d' and yyyymmdd between '%d-%d-%d' and '%d-%d-%d'",$id,$ps[0],$ps[1],1,$ps[0],$ps[1],$ps[2]);
			$vr = pg_query($handle,$query);
?><!-- <?php if($debug) printf("Query(%d) = [%s]",$vr,$query); ?> --><?php
			$vo = pg_fetch_array($vr);
			$rSum = $vo['rsum'];
			$lSum = $vo['lsum'];
			$tSum = $vo['tsum'];
			$bSum = $vo['bsum'];
			$vSum = $vo['vsum']; $wSum = $vo['wsum']; // 2018-04-01
			$mSum = $vo['msum'];

			$mlSum = $vo['mlsum'];
			$mySum = $vo['mysum'];

			$dwin = $target? ((float)$result/(float)$target)*100.0:0; // 日毎の達成率
//			$win = $mvalue? ((float)$rSum/(float)$mvalue)*100.0:0; // 達成率の分母を月間目標額に変更(8/20)
			$win = $tSum? ((float)$rSum/(float)$tSum)*100.0:0; // 達成率の分母をやっぱ目標金額の累積に変更(8/20)
			$ppy = $lSum? ((float)$rSum/(float)$lSum)*100.0:0;
//			$ppy = $lvalue? ((float)$rSum/(float)$lvalue)*100.0:0; // 同上
			$cavg = $vSum? (int)($rSum/$vSum):0;
// イベント期間中か
			$query = sprintf("select * from event where vf=true and '%d'=any(shop) and '%d-%d-%d' between ps and pe",$id,$ps[0],$ps[1],$ps[2]);
			$er = pg_query($handle,$query);
?><!-- <?php if($debug) printf("Query(%d) = [%s]",$er,$query); ?> --><?php
			$es = pg_num_rows($er);
			if($es){
				$ooo = array();
				for($aa=0; $aa<$es; $aa++){
					$eo = pg_fetch_array($er,$aa);
					if($eo['ps']==$eo['pe']){
						$text = $eo['name'];
					}
					else{
						$text = sprintf("%s (%s～%s)",$eo['name'],dt2JPmd($eo['ps']),dt2JPmd($eo['pe']));
					}
					$ooo[] = $text;
				}
				$event = implode("\n",$ooo);
			}
			else{
				$event = "";
			}
//
			$data[] = array(
				'id'=>$id,'name'=>$name,
				'dw'=>$dw,'did'=>$did,'sid'=>$id,
				'aw'=>$aw,'tw'=>$tw,'aid'=>$aid,
				'mvalue'=>$mvalue,
				'result'=>$result,'target'=>$target,'last'=>$last,'book'=>$book,'member'=>$member,'visitor'=>$visitor,
				'rsum'=>$rSum,'tsum'=>$tSum,'bsum'=>$bSum,'vsum'=>$vSum,'msum'=>$mSum,'lsum'=>$lSum,
				'mlot'=>$mlot,'myen'=>$myen,'mlsum'=>$mlSum,'mysum'=>$mySum,
				'win'=>$win,'ppy'=>$ppy,'dwin'=>$dwin,
				'dname'=>$dname,'aname'=>$aname,
				'cavg'=>$cavg,'ys'=>$ys,'odate'=>$oDate,
				'comment'=>$comment,'event'=>$event,'open'=>$open,'entered'=>$entered,'etime'=>$etime,
				'welcome'=>$welcome,'wsum'=>$wSum,
				'booktotal'=>$booktotal, // 2019-10-30
				'apay'=>$apay, // 2019-10-30
				'atotal'=>$atotal, // 2019-10-30
			);
		}
//Var_dump::display($data);
		usort($data,$__oList[$order]['func']);
		if($desc=='f'){
			$data=array_reverse($data);
		}
// 閲覧履歴を残すのだーっ!
	if($whoami['alog']=='t'){
		pg_query($handle,"LOCK reportlog IN EXCLUSIVE MODE");
		$ua = pg_escape_string($_SERVER['HTTP_USER_AGENT']);
		$ip = pg_escape_string($_SERVER['REMOTE_ADDR']);
		$query = sprintf("select max(id) from reportlog");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$query = sprintf("insert into reportlog(id,staff,ua,ip,rtype) values('%d','%d','%s','%s','D')",$qo['max']+1,$whoami['id'],$ua,$ip);
		$qr = pg_query($handle,$query);
	}
?>
<form action="" method="get" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
<p class="title1">
		一覧表示 <?php printf("%s",dt2JP(implode("-",$ps))); ?> : <?php printf("%s",number_format($qs)); ?>店
		<!-- <?php if($debug) printf("Query(%d) = [%s]",$qr,$query); ?> -->
		<label></label>
	<input name="ps" type="hidden" id="ps" value="<?php printf("%s",$__ps); ?>" />
	<input name="mode" type="hidden" id="mode" value="shop" />
	<?php
if($whoami['perm']&PERM_EXPORT_CSV){
?>
	<label>
	<input name="export" type="button" id="export" onclick="MM_goToURL('parent','csvout.php');return document.MM_returnValue" value="CSV形式での出力" />
	</label>
	<?php
}
?>
</p>
		<table width="17%">
				<tr>
						<td width="2%" height="18" class="th-edit">No</td>
						<td width="2%" class="th-edit">店舗 
								<label></label>
								<label></label></td>
						<td width="1%" class="th-edit">月予算</td>
						<td width="1%" class="th-edit">日割予算</td>
						<td width="1%" class="th-edit">売上</td>
						<td width="1%" class="th-edit">売上累計</td>
						<td width="1%" class="th-edit">達成率</td>
						<td width="1%" class="th-edit">昨対比</td>
						<td width="95%" class="th-edit">取りおき</td>
						<td width="95%" class="th-edit">取りおき計</td>
						<td width="1%" class="th-edit">前受金</td>
						<td width="1%" class="th-edit">前受計</td>
						<td width="1%" class="th-edit">接客回数</td>
						<td width="1%" class="th-edit">接客累計</td>
						<td width="1%" class="th-edit">客数</td>
						<td width="1%" class="th-edit">客数<br />
						累計</td>
						<td width="1%" class="th-edit">客単価</td>
						<td width="1%" class="th-edit">顧客カード</td>
						<td width="1%" class="th-edit">顧客カード<br />
						累計</td>
						<td width="1%" class="th-edit">顧客買上数</td>
						<td width="1%" class="th-edit">顧客買上数<br />
						累計</td>
						<td width="1%" class="th-edit">顧客買上金額</td>
						<td width="1%" class="th-edit">顧客買上金額<br />
						累計</td>
						<td width="1%" class="th-edit">店舗</td>
				</tr>
<?php
	$rSum = 0;
	$tSum = 0;
	$lSum = 0;
	$bSum = 0;
	$mSum = 0;
	$vSum = 0; $wSum = 0; // 2018-04-01
	$cSum = 0;
	$mvSum = 0;
	$rrSum = 0;
	$ttSum = 0;
	$llSum = 0;
	$bbSum = 0;
	$vvSum = 0; $wwSum = 0; // 2018-04-01
	$mmSum = 0;
	
	$___mlSum = 0;
	$___mySum = 0;

	$_mlsum = 0;
	$_mysum = 0;
	
	$___mlsum = 0;
	$___mysum = 0;
	
	
	$orSum = 0; // 昨対比の対象
	$olSum = 0; // 昨対比の対象
			
	$csvMIN = array(); // for export
	$csvMIN[] = "店舗名,月予算,日割予算,売上,売上累計,達成率,昨対比,取りおき,取りおき計,顧客カード,顧客カード累計,接客回数,接客回数累計,客数,客数累計,客単価,顧客買上数,顧客買上数累計,顧客買上金額,顧客買上金額累計";
	
	for($a=0; $a<$ds; $a++){
		$qo = $data[$a];
		$sid = $qo['sid']; // shop.id
		$etime = $qo['etime'];
		$comment = trim(mb_convert_kana($qo['comment'],"as"));
		$event = $qo['event'];
		$open = $qo['open'];
		$ys = $qo['ys'];
		$oDate = $qo['odate'];
		$dName = $qo['dname'];
		$aName = $qo['aname'];
		$mvalue = $qo['mvalue'];
		$result = $qo['result'];
		$target = $qo['target'];
		$last = $qo['last'];
		$book = $qo['book'];
		$booktotal = $qo['booktotal']; // 2019-10-30
		$apay = $qo['apay']; // 2019-10-30
		$atotal = $qo['atotal']; // 2019-10-30
		$rSum += $result;
		$tSum += $target;
		$lSum += $last;
		$bSum += $book;
		$member = $qo['member'];
		
		$mlot= $qo['mlot']; // 買上数(個別)
		$myen= $qo['myen']; // 買上￥(個別)

		$___mlSum += $mlot;
		$___mySum += $myen;

		$welcome = $qo['welcome'];
		$visitor = $qo['visitor'];
		$cavg = $qo['cavg'];
		$win = $qo['win'];		$dwin = $qo['dwin'];
		$ppy = $qo['ppy'];
		$mSum += $member;
		$wSum += $welcome;
		$vSum += $visitor;
		$entered = $qo['entered'];

		$__tSum = $qo['tsum'];
		$__lSum = $qo['lsum'];
		$__rSum = $qo['rsum'];
		$__bSum = $qo['bsum'];
		$__vSum = $qo['vsum']; $__wSum = $qo['wsum']; // 2018-04-01
		$__mSum = $qo['msum'];

		$__mlSum = $qo['mlsum']; // 買上数累計(個別)
		$__mySum = $qo['mysum']; // 買上￥累計(個別)

		$mvSum += $mvalue;
		$rrSum += $__rSum;
		$ttSum += $__tSum;
		$llSum += $__lSum;
		$bbSum += $__bSum;
		$vvSum += $__vSum; $wwSum += $__wSum; // 2018-04-01
		$mmSum += $__mSum;
		
		$_mlsum += $__mlSum; // 買上数累計(計)
		$_mysum += $__mySum; // 買上￥累計(計)

		if($ys>=$fromOpen){
			$orSum += $__rSum;
			$olSum += $__lSum;
		}

		$line = array($qo['name'],$mvalue,$target,$result,$__rSum,$win,$ppy,$book,$__bSum,$member,$__mSum,$welcome,$__wSum,$visitor,$__vSum,$cavg,$mlot,$__mlSum,$myen,$__mySum);
		$csvMIN[] = implode(",",$line);

//
?>
				<tr elmtype="shop" shop="<?php printf("%d",$sid); ?>" open="<?php printf("%s",$open); ?>" entered="<?php printf("%s",$entered); ?>" win="<?php printf("%d",$win); ?>" ys="<?php printf("%d",$ys); ?>">
						<td class="td-edit"><?php printf("%d",$a+1); ?>
						<!--<?php printf("dw=[%d] aw=[%d]",$qo['dw'],$qo['aw']); ?> --></td>
						<td class="td-edit">
						<label><span title="<?php printf("%s",$entered=='t'? sprintf("%s登録",ts2JP($etime)):""); ?>"><?php printf("%s",$qo['name']); ?></span> <img src="../images/stop.png" alt="休業日" width="16" height="16" border="0" class="notDisplay" elmtype="open" status="<?php printf("%s",$open); ?>" /> <img src="../images/wakaba3.gif" alt="<?php printf("%s ～",$oDate); ?>" width="10" height="14" class="notDisplay" title="<?php printf("%s ～",$oDate); ?>" elmtype="age" ys="<?php printf("%d",$ys); ?>" /> <img src="../images/Event.gif" alt="<?php printf("%s",$event); ?>" blink="on" width="16" height="16" border="0" class="notDisplay" title="<?php printf("%s",$event); ?>" elmtype="event" /> <img src="../images/comment.gif" alt="<?php printf("%s",$comment); ?>" blink="on" width="16" height="16" border="0" class="notDisplay" title="<?php printf("%s",$comment); ?>" elmtype="comment" /></label></td>
						<td class="td-editDigit" title="月予算"><?php printf("%s",number_format($mvalue,0)); ?></td>
						<td class="td-editDigit" title="日割予算"><?php printf("%s",number_format($target,0)); ?></td>
						<td class="td-editDigit" title="売上"><?php printf("%s",number_format($result,0)); ?></td>
						<td class="td-editDigit" title="売上累計"><?php printf("%s",number_format($__rSum,0)); ?></td>
						<td class="td-editDigit" title="達成率"><?php printf("%s",number_format($win,2)); ?>％</td>
						<td class="td-editDigit" title="昨対比"><?php printf("%s",number_format($ppy,2)); ?>％</td>
						<td class="td-editDigit" title="取りおき"><?php printf("%s",number_format($book,0)); ?></td>
						<td class="td-editDigit" title="取りおき計"><?php printf("%s",number_format($booktotal,0)); // 2019-10-30 ?></td>
						<td class="td-editDigit" title="前受金"><?php printf("%s",number_format($apay,0)); ?></td>
						<td class="td-editDigit" title="前受計"><?php printf("%s",number_format($atotal,0)); ?></td>
						<td class="td-editDigit" title="客数"><?php printf("%s",number_format($welcome,0)); ?></td>
						<td class="td-editDigit" title="客数"><?php printf("%s",number_format($__wSum,0)); ?></td>
						<td class="td-editDigit" title="客数"><?php printf("%s",number_format($visitor,0)); ?></td>
						<td class="td-editDigit" title="客数累計"><?php printf("%s",number_format($__vSum,0)); ?></td>
						<td class="td-editDigit" title="客単価"><?php printf("%s",number_format($cavg,0)); ?></td>
						<td class="td-editDigit" title="顧客カード"><?php printf("%s",number_format($member,0)); ?></td>
						<td class="td-editDigit" title="顧客カード累計"><?php printf("%s",number_format($__mSum,0)); ?></td>
						<td class="td-editDigit" title="顧客買上数"><?php printf("%s",number_format($mlot,0)); ?></td>
						<td class="td-editDigit" title="顧客買上数累計"><?php printf("%s",number_format($__mlSum,0)); ?></td>
						<td class="td-editDigit" title="顧客買上金額"><?php printf("%s",number_format($myen,0)); ?></td>
						<td class="td-editDigit" title="顧客買上金額累計"><?php printf("%s",number_format($__mySum,0)); ?></td>
						<td class="td-edit" title="店舗"><?php printf("%s",$qo['name']); ?></td>
				</tr>
<?php
	}
		$zSum = $ttSum? ((float)$rrSum/(float)$ttSum)*100.0:0;
		$pSum = $llSum? ((float)$rrSum/(float)$llSum)*100.0:0;
		$cSum = $vvSum? $rrSum/$vvSum:0;
		$opSum = $olSum? ((float)$orSum/(float)$olSum)*100.0:0;

		$line = array('',$mvSum,$tSum,$rSum,$rrSum,$zSum,$opSum,$bSum,$bbSum,$mSum,$mmSum,$wSum,$wwSum,$vSum,$vvSum,$cSum,$___mlSum,$_mlsum,$___mySum,$_mysum);
//		$line = array('',$mvSum,$tSum,$rSum,$rrSum,$zSum,$pSum,$bSum,$bbSum,$mSum,$mmSum,$vSum,$vvSum,$cSum,$___mlsum,$_mlsum,$___mysum,$_mysum);
		$csvMIN[] = implode(",",$line);
?>
				<tr>
						<td class="th-edit">計/平均</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($mvSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($tSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rrSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($zSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($opSum,2)); ?>％</td> <?php // なぜかopSumを出していた (2009-12-24) がこれで良いのだと(怒) ?>
						<td class="th-editDigit"><?php echo(""); // 2019-10-30 ?></td>
						<td class="th-editDigit"><?php echo(""); // 2019-10-30 ?></td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($wSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($wwSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vvSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($cSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($mSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($mmSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($___mlSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($_mlsum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($___mySum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($_mysum,0)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
				</tr>
		</table>
		<br />
		<table width="40%">
				<tr>
						<th width="5%" nowrap="nowrap" class="th-edit">比較表</th>
						<th width="7%" nowrap="nowrap" class="th-edit">当月予算計</th>
						<th width="9%" nowrap="nowrap" class="th-edit">当月売上累計</th>
						<th width="9%" nowrap="nowrap" class="th-edit">昨年売上累計</th>
						<th width="6%" nowrap="nowrap" class="th-edit">差額累計</th>
						<th width="7%" nowrap="nowrap" class="th-edit">当月達成率</th>
						<th width="57%" nowrap="nowrap" class="th-edit">昨年比率</th>
				</tr>
<?php
	$mmSum = array(0,0);
	$rrSum = array(0,0);
	$llSum = array(0,0);
	for($a=0; $a<$ds; $a++){
		$src = $data[$a];
		$dst = ($src['ys']<$fromOpen)? 1:0;
		$mmSum[$dst]+=$src['mvalue'];
		$rrSum[$dst]+=$src['rsum'];
		$llSum[$dst]+=$src['lsum'];
	}
	$wSum = array(0,0);
	$lSum = array(0,0);
	$wSum[0] = $mmSum[0]? ((float)$rrSum[0]/(float)$mmSum[0])*100.0:0;
	$lSum[0] = $llSum[0]? ((float)$rrSum[0]/(float)$llSum[0])*100.0:0;
?>
				<tr>
						<td nowrap="nowrap" class="td-edit">オープン<?php printf("%d",$fromOpen); ?>年以上</td>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($mmSum[0],0)); ?></td>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($rrSum[0],0)); ?></td>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($llSum[0],0)); ?></td>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($rrSum[0]-$llSum[0],0)); ?></td>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%3.2f",$wSum[0]); ?>％</td>
				<td nowrap="nowrap" class="td-editDigit"><?php printf("%3.2f",$lSum[0]); ?>％&nbsp;</td>				
				</tr>
				<tr>
						<td nowrap="nowrap" class="td-edit">オープン<?php printf("%d",$fromOpen); ?>年未満</td>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($mmSum[1],0)); ?></td>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($rrSum[1],0)); ?></td>
						<td nowrap="nowrap" class="td-editDigit">&nbsp;</td>
						<td nowrap="nowrap" class="td-editDigit">&nbsp;</td>
						<td nowrap="nowrap" class="td-editDigit">&nbsp;</td>
				<td nowrap="nowrap" class="td-editDigit">&nbsp;</td>				</tr>
		</table>
		<p>&nbsp;</p>
</form>
<script language="JavaScript" type="text/javascript">
window.onload = function()
{
	var perm_report_full = <?php printf("%d",$whoami['perm']&PERM_REPORT_FULL); ?>;
	var fromOpen = <?php printf("%d",$fromOpen); ?>;
	
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("elmtype")=='shop'){
			if(elm[a].getAttribute("win")<100){
				elm[a].className = 'notWin';
			}
			if(elm[a].getAttribute("shop")=='<?php printf("%d",$whoami['shop']); ?>'){
				elm[a].style.fontWeight = 'bold';
			}
			if(elm[a].getAttribute("open")=='t'){
			 	if(elm[a].getAttribute("entered")=='f'){
					elm[a].style.color = '#888888';
//					elm[a].style.textDecoration = 'line-through';
//					elm[a].style.fontWeight = 'bold';
				}
				else if(elm[a].getAttribute("ys")<fromOpen){
					elm[a].style.color = '#0000FF';
				}
			}
			else{
				elm[a].style.color = '#888888';
			}
		}
	}
	var elm = document.getElementsByTagName('IMG');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("elmtype")=='age'){
			if(elm[a].getAttribute("ys")<fromOpen){
				elm[a].className = 'setDisplay';
			}
		}
		if(elm[a].getAttribute("elmtype")=='event'){
			if(elm[a].getAttribute("alt")!=''){
				elm[a].className = 'setDisplay';
			}
		}
		if(elm[a].getAttribute("elmtype")=='comment'){
			if(elm[a].getAttribute("alt")!=''){
				elm[a].className = 'setDisplay';
			}
		}
		if(elm[a].getAttribute("elmtype")=='open'){
			if(elm[a].getAttribute("status")!='t'){
				elm[a].className = 'setDisplay';
			}
		}
	}
	startBlink(2);
	leapAdjust(document.menu,'ps');
}
</script>
<?php
		$_SESSION['csv'] = $csvMIN;
		break;
//--------------------------------------------------------------------
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</body>
</html>
