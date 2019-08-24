<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>accesslog</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Refresh" content="300" />
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<?php

$cleanUp = true;
$dMax = 30;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
	pg_query($handle,"LOCK reportlog IN EXCLUSIVE MODE");
//
	$permIcon = array(
		array('type'=>0,'src'=>"../images/checkbox/000805.gif",'alt'=>$__dPerm[0]['name'],'mask'=>$__dPerm[0]['mask']),
		array('type'=>1,'src'=>"../images/checkbox/000811.gif",'alt'=>$__dPerm[1]['name'],'mask'=>$__dPerm[1]['mask']),
		array('type'=>2,'src'=>"../images/checkbox/000818.gif",'alt'=>$__dPerm[2]['name'],'mask'=>$__dPerm[2]['mask']),
		array('type'=>3,'src'=>"../images/checkbox/000807.gif",'alt'=>$__dPerm[3]['name'],'mask'=>$__dPerm[3]['mask']),
	);
//
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	default:
		if(isset($_REQUEST['exec'])){
			$days = $_REQUEST['days'];
			$_SESSION['rlog']['days'] = $days;
		}
		else if(isset($_SESSION['rlog'])){
			$days = $_SESSION['rlog']['days'];
		}
		else{
			$days = 1;
		}
// get_browserで妙に時間がかかる気が・・・
		function getWBprop($handle,$ua)
		{
			for($aa=0,$bb=count($_SESSION['uacache']); $bb--; $aa++){
				if($_SESSION['uacache'][$aa]['ua']==$ua){
?><!-- UA cache Hit! --><?php
					return($_SESSION['uacache'][$aa]['prop']); // キャッシュにヒットしたら
				}
			}

			$query = sprintf("select prop from useragent where vf=true and ua='%s'",$ua);
			$qr = pg_query($handle,$query);
			if(pg_num_rows($qr)){
				$qo = pg_fetch_array($qr);
?><!-- UA table Hit (<?php printf("%s",$query); ?>) --><?php
				return(unserialize($qo['prop']));
			}
			else{
				$browser = get_browser($ua,true);
				$_SESSION['uacache'][] = array('ua'=>$ua,'prop'=>$browser);
				$query = sprintf("select max(id) from useragent");
				$qr = pg_query($handle,$query);
				$qo = pg_fetch_array($qr);
				$id = $qo['max']+1;
				$prop = pg_escape_string(serialize($browser));
				$query = sprintf("insert into useragent(id,ua,prop) values('%d','%s','%s')",$id,$ua,$prop);
				$qr = pg_query($handle,$query);
?><!-- UA stored! [<?php printf("%s",$query); ?>] --><?php
				return($browser);
			}
		}
//		
?>
<!-- <?php printf("uacache entries = [%d]",count($_SESSION['uacache'])); ?> -->
<script type="text/javascript">
function checkTheForm(F)
{
	if(F.elements['cleanup'].checked == true){
		return confirm('削除された履歴は元に戻せません。よろしいですか?');
	}
	else return(true);
}
</script>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list" onsubmit="return checkTheForm(this)">
<?php
	$qq = array();
	$qq[] = sprintf("select reportlog.etime,reportlog.ip,reportlog.ua,reportlog.rtype,staff.id,staff.nickname,staff.shop,staff.perm,shop.name as sname");
	$qq[] = sprintf("from reportlog join (staff join shop on staff.shop=shop.id) on reportlog.staff=staff.id");
	$qq[] = sprintf("where reportlog.vf=true");
	$qq[] = sprintf("and reportlog.etime>now()+'-%d hour'",$days*24);
	$qq[] = sprintf("order by reportlog.etime desc");
	$query = implode(" ",$qq);
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);

?><!-- <?php printf("Query(%d:%d) = [%s]",$qr,$qs,$query); ?> --><?php
	if(isset($_REQUEST['cleanup']) && $_REQUEST['cleanup']=='t'){ // お掃除
		$query = sprintf("update reportlog set vf=false where vf=true and etime<=now()+'-%d hour'",$days*24);
		$cr = pg_query($handle,$query);
?><!-- <?php printf("Query(%d) = [%s]",$cr,$query); ?> --><?php
		$query = sprintf("update csvlog set vf=false where vf=true and etime<=now()+'-%d hour'",$days*24);
		$cr = pg_query($handle,$query);
?><!-- <?php printf("Query(%d) = [%s]",$cr,$query); ?> --><?php
	}
?>
<table width="100%">
		<tr>
				<td valign="top" nowrap="nowrap"><table width="45%">
						<tr>
								<td width="14%" valign="top" nowrap="nowrap" class="th-edit"><span class="title1">レポート閲覧履歴</span></td>
								<td width="86%" valign="top" nowrap="nowrap" class="td-edit"><span class="title1">現在まで
										<select name="days" id="days">
																<?php
	for($a=1; $a<=$dMax; $a++){
		$selected = sprintf("%s",$a==$days? $__XHTMLselected:"");
?>
																<option <?php printf("%s",$selected);?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
																<?php
	}
?>
														</select>
										日以内 (<?php printf("%s",number_format($qs)); ?>アクセス)</span></td>
								<td width="86%" valign="top" nowrap="nowrap" class="td-edit"><label>
										<input name="cleanup" type="checkbox" id="cleanup" value="t" />
										これ以前の履歴をクリアする
								</label></td>
								<td width="86%" valign="top" nowrap="nowrap" class="th-edit"><label>
										<input type="submit" name="refresh" id="refresh" value="再表示" />
										<input name="exec" type="hidden" id="exec" value="t" />
																		</label></td>
						</tr>
				</table></td>
		</tr>
		<tr>
				<td valign="top" nowrap="nowrap">&nbsp;</td>
		</tr>
		<tr>
				<td width="41%" valign="top" nowrap="nowrap"><table width="12%">
						<tr>
								<td width="2%" class="th-edit">日時</td>
								<td width="94%" class="th-edit">type</td>
								<td width="3%" class="th-edit">staff</td>
								<td width="1%" class="th-edit">RemoteHost</td>
								<td width="94%" class="th-edit">UserAgent</td>
						</tr>
						<?php
	$rlogtime = isset($_SESSION['rlogtime'])? $_SESSION['rlogtime']:time();

	$fa = pg_fetch_all($qr);
	for($a=0; $a<$qs; $a++){
//		$qo = pg_fetch_array($qr,$a);
		$qo = $fa[$a];
		if($qo['shop']){
			$who = sprintf("%s",$qo['sname']);
		}
		else{
			$who = sprintf("%s",$qo['nickname']);
		}
		$ip = $qo['ip'];
		$host = gethostbyaddr($ip);
		$rtype = sprintf("%s",$qo['rtype']=='D'? "日報":"期間");
		$ua = $qo['ua'];
		$browser = getWBprop($handle,$ua);

		$new = strtotime($qo['etime'])>$rlogtime? "t":"f";
?>
						<tr shop="<?php printf("%d",$qo['shop']); ?>" new="<?php printf("%s",$new); ?>">
								<td class="td-edit"><?php printf("%s",ts2JP($qo['etime'])); ?></td>
								<td class="td-editDigit"><?php printf("%s",$rtype); ?></td>
								<td class="td-edit"><a href="staff.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$who); ?></a></td>
								<td class="td-edit"><div title="<?php printf("%s",$host? $ip:""); ?>"><?php printf("%s",$host? $host:$ip); ?></div></td>
								<td class="td-edit"><div title="<?php printf("%s",$ua); ?>"><?php printf("%s (%s)",$browser['parent'],$browser['platform']); ?></div></td>
						</tr>
						<?php
	}
?>
				</table></td>
		</tr>
		<tr>
				<td valign="top" nowrap="nowrap"><span class="title1">CSV出力履歴</span></td>
		</tr>
		<tr>
				<td valign="top" nowrap="nowrap"><table width="12%">
						<tr>
								<td width="2%" class="th-edit">日時</td>
								<td width="3%" class="th-edit">staff</td>
								<td width="1%" class="th-edit">RemoteHost</td>
								<td width="94%" class="th-edit">UserAgent</td>
								<td width="94%" class="th-edit">Size</td>
						</tr>
						<?php
	$qq = array();
	$qq[] = sprintf("select csvlog.*,length(csvlog.csv) as size,staff.nickname");
	$qq[] = sprintf("from csvlog join staff on csvlog.staff=staff.id");
	$qq[] = sprintf("where csvlog.vf=true");
	$qq[] = sprintf("and csvlog.etime>now()+'-%d hour'",$days*24);
	$qq[] = sprintf("order by csvlog.etime desc");
	$query = implode(" ",$qq);
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$ip = $qo['ip'];
		$host = gethostbyaddr($ip);
		$browser = getWBprop($handle,$qo['ua']);
		$new = strtotime($qo['etime'])>$rlogtime? "t":"f";
?>
						<tr new="<?php printf("%s",$new); ?>">
								<td class="td-edit"><?php printf("%s",ts2JP($qo['etime'])); ?></td>
								<td class="td-edit"><?php printf("%s",$qo['nickname']); ?></td>
								<td class="td-edit"><div title="<?php printf("%s",$host? $ip:""); ?>"><?php printf("%s",$host? $host:$ip); ?></div></td>
								<td class="td-edit"><div title="<?php printf("%s",$ua); ?>"><?php printf("%s (%s)",$browser['parent'],$browser['platform']); ?></div></td>
								<td class="td-editDigit"><?php printf("%s",number_format($qo['size'])); ?> bytes</td>
						</tr>
						<?php
	}
?>
				</table></td>
		</tr>
</table>
</form>
<script language="JavaScript" type="text/javascript">
window.onload = function()
{
	var elm;
	var a;
	var b;

	elm = document.getElementsByTagName('TR');
	for(a=0,b=elm.length; b--; a++){
		switch(elm[a].getAttribute('shop')){
			case null:
			case '0':
				break;
			default:
				elm[a].style.backgroundColor = '#FFCCFF';
				break;
		}
		switch(elm[a].getAttribute('new')){
			case 't':
				elm[a].style.fontWeight = 'bold';
				break;
			default:
				break;
		}
	}

	elm = document.getElementsByTagName('IMG');
	for(a=0,b=elm.length; b--; a++){
		if((elm[a].getAttribute('show'))!=0){
			elm[a].className = 'setDisplay';
		}
	}
	startBlink(2);
}
</script>
<?php
		$_SESSION['rlogtime'] = time(); // 最後にログを見た時刻
		break;
//--------------------------------------------------------------------
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</body>
</html>
