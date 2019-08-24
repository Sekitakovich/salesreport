<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>accesslog</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Refresh" content="60" />
<style type="text/css">
<!--
.__setDisplay {
	display: inline;
}
-->
</style>
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<?php

$cleanUp = false;
$dMax = 30;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
	pg_query($handle,"LOCK accesslog IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	default:
		if(isset($_REQUEST['exec'])){
			$days = $_REQUEST['days'];
			$_SESSION['alog']['days'] = $days;
		}
		else if(isset($_SESSION['alog'])){
			$days = $_SESSION['alog']['days'];
		}
		else{
			$days = 1;
		}

		$alog = array();
		$account = array();

		$qq = array();
		$qq[] = sprintf("SELECT staff.id,staff.shop,staff.perm,staff.nickname,staff.account,shop.name as sname,staff.last as atime");
		$qq[] = sprintf("from staff join shop on staff.shop=shop.id");
		$qq[] = sprintf("where staff.vf=true");
		$qq[] = sprintf("and staff.alog=true");
		$qq[] = sprintf("and staff.last>now()+'-%d hour'",$days*24);
$qq[] = sprintf("order by staff.last desc");
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query(%d:%d) = [%s]",$qr,$qs,$query); ?> --><?php
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$account[] = $qo['id'];
			$alog[] = array('id'=>$qo['id'],'account'=>$qo['account'],'nickname'=>$qo['nickname'],'atime'=>$qo['atime'],'shop'=>$qo['shop'],'sname'=>$qo['sname'],'perm'=>$qo['perm']);
		}

		if(true){
			$qq = array();
			$qq[] = sprintf("SELECT staff.idate,staff.id,staff.shop,staff.perm,staff.nickname,staff.account,shop.name as sname,staff.last as atime");
			$qq[] = sprintf("from staff join shop on staff.shop=shop.id");
			$qq[] = sprintf("where staff.vf=true");
			$qq[] = sprintf("and staff.alog=true");
			$qq[] = sprintf("and staff.id not in (%s)",implode(",",$account));
			$qq[] = sprintf("order by staff.last desc,staff.idate desc");
			$query = implode(" ",$qq);
			$sr = pg_query($handle,$query);
			$ss = pg_num_rows($sr); //var_dump($query);
?><!-- <?php printf("Query(%d:%d) = [%s]",$sr,$ss,$query); ?> --><?php
		}
		else $ss=0;
?>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
<table width="100%">
		<tr>
				<td valign="top" nowrap="nowrap"><span class="title1"><?php printf("%s",date("Y年m月d日 H時i分")); ?> 現在まで
								<select name="days" id="days" onchange="this.form.submit()">
										<?php
	for($a=1; $a<=$dMax; $a++){
		$selected = sprintf("%s",$a==$days? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected);?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
										<?php
	}
?>
								</select>
日以内の最終ログイン状況 (<?php printf("%s",number_format(count($alog))); ?>名)</span>
						<input name="exec" type="hidden" id="exec" value="t" /></td>
				<td valign="top" nowrap="nowrap" class="title1">左記期間内に認証記録のないstaff (<?php printf("%s",number_format($ss)); ?>名)</td>
				</tr>
		<tr>
				<td valign="top" nowrap="nowrap">&nbsp;</td>
				<td valign="top" nowrap="nowrap">&nbsp;</td>
		</tr>
		<tr>
				<td width="40%" valign="top" nowrap="nowrap">
						<table>
								<tr>
										<td class="th-edit">日時</td>
										<td class="th-edit">アカウント</td>
										<td class="th-edit">ハンドル</td>
										<td class="th-edit">権限</td>
								</tr>
								<?php
	for($a=0,$b=count($alog); $b--; $a++){
		$qo = $alog[$a];
		if($qo['shop']){
			$who = sprintf("%s",$qo['sname']);
		}
		else{
			$who = sprintf("%s",$qo['nickname']);
		}
		$when = ts2JP($qo['atime']);
		$ac = $qo['account'];
?>
								<tr shop="<?php printf("%d",$qo['shop']); ?>">
										<td class="td-edit"><?php printf("%s",$when); ?></td>
										<td class="td-edit"><?php printf("%s",$ac); ?></td>
										<td class="td-edit"><a href="staff.php?mode=edit&id=<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$who); ?></a></td>
										<td class="td-edit"><?php
for($aa=0,$bb=count($__dPerm); $bb--; $aa++){
	$perm = $__dPerm[$aa];
	$icon = sprintf("../images/checkbox/%s",$perm['icon']);
	$alt = $perm['name'];
	$show = $perm['mask']&$qo['perm'];
?>
												<img type="perm" src="<?php printf("%s",$icon); ?>" alt="" width="16" height="16" border="0" class="notDisplay" title="<?php printf("%s",$alt); ?>" show="<?php printf("%d",$show); ?>" />
												<?php
}
?></td>
								</tr>
								<?php
	}
?>
								<tr>
										<td class="th-edit">&nbsp;</td>
										<td class="th-edit">&nbsp;</td>
										<td class="th-edit"><?php printf("%s",number_format(count($alog))); ?>名</td>
										<td class="th-edit">&nbsp;</td>
								</tr>
						</table></td>
				<td width="49%" valign="top" nowrap="nowrap">
						<table>
								<tr>
										<td class="th-edit">最終</td>
										<td class="th-edit">ハンドル</td>
										<td class="th-edit">権限</td>
										</tr>
								<?php
	for($a=0; $a<$ss; $a++){
		$qo = pg_fetch_array($sr,$a);
		if($qo['shop']){
			$who = sprintf("%s",$qo['sname']);
		}
		else{
			$who = sprintf("%s",$qo['nickname']);
		}
		$ac = $qo['account'];
		$when = $qo['atime']? ts2JP($qo['atime']):"?";
//		$mail = getPGSQLarray($qo['mail']);
$mail = '';
?>
								<tr shop="<?php printf("%d",$qo['shop']); ?>">
										<td class="td-edit"><?php printf("%s",$when); ?></td>
										<td class="td-edit"><div blink="<?php printf("%s",$when=='?'? "on":"off"); ?>"><a href="staff.php?mode=edit&id=<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$who); ?></a></div></td>
										<td nowrap="nowrap" class="td-edit"><?php
for($aa=0,$bb=count($__dPerm); $bb--; $aa++){
	$perm = $__dPerm[$aa];
	$icon = sprintf("../images/checkbox/%s",$perm['icon']);
	$alt = $perm['name'];
	$show = $perm['mask']&$qo['perm'];
?>
												<img type="perm" src="<?php printf("%s",$icon); ?>" alt="" width="16" height="16" border="0" class="notDisplay" title="<?php printf("%s",$alt); ?>" show="<?php printf("%d",$show); ?>" />
												<?php
}
?></td>
										</tr>
								<?php
	}
?>
								<tr>
										<td class="th-edit">&nbsp;</td>
										<td class="th-edit"><?php printf("%s",number_format($ss)); ?>名</td>
										<td class="th-edit">&nbsp;</td>
										</tr>
						</table></td>
				</tr>
</table>
</form>
		<?php
		break;
//--------------------------------------------------------------------
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
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
</body>
</html>
