<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>tenant</title>
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
		$query = sprintf("select count(*) from shop where vf=true and tenant='%d'",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		return($qo['count']);
	}
	$whoami = getStaffInfo($handle);

//	Var_Dump::display($whoami);

	pg_query($handle,"begin");
	pg_query($handle,"LOCK tenant IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "wsave":
		$idS = $_REQUEST['idS'];
		$old = $_REQUEST['old'];
		
		for($a=0,$b=count($idS); $b--; $a++){
			if($old[$a]!=$idS[$a]){
				$query = sprintf("update tenant set weight='%d' where id='%d'",$b,$idS[$a]);
				$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			}
		}
?>
<a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from tenant");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into tenant(id,istaff,ustaff,weight) values('%d','%d','%d','%d')",$id,$whoami['id'],$whoami['id'],$id);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update tenant set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update tenant set %s where id='%d'",implode(",",$set),$id);
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

			$query = sprintf("select tenant.* from tenant where tenant.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$weight = $qo['weight'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$weight = 0;

			$refer = 0;
		}
?>
<p class="title1">テナント：編集
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
						<td width="2%" class="th-edit">名称</td>
						<td width="98%" class="td-edit"><label>
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="64" maxlength="128" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td class="td-edit"><label>
								<textarea name="remark" cols="64" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
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
//
		$query = sprintf("select count(*) from tenant");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$wmax = $qo['count']*2;
//
		$query = sprintf("select tenant.*,staff.nickname from tenant,staff where tenant.vf=true and tenant.ustaff=staff.id order by tenant.weight desc,tenant.name");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);

//Var_dump::display($query);

?>
<p class="title1">テナント <a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">表示順</td>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="95%" class="th-edit">店舗</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
?>
				<tr id="row[<?php printf("%d",$a); ?>]">
						<td class="td-edit"><label>
								<input name="next[<?php printf("%d",$a); ?>]" type="button" id="next[<?php printf("%d",$a); ?>]" onclick="swapRow(this.form,'<?php printf("%d",$a); ?>','N')" value="↓" />
								</label>
										<label>
												<input name="prev[<?php printf("%d",$a); ?>]" type="button" id="prev[<?php printf("%d",$a); ?>]" onclick="swapRow(this.form,'<?php printf("%d",$a); ?>','P')" value="↑" />
										</label>						<input name="idS[<?php printf("%d",$a); ?>]" type="hidden" id="idS[<?php printf("%d",$a); ?>]" value="<?php printf("%d",$qo['id']); ?>" />
						<input name="old[<?php printf("%d",$a); ?>]" type="hidden" id="old[<?php printf("%d",$a); ?>]" value="<?php printf("%d",$qo['id']); ?>" /></td>
						<td class="td-edit"><a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><select name="shop" id="shop" onchange="editMaster(this)">
								<?php
		$query = sprintf("select * from shop where vf=true and tenant='%d' order by name",$qo['id']);
		$nr = pg_query($handle,$query);
		$ns = pg_num_rows($nr);
?>
								<option value="0" selected="selected"><?php printf("%d",$ns); ?>店</option>
								<?php
	for($aa=0; $aa<$ns; $aa++){
		$no = pg_fetch_array($nr,$aa);
?>
								<option value="<?php printf("%d",$no['id']); ?>"><?php printf("%s",$no['name']); ?></option>
								<?php
	}
?>
						</select></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
				<tr>
						<td class="th-edit"><input name="update" type="submit" disabled="disabled" id="update" value="更新" />
										<input name="mode" type="hidden" id="mode" value="wsave" />
						<input name="length" type="hidden" id="length" value="<?php printf("%d",$qs); ?>" /></td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
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
</body>
</html>
