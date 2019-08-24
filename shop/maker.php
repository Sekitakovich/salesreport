<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>brand</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	function isRefer($handle,$id)
	{
		$query = sprintf("select count(*) from brand where vf=true and maker='%d'",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		return($qo['count']);
	}
	$whoami = getStaffInfo($handle);

//	Var_Dump::display($whoami);

	pg_query($handle,"begin");
	pg_query($handle,"LOCK maker IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from maker");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into maker(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("address='%s'",pg_escape_string($_REQUEST['address']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("nation='%d'",$_REQUEST['nation']);
		$set[] = sprintf("currency='%d'",$_REQUEST['currency']);

		for($attribute=0,$a=0,$b=count($_REQUEST['attribute']); $b--; $a++){
			$attribute|=$_REQUEST['attribute'][$a];
		}
		$set[] = sprintf("attribute='%d'",$attribute);

		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update maker set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update maker set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select maker.* from maker where maker.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$nation = $qo['nation'];
			$currency = $qo['currency'];
			$address = $qo['address'];
			$attribute = $qo['attribute'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$nation = 0;
			$currency = 0;
			$address = '';
			$attribute = 0;

			$refer = 0;
		}
?>
<p class="title1">Maker/Shipper：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須";
	}
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else if(F.elements['delete'].checked){
		return confirm('このレコードを削除しますか?');
	}
	else return true;
}
		</script>
</p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="29%">
				<tr>
						<td width="2%" class="th-edit">名称&nbsp;</td>
						<td width="98%" class="td-edit"><label>
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="32" maxlength="128" /></label><label></label>&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">属性&nbsp;</td>
						<td class="td-edit">
<?php
	for($a=0,$b=count($__cAttr); $b--; $a++){
		$aName = $__cAttr[$a]['name'];
		$aMask = $__cAttr[$a]['mask'];
		$checked = sprintf("%s",$attribute&$aMask? "checked=\"checked\"":"");
?>
<label>
<input name="attribute[]" type="checkbox" id="attribute[]" value="<?php printf("%d",$aMask); ?>" <?php printf("%s",$checked); ?> /> 
<?php printf("%s",$aName); ?></label><br /><?php
	}
?>						&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">nation&nbsp;</td>
						<td class="td-edit"><select name="nation" id="nation">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select * from nation where vf=true order by code3");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$nation? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s -%s)",$qo['code3'],$qo['name'],$qo['kana']); ?></option>
								<?php
		}
?>
						</select>&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">currency</td>
						<td class="td-edit"><select name="currency" id="currency">
										<option value="0">-- 選択してください --</option>
										<?php
		$cd = array();
		$ex = array();
		$query = sprintf("SELECT currency.* from currency where currency.vf=true order by currency.code");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$currency? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['code'],$qo['name']); ?></option>
										<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">address&nbsp;</td>
						<td class="td-edit"><label>
								<input name="address" type="text" id="address" value="<?php printf("%s",$address); ?>" size="64" maxlength="256" />
						</label>&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">備考&nbsp;</td>
						<td class="td-edit"><label>
								<textarea name="remark" cols="64" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
						</label>&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">登録&nbsp;</td>
						<td class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
								<input name="mode" type="hidden" id="mode" value="save" />
								<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
								<span id="void">
								<input name="delete" type="checkbox" id="delete" value="t" />
削除する</span>&nbsp;</td>
				</tr>
		</table>
</form><script language="JavaScript" type="text/javascript">
window.onload = function(){
	var refer = <?php printf("%d",$refer); ?>;
	var id = <?php printf("%d",$id); ?>;
	if(refer || id==0){
		var elm = document.getElementById('void');
		elm.className = 'notDisplay';
	}
	var edit = <?php printf("%s",($whoami['perm']&PERM_MASTER_EDIT)? "true":"false"); ?>;
	if(edit==false){
		Form.disable('edit'); // see prototype.js
	}
	editPrepare('edit','exec'); // in common.js
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'maker.udate desc'),
			array('name'=>'名称','text'=>'maker.name'),
			array('name'=>'narea','text'=>'narea.weight desc,nation.name,maker.name'),
			array('name'=>'nation','text'=>'nation.name,maker.name'),
		);

		if(isset($_REQUEST['exec'])){
			$kw = $_REQUEST['kw'];
			$nation = $_REQUEST['nation'];
			$narea = $_REQUEST['narea'];
			$currency = $_REQUEST['currency'];
			$order = $_REQUEST['order'];
			$attribute = $_REQUEST['attribute'];
			$_SESSION['maker'] = array(
				'kw'=>$kw,
				'nation'=>$nation,
				'narea'=>$narea,
				'currency'=>$currency,
				'attribute'=>$attribute,
				'order'=>$order
			);
		}
		else if(isset($_SESSION['maker'])){
			$bo = $_SESSION['maker'];
			$kw = $bo['kw'];
			$nation = $bo['nation'];
			$narea = $bo['narea'];
			$currency = $bo['currency'];
			$order = $bo['order'];
			$attribute = $bo['attribute'];
		}
		else{
			$kw = "";
			$nation =0;
			$narea = 0;
			$currency =0;
			$order = 0;
			$attribute = 0;
		}

//printf("attribute = [%d]",$attribute);
?>
<p class="title1">Maker/Shipper <a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit">新規登録</a></p>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">area&nbsp;</td>
						<td width="24%" class="td-edit"><select name="narea" id="narea" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select narea.* from narea where narea.vf=true order by narea.weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$narea? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
								<?php
		}
?>
						</select>&nbsp;</td>
						<td width="7%" class="th-edit">nation&nbsp;</td>
						<td width="64%" class="td-edit"><select name="nation" id="nation" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select nation.id,nation.code3,nation.kana,nation.name from nation where nation.vf=true and nation.id in (select distinct nation from maker where vf=true) order by nation.name");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$nation? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s - %s)",$qo['code3'],$qo['name'],$qo['kana']); ?></option>
								<?php
		}
?>
						</select>&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">属性&nbsp;</td>
						<td class="td-edit"><label>
						<select name="attribute" id="attribute" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		for($a=0,$b=count($__cAttr); $b--; $a++){
			$aName = $__cAttr[$a]['name'];
			$aMask = $__cAttr[$a]['mask'];
			$selected = sprintf("%s",$aMask==$attribute? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$aMask); ?>"><?php printf("%s",$aName); ?></option>
								<?php
		}
?>
						</select>
						</label>&nbsp;</td>
						<td class="th-edit">currency</td>
						<td class="td-edit"><select name="currency" id="currency" onchange="this.form.submit()">
										<option value="0">-- 全て --</option>
										<?php
		$cd = array();
		$ex = array();
//		$query = sprintf("SELECT currency.* from currency where currency.vf=true order by currency.code");
		$query = sprintf("SELECT currency.* from currency where currency.vf=true and id in (select distinct currency from maker where vf=true) order by currency.code");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$currency? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['code'],$qo['name']); ?></option>
										<?php
		}
?>
								</select></td>
				</tr>
				<tr>
						<td class="th-edit">キーワード&nbsp;</td>
						<td class="td-edit"><label></label>
										<label>名称もしくは読みに
												<input name="kw" type="text" id="kw" value="<?php printf("%s",$kw); ?>" size="16" maxlength="64" />
												を含む</label>&nbsp;</td>
						<td class="th-edit">並べ替え&nbsp;</td>
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
										<input name="exec" type="hidden" id="exec" value="on" />&nbsp;</td>
				</tr>
		</table>
</form>
<?php
//	
	$qq = array();
	$qq[] = sprintf("select currency.code as ccode,narea.name as aname,nation.code3,nation.name as nname,nation.kana as nkana,maker.*,ustaff.nickname");
	$qq[] = sprintf("from maker join currency on maker.currency=currency.id join (nation join narea on nation.narea=narea.id) on maker.nation=nation.id join staff as ustaff on maker.ustaff=ustaff.id");
	$qq[] = sprintf("where maker.vf=true");
	if($narea){
		$qq[] = sprintf("and narea.id='%d'",$narea);
	}
	if($nation){
		$qq[] = sprintf("and nation.id='%d'",$nation);
	}
	if($attribute){
		$qq[] = sprintf("and maker.attribute=%d",$attribute);
	}
	if($kw){
		$kw = strtoupper($kw);
		$dst = array('maker.name');
		$ooo = array();
		for($a=0,$b=count($dst); $b--; $a++){
			$ooo[] = sprintf("upper(%s) like '%%%s%%'",$dst[$a],pg_escape_string($kw));
		}
		$ppp = implode(" or ",$ooo);
		$qq[] = sprintf("and %s",$ppp);
	}
	if($currency){
		$qq[] = sprintf("and currency.id='%d'",$currency);
	}
	$qq[] = sprintf("order by %s",$__oList[$order]['text']);
	$query = implode(" ",$qq);
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);

?><!--<?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
<p class="title1">検索結果 (<?php printf("%d",$qs); ?>件)
		<script type="text/javascript">
function openURL(elm)
{
	var url = elm.getAttribute('url');
	if(url){
		if(confirm(sprintf("別ウィンドウで %s を開きますか?",url))){
			window.open(url);
		}
	}
}
		</script>
</p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;&nbsp;</td>
						<td width="2%" class="th-edit">名称&nbsp;</td>
						<td width="95%" class="th-edit">M&nbsp;</td>
						<td width="95%" class="th-edit">S&nbsp;</td>
						<td width="95%" class="th-edit">area&nbsp;</td>
						<td width="95%" class="th-edit">nation&nbsp;</td>
						<td width="95%" class="th-edit">通貨</td>
						<td width="95%" class="th-edit">brand&nbsp;</td>
						<td width="95%" class="th-edit">最終更新日時&nbsp;</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
?>
				<tr id="row[<?php printf("%d",$a); ?>]">
						<td class="td-edit"><a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a>&nbsp;</td>
						<td class="td-edit">						<span title="<?php printf("%s",$qo['name']); ?>" class="openURL" onclick="openGoogle('<?php printf("%s",rawurlencode($qo['name'])); ?>')"><?php printf("%s",cutoffStr($qo['name'])); ?></span>&nbsp;</td>
						<td class="td-edit"><?php printf("%s",($qo['attribute']&CORP_ATTR_MAKER)? "○":""); ?>&nbsp;</td>
						<td class="td-edit"><?php printf("%s",($qo['attribute']&CORP_ATTR_SHIPPER)? "○":""); ?>&nbsp;</td>
						<td class="td-edit"><span title="<?php printf("%s - %s",$qo['nname'],$qo['nkana']); ?>"><?php printf("%s",$qo['aname']); ?></span>&nbsp;</td>
						<td class="td-edit"><span title="<?php printf("%s - %s",$qo['nname'],$qo['nkana']); ?>"><?php printf("%s",$qo['code3']); ?></span>&nbsp;</td>
						<td class="td-edit"><?php printf("%s",$qo['ccode']); ?></td>
						<td class="td-edit">
<?php
	$query = sprintf("select * from brand where vf=true and maker='%d'",$qo['id']);
	$br = pg_query($handle,$query);
	$bs = pg_num_rows($br);
	$bn = array();
	for($b=0; $b<$bs; $b++){
		$bo = pg_fetch_array($br,$b);
		$bn[] = sprintf("<a href=\"brand.php?mode=edit&id=%d\">%s</a>",$bo['id'],cutoffStr($bo['name']));
	}
	printf("%s",implode("<br />",$bn));
?>						&nbsp;</td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?>&nbsp;</td>
				</tr>
<?php
	}
?>
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
</body>
</html>
