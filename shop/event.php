<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>event</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);

	pg_query($handle,"begin");
	pg_query($handle,"LOCK event IN EXCLUSIVE MODE");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from event");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into event(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("shop='{%s}'",implode(",",$_REQUEST['shop']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
		$set[] = sprintf("ps='%s'",implode("-",$_REQUEST['ps']));
		$set[] = sprintf("pe='%s'",implode("-",$_REQUEST['pe']));

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update event set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update event set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="event.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select event.* from event where event.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$shop = getPGSQLarray($qo['shop']);
			$ps = explode("-",$qo['ps']);
			$pe = explode("-",$qo['pe']);

			$refer = 0;
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$shop = array();
			$ps = $tt;
			$pe = $tt;

			$refer = 0;
		}
?>
<p class="title1">HPFイベント：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var a;
	var err = 0;
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須です";
	}
/*
	if(F.elements['shop'].value==0){
		mes[err++] = "店舗は必須です";
	}
*/
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
		<table width="34%">
				<tr>
						<td width="2%" class="th-edit">名称</td>
						<td width="98%" class="td-edit"><input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="64" maxlength="256" /></td>
				</tr>
				<tr>
						<td class="th-edit">期間</td>
						<td class="td-edit"><span class="td-nowrap">
								<select name="ps[0]" id="ps[0]" onchange="leapAdjust(this.form,'ps')">
										<?php
	for($a=$tt[0]-100; $a<=$tt[0]; $a++){
		$selected=sprintf("%s",$a==$ps[0]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
										<?php
	}
?>
										</option>
								</select>
								年
								<select name="ps[1]" id="ps[1]" onchange="leapAdjust(this.form,'ps')">
										<?php
	for($a=1; $a<=12; $a++){
		$selected=sprintf("%s",$a==$ps[1]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
												<?php
	}
?>
										</option>
								</select>
								月
								<select name="ps[2]" id="ps[2]">
										<?php
	for($a=1; $a<=31; $a++){
		$selected=sprintf("%s",$a==$ps[2]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
												<?php
	}
?>
										</option>
								</select>
								日 ～
								<select name="pe[0]" id="pe[0]" onchange="leapAdjust(this.form,'pe')">
										<?php
	for($a=$tt[0]-100; $a<=$tt[0]; $a++){
		$selected=sprintf("%s",$a==$pe[0]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
												<?php
	}
?>
										</option>
								</select>
								年
								<select name="pe[1]" id="pe[1]" onchange="leapAdjust(this.form,'pe')">
										<?php
	for($a=1; $a<=12; $a++){
		$selected=sprintf("%s",$a==$pe[1]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
												<?php
	}
?>
										</option>
								</select>
								月
								<select name="pe[2]" id="pe[2]">
										<?php
	for($a=1; $a<=31; $a++){
		$selected=sprintf("%s",$a==$pe[2]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
												<?php
	}
?>
										</option>
								</select>
								日</span></td>
				</tr>
				<tr>
						<td class="th-edit">店舗</td>
						<td class="td-edit"><?php
//	$query = sprintf("select shop.* from shop,division,area where shop.vf=true and shop.division=division.id and shop.area=area.id order by division.weight desc,area.weight desc,shop.name");
	$query = sprintf("select shop.* from shop,division,area where shop.vf=true and shop.division=division.id and shop.area=area.id order by shop.name");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$checked = sprintf("%s",in_array($qo['id'],$shop)? $__XHTMLchecked:"");
?>
										<label>
										<input <?php printf("%s",$checked); ?> name="shop[]" type="checkbox" id="shop[]" value="<?php printf("%d",$qo['id']); ?>" />
												<?php printf("%s",$qo['name']); ?> </label>
								<br />
										<?php
	}
?>						</td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td class="td-edit"><label>
								<textarea name="remark" cols="48" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
								<input name="mode" type="hidden" id="mode" value="save" />
								<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
								<span id="void">
								<input name="delete" type="checkbox" id="delete" value="t" />
削除する</span></td>
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
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$qq = array();
		$qq[] = sprintf("select event.*,cast(now() as date) between event.ps and event.pe as active,staff.nickname");
		$qq[] = sprintf("from event join staff on event.ustaff=staff.id");
		$qq[] = sprintf("where event.vf=true");
		$qq[] = sprintf("order by event.ps desc,event.pe desc");
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
?>
<p class="title1">イベント <a href="event.php?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="1%" class="th-edit">期間</td>
						<td width="2%" class="th-edit">店舗</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$active = $qo['active'];
		$shop = getPGSQLarray($qo['shop']);

		if(count($shop)){
			$qq = array();
			$qq[] = sprintf("select *");
			$qq[] = sprintf("from shop");
			$qq[] = sprintf("where vf=true");
			$qq[] = sprintf("and id in (%s)",implode(",",$shop));
			$qq[] = sprintf("order by shop.name");
			$query = implode(" ",$qq);
			$sr = pg_query($handle,$query);
			$ss = pg_num_rows($sr);
			$ooo = array();
			for($aa=0; $aa<$ss; $aa++){
				$so = pg_fetch_array($sr,$aa);
				$ooo[] = $so['name'];
			}
			$sname = implode("<br />",$ooo);
		}
		else $sname = "????";
?>
				<tr elmtype="event" active="<?php printf("%s",$active); ?>">
						<td class="td-edit"><a href="event.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><?php printf("%s ～ %s",dt2JP($qo['ps']),dt2JP($qo['pe'])); ?></td>
						<td class="td-edit"><?php printf("%s",$sname); ?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
		
		</table>
</form>
<script language="JavaScript" type="text/javascript">
window.onload = function()
{
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
//console.info(elm[a].getAttribute("elmtype"));
		if(elm[a].getAttribute("elmtype")=='event'){
			if(elm[a].getAttribute("active")=='t'){
//				elm[a].style.backgroundColor = "#FF8080";
				elm[a].style.background = '#FFCCFF';
			}
		}
	}
}
</script>
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
