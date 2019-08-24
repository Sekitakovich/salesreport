<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>season</title>
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

	$__dMin = 2000;
	$__dMax = 2020;
	
	pg_query($handle,"begin");
	pg_query($handle,"LOCK season IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from season");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into season(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("year='%d'",$_REQUEST['year']);
		$set[] = sprintf("stype='%d'",$_REQUEST['stype']);

		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update season set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update season set %s where id='%d'",implode(",",$set),$id);
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
		$query = sprintf("select count(*) from purchase where vf=true and season='%d'",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		return($qo['count']);
	}
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select season.* from season where season.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$year = $qo['year'];
			$stype = $qo['stype'];

			$remark = $qo['remark'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$year = $tt[0];
			$stype = 0;

			$refer = 0;
		}
?>
<p class="title1">シーズン：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var a;
	var err = 0;
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須です";
	}
// Ajax! Ajax! Ajax!
	var parameters = sprintf("?query=select count(*) from season where vf=true and year='%d' and stype='%d' and id<>%d",
		F.elements['year'].value,
		F.elements['stype'].value,
		F.elements['id'].value);
	var ooo = new Ajax.Request('../query.php',{method:'get',asynchronous:false,parameters:parameters});
	var count = parseInt(ooo.transport.responseText);
// Ajax! Ajax! Ajax!
	if(count){
		mes[err++] = "Year:Seasonの組み合わせが既に登録済みです";
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
		<table width="30%">
				<tr>
						<td class="th-edit">year
						<script type="text/javascript">
function setName(F)
{
	if(F.elements['name'].value==''){
		if(F.elements['year'].value!='0'){
			if(F.elements['stype'].value!='0'){
				var season = F.elements['stype'].options[F.elements['stype'].options.selectedIndex].getAttribute("nickname");
				F.elements['name'].value = sprintf("%04d-%s",F.elements['year'].value,season);
			}
		}
	}
}
								</script></td>
						<td width="16%" class="td-edit"><label>
								<select name="year" id="year" onchange="setName(this.form)">
<?php
	$yMin = 2000;
	$yMax = 2100;
	for($a=$yMin; $a<=$yMax; $a++){
		$selected=sprintf("%s",$a==$year? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
<?php
	}
?>
								</select>
						</label></td>
						<td width="3%" class="th-edit">stype</td>
						<td width="70%" class="td-edit"><select name="stype" id="stype" onchange="setName(this.form)">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select * from stype where vf=true order by month desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$stype? $__XHTMLselected:"");
?>
								<option nickname="<?php printf("%s",$qo['nickname']); ?>" <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['nickname'],$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td width="11%" class="th-edit">名称</td>
						<td colspan="3" class="td-edit"><input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="16" maxlength="32" /></td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td colspan="3" class="td-edit"><label>
								<textarea name="remark" cols="48" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td colspan="3" class="td-edit"><input name="exec" type="submit" disabled="disabled" id="exec" value="実行" />
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
		$qq = array();
		$qq[] = sprintf("select season.*,staff.nickname,stype.nickname as stname,stype.name as stnote");
		$qq[] = sprintf("from season join staff on season.ustaff=staff.id join stype on season.stype=stype.id");
		$qq[] = sprintf("where season.vf=true");
		$qq[] = sprintf("order by season.year desc");
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
?>
<p class="title1">シーズン <a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="95%" class="th-edit">年度</td>
						<td width="95%" class="th-edit">種別</td>
						<td width="95%" class="th-edit">purchase</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
//		$active = $qo['active'];
$active ='t';
		$stype = $qo['stype'];
		$stname = sprintf("%s (%s)",$qo['stname'],$qo['stnote']);

?>
				<tr elmtype="season" active="<?php printf("%s",$active); ?>">
						<td class="td-edit"><a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><?php printf("%d",$qo['year']); ?></td>
						<td class="td-edit"><?php printf("%s",$stname); ?></td>
						<td class="td-edit"><select name="purchase" id="purchase" onchange="editMaster(this)">
								<?php
		$query = sprintf("select purchase.*,brand.name from purchase join brand on purchase.brand=brand.id where purchase.season='%d' order by purchase.pdate desc",$qo['id']);
		$nr = pg_query($handle,$query);
		$ns = pg_num_rows($nr);
?>
								<option value="0" selected="selected"><?php printf("%d",$ns); ?></option>
								<?php
	for($cc=0; $cc<$ns; $cc++){
		$no = pg_fetch_array($nr,$cc);
?>
								<option value="<?php printf("%d",$no['id']); ?>"><?php printf("%s %s",$no['name'],dt2JP($no['pdate'])); ?></option>
								<?php
	}
?>
						</select></td>
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
