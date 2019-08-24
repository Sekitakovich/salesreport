<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>exhibition</title>
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
	pg_query($handle,"LOCK exhibition IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from exhibition");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into exhibition(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("fullname='%s'",pg_escape_string($_REQUEST['fullname']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
		$set[] = sprintf("ps='%s'",implode("-",$_REQUEST['ps']));
		$set[] = sprintf("pe='%s'",implode("-",$_REQUEST['pe']));

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update exhibition set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update exhibition set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		function isRefer($handle,$id)
		{
			$query = sprintf("select count(*) from purchase where vf=true and exhibition='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			return($qo['count']);
		}
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select exhibition.* from exhibition where exhibition.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$fullname = $qo['fullname'];
			$remark = $qo['remark'];
			$ps = explode("-",$qo['ps']);
			$pe = explode("-",$qo['pe']);

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$fullname = '';
			$remark = "";
			$ps = $tt;
			$pe = $tt;

			$refer = 0;
		}
?>
<p class="title1">HPF展示会(HPF主催)：編集
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
						<td class="th-edit">名称 (正式：日本語)</td>
						<td class="td-edit"><input name="fullname" type="text" id="fullname" value="<?php printf("%s",$fullname); ?>" size="64" maxlength="256" /></td>
				</tr>
				<tr>
						<td width="2%" class="th-edit">名称 (表示：英語)</td>
						<td width="98%" class="td-edit"><input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="16" maxlength="32" /></td>
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
	editPrepare('edit','exec'); // in common.js
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$query = sprintf("select min(ps),max(ps) from exhibition where vf=true");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$min = explode("-",$qo['min']);
		$max = explode("-",$qo['max']);
		if(isset($_REQUEST['exec'])){
			$ps = $_REQUEST['ps'];
			$pe = $_REQUEST['pe'];
			$kw = $_REQUEST['kw'];
		}
		else{
			$ps = $min;
			$pe = $max;
			$kw = '';
		}	

		$qq = array();
		$qq[] = sprintf("select exhibition.*,cast(now() as date) between exhibition.ps and exhibition.pe as active,staff.nickname");
		$qq[] = sprintf("from exhibition join staff on exhibition.ustaff=staff.id");
		$qq[] = sprintf("where exhibition.vf=true");
		$qq[] = sprintf("and exhibition.ps between '%s' and '%s'",implode("-",$ps),implode("-",$pe));
		if($kw){

			$ooo = array();
			$ooo[] = sprintf("exhibition.name like '%%%s%%'",$kw);

			$ppp = implode(" or ",$ooo);
			$qq[] = sprintf("and (%s)",$ppp);
		}
		$qq[] = sprintf("order by exhibition.ps desc,exhibition.pe desc");
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
?>
<p class="title1">展示会(HPF主催) <a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit">新規登録</a></p>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">期間</td>
						<td width="24%" class="td-edit"><label></label>
										<label>
										<select name="ps[0]" id="ps[0]" onchange="leapAdjust(this.form,'ps')">
												<?php
for($a=$min[0]; $a<=$max[0]; $a++){
	$selected=sprintf("%s",$a==$ps[0]? $__XHTMLselected:"");
?>
												<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
												<?php
}
?>
										</select></label>
年
<label><select name="ps[1]" id="ps[1]" onchange="leapAdjust(this.form,'ps')">
		<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$ps[1]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label>
月
<label><select name="ps[2]" id="ps[2]">
		<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$ps[2]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label>
日 ～
<label><select name="pe[0]" id="pe[0]" onchange="leapAdjust(this.form,'pe')">
		<?php
for($a=$min[0]; $a<=$max[0]; $a++){
	$selected=sprintf("%s",$a==$pe[0]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label>
年
<label><select name="pe[1]" id="pe[1]" onchange="leapAdjust(this.form,'pe')">
		<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$pe[1]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label>
月
<label><select name="pe[2]" id="pe[2]">
		<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$pe[2]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label>
日
<input type="submit" name="go" id="go" value="更新" />
<input name="exec" type="hidden" id="exec" value="on" />
</label></td>
						<td width="7%" class="th-edit">キーワード</td>
						<td width="64%" class="td-edit"><label>名称に
										<input onchange=this.form.submit() name="kw" type="text" id="kw" value="<?php printf("%s",$kw);  ?>" />
						を含む</label></td>
				</tr>
		</table>
</form>
<p></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">名称(正式)</td>
						<td width="1%" class="th-edit">名称(表示)</td>
						<td width="1%" class="th-edit">期間</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$active = $qo['active'];
?>
				<tr elmtype="exhibition" active="<?php printf("%s",$active); ?>">
						<td class="td-edit"><a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['fullname']); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><?php printf("%s ～ %s",dt2JP($qo['ps']),dt2JP($qo['pe'])); ?></td>
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
		if(elm[a].getAttribute("elmtype")=='exhibition'){
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
