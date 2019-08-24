<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>estimate</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<?php
include("../hpfmaster.inc");

if($handle=pg_connect($pgconnect)){
	pg_query($handle,"LOCK estimate IN EXCLUSIVE MODE");
	function isRefer($handle,$id)
	{
		$query = sprintf("select count(*) from belink where estimate='%d'",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		return($qo['count']);
	}
	$whoami = getStaffInfo($handle);

//	Var_Dump::display($whoami);

	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from estimate");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into estimate(id,istaff,ustaff,weight) values('%d','%d','%d','%d')",$id,$whoami['id'],$whoami['id'],$id);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("stype='%d'",$_REQUEST['stype']);
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
		$set[] = sprintf("pays='%d'",$_REQUEST['pays']);

		for($a=0,$b=$_REQUEST['pays']; $b--; $a++){
			$set[] = sprintf("yoffset[%d]='%d'",$a+1,$_REQUEST['yoffset'][$a]);
			$set[] = sprintf("month[%d]='%d'",$a+1,$_REQUEST['month'][$a]);
			$set[] = sprintf("percentage[%d]='%d'",$a+1,$_REQUEST['percentage'][$a]);
		}		

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update estimate set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update estimate set %s where id='%d'",implode(",",$set),$id);
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

			$query = sprintf("select estimate.* from estimate where estimate.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$stype = $qo['stype'];
			$pays = $qo['pays'];
			$yoffset = getPGSQLarray($qo['yoffset']);
			$month = getPGSQLarray($qo['month']);
			$percentage = getPGSQLarray($qo['percentage']);
			$weight = $qo['weight'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$stype = 0;
			$pays = 1;
			$yoffset = array();
			$month = array();
			$percentage = array('100');
			$weight = 0;

			$refer = 0;
		}
?>
<p class="title1">支払パターン(資金繰り用予測)：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var a;
	var b;
	var mes = new Array();
	var err = 0;
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須です";
	}
	if(F.elements['stype'].value==''){
		mes[err++] = "stypeは必須です";
	}
//	ここからパターンのチェック
	var pays = parseInt(F.elements['pays'].value); // 回数
	var total = 0;
	for(a=0; a<pays; a++){
		var dst = sprintf("percentage[%d]",a);
		total += parseInt(F.elements[dst].value);
	}
	if(total<100){
		mes[err++] = "合計が100％に達していません";
	}
	else if(total>100){
		mes[err++] = "合計が100％を超えています";
	}
//	ここまで
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else if(F.elements['delete'].checked){
		return confirm('このレコードを削除しますか?');
	}
	else return confirm('この内容で登録してよろしいですか?');
}
		</script>
		<script type="text/javascript">
function resetPtable(F)
{
	var pays = parseInt(F.elements['pays'].value);
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute('elmtype')=='payment'){
			var offset = elm[a].getAttribute('offset');
			elm[a].className = (offset>=pays)? 'hideTR':'showTR';
		}
	}
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
						<td class="th-edit">stype</td>
						<td class="td-edit"><select name="stype" id="stype">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select * from stype where vf=true order by month desc");
		$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$stype? $__XHTMLselected:"");
?>
			<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['nickname'],$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">パターン</td>
						<td class="td-edit"><table width="38%">
								<tr>
										<td width="7%" class="th-edit"><select name="pays" id="pays" onchange="resetPtable(this.form)">
														<?php
		for($a=1; $a<=$__estMax; $a++){
			$selected = sprintf("%s",$a==$pays? $__XHTMLselected:"");
?>
														<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
														<?php
		}
?>
												</select>
												回</td>
										<td width="7%" class="th-edit">年度</td>
										<td width="93%" class="th-edit">月</td>
										<td width="93%" class="th-edit">割合(％)</td>
								</tr>
								<?php
	for($aa=0; $aa<$__estMax; $aa++){
?>
								<tr class="hideTR" elmtype="payment" offset="<?php printf("%d",$aa); ?>">
										<td class="td-editDigit"><?php printf("%d",$aa+1); ?></td>
										<td class="td-editDigit"><label>
												<select <?php printf("%s",$selected); ?> name="yoffset[<?php printf("%d",$aa); ?>]" id="yoffset[<?php printf("%d",$aa); ?>]">
<?php
	$ys = array(
		array('value'=>0,'name'=>'同年'),
		array('value'=>1,'name'=>'翌年'),
		array('value'=>2,'name'=>'翌々年'),
	);
	for($a=0,$b=count($ys); $b--; $a++){
		$selected = sprintf("%s",$ys[$a]['value']==$yoffset[$aa]? $__XHTMLselected:"");
?>
														<option value="<?php printf("%d",$ys[$a]['value']); ?>"><?php printf("%s",$ys[$a]['name']); ?></option>
<?php
	}
?>
												</select>
										</label></td>
										<td class="td-editDigit"><select name="month[<?php printf("%d",$aa); ?>]" id="month[<?php printf("%d",$aa); ?>]">
												<?php
	for($a=1; $a<=12; $a++){
		$selected = sprintf("%s",$a==$month[$aa]? $__XHTMLselected:"");
?>
												<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$a); ?></option>
												<?php
	}
?>
										</select></td>
										<td class="td-editDigit"><select name="percentage[<?php printf("%d",$aa); ?>]" id="percentage[<?php printf("%d",$aa); ?>]">
												<?php
	for($a=1,$b=100; $b--; $a++){
		$selected = sprintf("%s",$a==$percentage[$aa]? $__XHTMLselected:"");
?>
												<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$a); ?></option>
												<?php
	}
?>
										</select></td>
								</tr>
								<?php
	}
?>
								<tr>
										<td class="th-edit">&nbsp;</td>
										<td class="th-edit">計</td>
										<td class="th-editDigit">&nbsp;</td>
										<td class="th-editDigit">&nbsp;</td>
								</tr>
						</table></td>
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
	resetPtable(document.forms['edit']);
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
?>
<p class="title1">支払パターン(資金繰り用予測) <a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
<?php
		$query = sprintf("select * from stype where vf=true order by month desc");
		$sr = pg_query($handle,$query);
		$ss = pg_num_rows($sr);
		for($aa=0; $aa<$ss; $aa++){
			$so = pg_fetch_array($sr,$aa);
?>
<p class="title1"><?php printf("%s (%s)",$so['nickname'],$so['name']); ?></p>
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="95%" class="th-edit">回</td>
						<td width="95%" class="th-edit">brand</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	$query = sprintf("select estimate.*,staff.nickname from estimate join staff on estimate.ustaff=staff.id where estimate.vf=true and estimate.stype='%d' order by estimate.weight desc,estimate.name",$so['id']);
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
?>
				<tr id="row[<?php printf("%d",$a); ?>]">
						<td class="td-edit"><a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><?php printf("%d",$qo['pays']); ?></td>
						<td class="td-edit"><select name="brand" id="brand" onchange="editMaster(this)">
								<?php
		$query = sprintf("select brand.* from belink join brand on belink.brand=brand.id where belink.estimate='%d' and belink.stype='%d' order by brand.name",$qo['id'],$so['id']);
		$nr = pg_query($handle,$query);
		$ns = pg_num_rows($nr);
?>
								<option value="0" selected="selected"><?php printf("%d",$ns); ?></option>
								<?php
	for($cc=0; $cc<$ns; $cc++){
		$no = pg_fetch_array($nr,$cc);
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
		</table>
		<p></p>
<?php
}
?>
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
