<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>category</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);

//	Var_Dump::display($whoami);

	pg_query($handle,"begin");
	pg_query($handle,"LOCK category IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from category");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into category(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("kana='%s'",pg_escape_string($_REQUEST['kana']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update category set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update category set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="category.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			function isRefer($handle,$id)
			{
				$table = array("brand");
				for($a=0,$b=count($table); $b--; $a++){
					$query = sprintf("select count(*) from %s where vf=true and '%d'=any(category)",$table[$a],$id);
					$qr = pg_query($handle,$query);
					$qo = pg_fetch_array($qr);
					if($qo['count']){
						return($qo['count']);
					}
				}
				return(0);
			}
			$query = sprintf("select category.* from category where category.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$kana = $qo['kana'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$kana = '';
			$remark = "";

			$refer = 0;
		}
?>
<p class="title1">カテゴリー：編集
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
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="16" maxlength="64" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">読み</td>
						<td class="td-edit"><label>
								<input name="kana" type="text" id="kana" value="<?php printf("%s",$kana); ?>" size="16" maxlength="64" />
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
?>
<p class="title1">カテゴリー <a href="category.php?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="2%" class="th-edit">読み</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	$query = sprintf("SELECT category.*,staff.nickname from category join staff on category.ustaff=staff.id where category.vf=true order by category.name");						
//Var_dump::display($query);

	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
?>
				<tr id="row[<?php printf("%d",$a); ?>]">
						<td class="td-edit"><a href="category.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['kana']); ?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
		</table>
</form><script language="JavaScript" type="text/javascript">
window.onload = function()
{
/*
	var a;
	var b;
	var length = document.list.length.value;
	var __t = 0;
	var __e = length-1;
	
	var top = document.getElementById('prev['+__t+']');
	var end = document.getElementById('next['+__e+']');

	top.className = 'notDisplay';
	end.className = 'notDisplay';
*/
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
