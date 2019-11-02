<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
	if($whoami['shop']){
//
		$query = sprintf("select shop.*,currency.rate,currency.name as cname from shop,currency where shop.id='%d' and shop.currency=currency.id",$whoami['shop']);
		$qr = pg_query($handle,$query);
		$thisShop = pg_fetch_array($qr); // 店舗情報
//	Var_Dump::display($thisShop);
		pg_query($handle,"begin");
		pg_query($handle,"LOCK daily IN EXCLUSIVE MODE");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php printf("HPF Sales Report system for %s",$thisShop['name']); ?></title>
<link href="shop.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
.style1 {font-size: large}
-->
</style>
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<?php
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "psave":
		$yy = $_REQUEST['yy'];
		$mm = $_REQUEST['mm'];
		$days = $_REQUEST['days'];
		$target = $_REQUEST['target'];
		$last = $_REQUEST['last'];
		$open = $_REQUEST['open'];

		for($a=0,$b=$days,$dd=1; $b--; $a++,$dd++){
			$query = sprintf("select id from daily where vf=true and shop='%d' and yyyymmdd='%d-%d-%d'",
				$thisShop['id'],
				$yy,$mm,$dd);
			$qr = pg_query($handle,$query);
			$qs = pg_num_rows($qr);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			if($qs){
				$qo = pg_fetch_array($qr);
				$id = $qo['id'];
			}
			else{
				$query = sprintf("select max(id) from daily");
				$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
				$qo = pg_fetch_array($qr);
				$id=$qo['max']+1;
				$query = sprintf("insert into daily(id,shop,yyyymmdd) values('%d','%d','%d-%d-%d')",$id,$thisShop['id'],$yy,$mm,$dd);
				$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			}
			$set = array();
			$set[] = sprintf("tbase='%f'",$target[$a]);
			$set[] = sprintf("lbase='%f'",$last[$a]);
			$set[] = sprintf("target='%d'",$target[$a]*$thisShop['rate']);
			$set[] = sprintf("last='%d'",$last[$a]*$thisShop['rate']);
			$set[] = sprintf("open=%s",isset($open[$a])? "false":"true");
			$set[] = sprintf("ua='%s'",pg_escape_string($_SERVER['HTTP_USER_AGENT']));
			$set[] = sprintf("ipaddress='%s'",$_SERVER['REMOTE_ADDR']);
			$set[] = sprintf("dtp='%s'",$thisShop['dtp']); // 2019-11-02
			$query = sprintf("update daily set %s where id='%d'",implode(",",$set),$id);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
?>
<a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "esave":
		$yyyymmdd = implode("-",$_REQUEST['yyyymmdd']);
		$query = sprintf("select id from daily where vf=true and shop='%d' and yyyymmdd='%s'",$thisShop['id'],$yyyymmdd);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php

		if($qs==0){
			$query = sprintf("select max(id) from daily");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into daily(id) values('%d')",$id);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		else{
			$qo = pg_fetch_array($qr);
			$id = $qo['id'];
		}
		$set = array();
		$set[] = sprintf("shop='%d'",$thisShop['id']);
		$set[] = sprintf("yyyymmdd='%s'",implode("-",$_REQUEST['yyyymmdd']));
		$set[] = sprintf("rbase='%f'",$_REQUEST['rbase']);
		$set[] = sprintf("bbase='%f'",$_REQUEST['bbase']);
		$set[] = sprintf("result='%d'",$_REQUEST['result']);
		$set[] = sprintf("book='%d'",$_REQUEST['book']);
		$set[] = sprintf("member='%d'",$_REQUEST['member']);
		$set[] = sprintf("welcome='%d'",$_REQUEST['welcome']);
		$set[] = sprintf("visitor='%d'",$_REQUEST['visitor']);
		$set[] = sprintf("note='%s'",pg_escape_string($_REQUEST['note']));
			$set[] = sprintf("ua='%s'",pg_escape_string($_SERVER['HTTP_USER_AGENT']));
			$set[] = sprintf("ipaddress='%s'",$_SERVER['REMOTE_ADDR']);
			$set[] = sprintf("dtp='%s'",$thisShop['dtp']); // 2019-11-02
		$set[] = sprintf("entered=true");
		$set[] = sprintf("etime=now()");
		$set[] = sprintf("myen='%d'",$_REQUEST['myen']);
		$set[] = sprintf("mlot='%d'",$_REQUEST['mlot']);
		$query = sprintf("update daily set %s where id='%d'",implode(",",$set),$id);
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
//--------------------------------------------------------------------
//--------------------------------------------------------------------
	default:
		if(isset($_REQUEST['next'])){ // 翌月
			$thisY = ($_REQUEST['thisM']==12)? $_REQUEST['thisY']+1:$_REQUEST['thisY'];
			$thisM = ($_REQUEST['thisM']==12)? 1:$_REQUEST['thisM']+1;
		}
		else if(isset($_REQUEST['prev'])){ // 前月
			$thisY = ($_REQUEST['thisM']==1)? $_REQUEST['thisY']-1:$_REQUEST['thisY'];
			$thisM = ($_REQUEST['thisM']==1)? 12:$_REQUEST['thisM']-1;
		}
		else{
			$thisY = $tt[0];
			$thisM = $tt[1];
		}
?>
<p class="title1">日報入力 : <?php printf("%s",$thisShop['name']); ?></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="enter" target="_self" id="enter" onsubmit="return checkTheForm(this)">
		<?php
		$yyyymmdd = $tt; // 基本は本日分の入力
?>
		<span class="title1">
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	var rbase = parseFloat(F.elements['rbase'].value);
	var bbase = parseFloat(F.elements['bbase'].value);
	var result = parseInt(F.elements['result'].value);
	var book = parseInt(F.elements['book'].value);
	var member = parseInt(F.elements['member'].value);
	var visitor = parseInt(F.elements['visitor'].value);
	var welcome = parseInt(F.elements['welcome'].value);
	var note = F.elements['note'].value;
	var rate = parseFloat(<?php printf("%f",$thisShop['rate']); ?>);

	var mlot = parseInt(F.elements['mlot'].value);
	var myen = parseInt(F.elements['myen'].value);

	if(isNaN(result)){
		mes[err++]= '売上に正しい値を入力してください';
	}
	if(isNaN(book)){
		mes[err++]= '取り置き発生金額に正しい値を入力してください';
	}
	if(isNaN(member)){
		mes[err++]= '新規顧客カード数に正しい値を入力してください';
	}
	if(isNaN(welcome)){
		mes[err++]= '接客回数に正しい値を入力してください';
	}
	if(isNaN(visitor)){
		mes[err++]= '買上客数に正しい値を入力してください';
	}
/*
	if(isNaN(mlot)){
		mes[err++]= '顧客買上数に正しい値を入力してください';
	}
	if(isNaN(myen)){
		mes[err++]= '顧客買上金額に正しい値を入力してください';
	}
*/
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else{
		var ask = new Array();
		var cname = '<?php printf("%s",$thisShop['cname']); ?>';
		var a=0;

		ask[a++] = sprintf("日付: %d年%d月%d日",F.elements['yyyymmdd[0]'].value,F.elements['yyyymmdd[1]'].value,F.elements['yyyymmdd[2]'].value);
		if(rate==1.0){
			ask[a++] = sprintf("売上: %d%s",result,cname);
			ask[a++] = sprintf("取り置き発生金額: %d%s",book,cname);
		}
		else{
			ask[a++] = sprintf("売上: %.3f%s",rbase,cname);
			ask[a++] = sprintf("取り置き発生金額: %.3f%s",bbase,cname);
		}
		ask[a++] = sprintf("新規顧客カード数: %d枚",member);
		ask[a++] = sprintf("買上客数: %d人",visitor);
		if(note){
			ask[a++] = sprintf("特記事項: [%s]",note);
		}
		ask[a++] = '';
		ask[a++] = "日報をこの内容で登録します。よろしいですか?";
		return confirm(ask.join('\n'));
	}
}
		</script>
		</span>
		<script language="JavaScript" type="text/javascript">
function setResult(F)
{
	var rate = <?php printf("%f",$thisShop['rate']); ?>;
	F.elements['result'].value = parseInt(F.elements['rbase'].value*rate);
}
function setBook(F)
{
	var rate = <?php printf("%f",$thisShop['rate']); ?>;
	F.elements['book'].value = parseInt(F.elements['bbase'].value*rate);
}
		</script>
		<table width="43%">
				<tr>
						<td width="7%" class="th-edit">日付</td>
						<td width="21%" class="td-edit"><select name="yyyymmdd[0]" id="yyyymmdd[0]" onchange="leapAdjust(this.form,'yyyymmdd')">
								<?php
for($a=$tt[0]-1; $a<=$tt[0]; $a++){
	$selected=sprintf("%s",$a==$yyyymmdd[0]? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
								<?php
}
?>
						</select>
								年
								<select name="yyyymmdd[1]" id="yyyymmdd[1]" onchange="leapAdjust(this.form,'yyyymmdd')">
										<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$yyyymmdd[1]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
										<?php
}
?>
								</select>
								月
								<select name="yyyymmdd[2]" id="yyyymmdd[2]">
										<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$yyyymmdd[2]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
										<?php
}
?>
								</select>
						日
						<input type="hidden" name="result" id="result" />
						<input type="hidden" name="book" id="book" /></td>
						<td width="4%" class="th-edit">接客回数</td>
						<td class="td-edit"><input name="welcome" type="text" class="input-Digit" id="welcome" size="3" maxlength="6" />
名</td>
				</tr>
				<tr>
						<td class="th-edit">売上</td>
						<td class="td-edit"><input name="rbase" type="text" class="input-Digit" id="rbase" onchange="setResult(this.form)" size="8" maxlength="8" />
(<?php printf("%s",$thisShop['cname']); ?>)</td>
						<td class="th-edit">取りおき発生金額</td>
						<td class="td-edit"><input name="bbase" type="text" class="input-Digit" id="bbase" onchange="setBook(this.form)" size="8" maxlength="8" />
(<?php printf("%s",$thisShop['cname']); ?>)</td>
				</tr>
				<tr>
						<td class="th-edit">新規顧客カード数</td>
						<td class="td-edit"><input name="member" type="text" class="input-Digit" id="member" size="3" maxlength="6" />
						枚</td>
						<td class="th-edit">買上客数</td>
						<td class="td-edit"><input name="visitor" type="text" class="input-Digit" id="visitor" size="3" maxlength="6" />
								名</td>
				</tr>
				<tr>
						<td class="th-edit">顧客買上数</td>
						<td class="td-edit"><input name="mlot" type="text" class="input-Digit" id="mlot" size="3" maxlength="6" /> 
								件</td>
						<td class="th-edit">顧客買上金額</td>
						<td class="td-edit"><input name="myen" type="text" class="input-Digit" id="myen" size="8" maxlength="12" />
						(<?php printf("%s",$thisShop['cname']); ?>)</td>
				</tr>
				<tr>
						<td class="th-edit">特記事項</td>
						<td colspan="3" class="td-edit"><textarea name="note" cols="48" rows="4" id="note"></textarea></td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td colspan="3" class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
						<input name="mode" type="hidden" id="mode" value="esave" /></td>
				</tr>
		</table>
</form>
<br />
<form action="" method="get" enctype="application/x-www-form-urlencoded" name="menu" target="_self" id="menu">
		<table width="27%">
				<tr>
						<td width="7%" class="th-edit"><span class="title1"><?php printf("%d年%d月",$thisY,$thisM); ?> 売上表</span></td>
						<td width="93%" class="td-edit"><label>
								<input type="submit" name="prev" id="prev" value="←前月" />
								</label>
										<label>
										<input type="submit" name="here" id="here" value="今月 (<?php printf("%d年%d月",$tt[0],$tt[1]); ?>)" />
								</label>
										<label>
												<input type="submit" name="next" id="next" value="翌月→" />
								</label>
										<label></label>						</td>
						<td width="93%" class="th-edit"><span class="style1" id="viewSum"></span></td>
				</tr>
		</table>
		<input name="thisY" type="hidden" id="thisY" value="<?php printf("%d",$thisY); ?>" />
		<input name="thisM" type="hidden" id="thisM" value="<?php printf("%d",$thisM); ?>" />
</form><br />
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list" onsubmit="return checkTheList(this)">
		<p>
				<script language="JavaScript" type="text/javascript">
function settSum(F)
{
	var days = parseInt(F.elements['days'].value);
	var a;
	var d;
	var tSum = 0;

	for(a=0,d=1; a<days; a++,d++){
		var dst = 'target['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 予算に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			tSum += val;
			F.elements['open['+a+']'].checked = (val? false:true);
		}
		var dst = 'last['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 昨年度の売上金額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
	}
//	F.elements['tSum'].value = number_format(tSum,0);
	F.elements['tSum'].value = tSum;
}
		</script>
				<script language="JavaScript" type="text/javascript">
function checkTheList(F)
{
	var mes = new Array();
	var err = 0;
	var yy = parseInt(F.elements['yy'].value);
	var mm = parseInt(F.elements['mm'].value);
	var tSum = parseInt(F.elements['tSum'].value);
	var days = parseInt(F.elements['days'].value);

	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else{
		var ask = new Array();
		var a=0;
		ask[a++] = sprintf("%d年%d月:予算 ￥%s",yy,mm,number_format(tSum,0));
//
		var b;
		var c;
		for(b=0,c=1; b<days; b++,c++){
			var tV = parseInt(F.elements['target['+b+']'].value);
			var lV = parseInt(F.elements['last['+b+']'].value);
			var isClose = F.elements['open['+b+']'].checked;
			if(isClose == false && (tV==0 || lV==0)){
				ask[a++] = sprintf("警告：%2d日 金額0の項目があります",c);
			}
		}
//
		ask[a++] = sprintf("");
		ask[a++] = sprintf("この内容で登録します。よろしいですか?");
		return confirm(ask.join('\n'));
	}
}
		</script>
		</p>
<span id="saveList">
		<p>*各営業日の「予算」に有効な値が入力されていないと「休」チェックは外せません</p>
</span>
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">日</td>
						<td class="th-editDigit" width="3%">曜</td>
						<td class="th-editDigit" width="3%">休</td>
						<td class="th-editDigit" width="3%">昨年</td>
						<td width="1%" class="th-editDigit">予算</td>
						<td width="95%" class="th-editDigit">売上</td>
						<td width="95%" class="th-editDigit">売上累計</td>
						<td width="95%" class="th-editDigit">達成率</td>
						<td width="1%" class="th-editDigit">昨対比</td>
						<td width="95%" class="th-editDigit">取りおき</td>
						<td width="95%" class="th-editDigit">顧客</td>
						<td width="95%" class="th-editDigit">接客</td>
						<td width="95%" class="th-editDigit">客数</td>
						<td width="95%" class="th-editDigit">客単価</td>
						<td width="95%" class="th-edit">顧買数</td>
						<td width="95%" class="th-edit">顧買金</td>
						<td width="95%" class="th-edit">特記事項</td>
				</tr>
<?php
	$days = date("t",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // この月が何日あるか
	$dow = date("w",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // 
	$tu = strtotime(date("Y-m-d"));
	$week = array('日','月','火','水','木','金','土');

	$tSum = 0;
	$rSum = 0;
	$bSum = 0;
	$mSum = 0;
	$vSum = 0;
	$wSum = 0;
	$lSum = 0;

	$mlotSum = 0;	
	$myenSum = 0;	

	$winN = 0; // 本日までの達成率
	
	$lostDay = array(); // 未入力の日

//
	$query = sprintf("select sum(target) as mvalue from daily where vf=true and shop='%d' and yyyymmdd between '%d-%d-1' and '%d-%d-%d'",
		$thisShop['id'],$thisY,$thisM,$thisY,$thisM,$days);
	$qr = pg_query($handle,$query);
	$qo = pg_fetch_array($qr);
	$mvalue = $qo['mvalue']; // 月間売上目標金額
//

	for($a=0,$d=1; $d<=$days; $a++,$d++,$dow++){
		$query = sprintf("select * from daily where vf=true and shop='%d' and 
		yyyymmdd='%d-%d-%d'",$thisShop['id'],$thisY,$thisM,$d);
		$qr = pg_query($handle,$query);
		?><!-- <?php echo($query); ?> --><?php
		if($qs = pg_num_rows($qr)){
			$qo = pg_fetch_array($qr);
			$target = $qo['target'];
			$result = $qo['result'];
			$book = $qo['book'];
			$last = $qo['last'];
//			$target = $qo['tbase'];
//			$result = $qo['rbase'];
//			$book = $qo['bbase'];
//			$last = $qo['lbase'];
			$member = $qo['member'];
			$welcome = $qo['welcome'];
			$visitor = $qo['visitor'];
			$note = $qo['note']? $qo['note']:'　';
			$open = $qo['open'];

			$mlot = $qo['mlot'];
			$myen = $qo['myen'];
		}
		else{
			$target = 0;
			$result = 0;
			$book = 0;
			$last = 0;
			$member = 0;
			$welcome = 0;
			$visitor = 0;
			$note = '　';
			$open = 'f';

			$mlot = 0;
			$myen = 0;
		}
		$cavg = $visitor? $result/$visitor:0;
//		$win = $target? ((float)$result/(float)$target)*100.0:0;
//		$ppy = $last? ((float)$result/(float)$last)*100.0:0;

		$tSum += $target;
		$rSum += $result;
		$bSum += $book;
		$mSum += $member;
		$wSum += $welcome;
		$vSum += $visitor;
		$lSum += $last;
		
		$mlotSum += $mlot;
		$myenSum += $myen;

//			$win = $mvalue? ((float)$rSum/(float)$mvalue)*100.0:0;
			$win = $tSum? ((float)$rSum/(float)$tSum)*100.0:0;
			$ppy = $lSum? ((float)$rSum/(float)$lSum)*100.0:0;

		$passed = strtotime(sprintf("%d-%d-%d",$thisY,$thisM,$d))<$tu? 1:0;
		if($passed){
			$winN = $win;
		}

//
		$query = sprintf("select * from holiday where vf=true and yyyymmdd='%d-%d-%d'",$thisY,$thisM,$d);
		$hr = pg_query($handle,$query);
		$hs = pg_num_rows($hr);
		if($hs){
			$ho = pg_fetch_array($hr);
		}
		$isHoliday = $hs;
		$hName = sprintf("%s",$hs? $ho['name']:"");
		$oc = $open=='f'? "checked=\"checked\"":"";

		$__lost = 0;
		if($passed){
			if($open == 't'){
				if($last!=0){
					if($result==0){
						$lostDay[] = $d; // 未入力の日
						$__lost = 1;
					}
				}
			}
		}
//
?>
				<tr passed="<?php printf("%d",$passed); ?>" lost="<?php printf("%d",$__lost); ?>">
						<td class="td-editDigit" dow="<?php printf("%s",$dow%7); ?>" isHoliday="<?php printf("%d",$isHoliday); ?>"><?php printf("%d",$d); ?></td>
						<td class="td-editDigit" dow="<?php printf("%s",$dow%7); ?>"><?php printf("%s",$week[$dow%7]); ?></td>
						<td class="td-editDigit"><input <?php printf("%s",$oc); ?> name="open[<?php printf("%d",$a); ?>]" type="checkbox" id="open[<?php printf("%d",$a); ?>]" onchange="settSum(this.form)" value="t"  /></td>
						<td class="td-editDigit"><input name="last[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="last[<?php printf("%d",$a); ?>]" onchange="settSum(this.form)" value="<?php printf("%d",$last); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><input name="target[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="target[<?php printf("%d",$a); ?>]" onchange="settSum(this.form)" value="<?php printf("%d",$target); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><label><?php printf("%s",number_format($result,0)); ?></label></td>
						<td class="td-editDigit"><label><?php printf("%s",number_format($rSum,0)); ?></label></td>
						<td class="td-editDigit"><?php printf("%s",number_format($win,2)); ?>％</td>
						<td class="td-editDigit"><?php printf("%s",number_format($ppy,2)); ?>％</td>
						<td class="td-editDigit"><?php printf("%s",number_format($book,0)); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($member)); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($welcome)); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($visitor)); ?></td>
						<!--						<td class="td-editDigit"><?php printf("%s",($member+$visitor)? number_format($result/($member+$visitor)):"????"); ?></td> -->
						<td class="td-editDigit"><?php printf("%s",($visitor)? number_format($result/($visitor)):"????"); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($mlot,0)); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($myen,0)); ?></td>
						<td class="td-edit"><?php printf("%s",nl2br($note)); ?></td>
				</tr>
<?php
	}
?>
				<tr>
						<td class="th-edit">計</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%"><?php printf("%s",number_format($lSum,0)); ?></td>
						<td class="th-editDigit"><input name="tSum" type="text" class="input-Digit" id="tSum" value="<?php printf("%d",$tSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($bSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($mSum)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($wSum)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vSum)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($mlotSum)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($myenSum)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
				</tr>
		</table>
		<br />
<span id="saveList">
		<table width="100%">
				<tr>
						<td width="18%" class="th-edit"><span class="title1">月間売上目標(予算)の登録</span></td>
						<td width="5%" class="td-edit"><label>
								<input type="submit" name="exec" id="exec" value="実行" />
						</label>
						<label></label></td>
						<td width="77%">
								<label>
								<input name="mode" type="hidden" id="mode" value="psave" />
								</label>
								<label>
								<input name="yy" type="hidden" id="yy" value="<?php printf("%d",$thisY);?>" />
								</label>
								<label>
								<input name="mm" type="hidden" id="mm" value="<?php printf("%d",$thisM);?>" />
								</label>
								<label>
								<input name="days" type="hidden" id="days" value="<?php printf("%d",$days); ?>" />
								</label>
						</td>
				</tr>
		</table>
		</span>
		<script language="JavaScript" type="text/javascript">
function alertBlank()
{
//	var lostDay = new Array(<?php printf("%s",implode(",",$lostDay)); ?>);
	var lostDay = array(<?php printf("%s",implode(",",$lostDay)); ?>);
	var a;
	var length;
	if(length=lostDay.length){
		var mes = new Array();
		for(a=0; a<length; a++){
			mes[a] = sprintf("【警告】 %d日の売上が未入力です",lostDay[a]);
		}
		alert(mes.join('\n'));
	}
}

window.onload = function()
{
//
	var elm = document.getElementsByTagName('TD');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		switch(elm[a].getAttribute("dow")){
			case '6':
				elm[a].style.color = "#0000FF";
				elm[a].style.fontWeight = "bold";
				break;
			case '0':
				elm[a].style.color = "#FF0000";
				elm[a].style.fontWeight = "bold";
				break;
			default:
				if(elm[a].getAttribute("isHoliday")=='1'){
					elm[a].style.color = "#00FF00";
					elm[a].style.fontWeight = "bold";
				}
				break;
		}
	}
//
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("passed")==0){
			elm[a].style.backgroundColor = "#CCCCCC";
		}
		else if(elm[a].getAttribute("lost")==1){
			elm[a].style.backgroundColor = "#CCCCCC";
		}
	}
//
	var vs = document.getElementById('viewSum');
	vs.innerHTML = sprintf("累計 ￥%s (%s％)",number_format(<?php printf("%d",$rSum); ?>,0),<?php printf("%.2f",$winN); ?>);
//
/*
*		当月以前の目標入力を禁止する
*/
	var form = document.list;
	var tY = <?php printf("%d",$tt[0]); ?>;
	var tM = <?php printf("%d",$tt[1]); ?>;
	var fY = parseInt(form.elements['yy'].value);
	var fM = parseInt(form.elements['mm'].value);
	if(((tY*12)+tM)>=((fY*12)+fM)){
		Form.disable(form);
		var ooo = document.getElementById('saveList');
		ooo.className = 'notDisplay';
	}
//
}
		alertBlank();
		</script>
</form>
<?php
		break;
//--------------------------------------------------------------------
	}
?>
</body>
</html>
<?php
		pg_query($handle,"commit");
		pg_close($handle);
	}
}
?>
