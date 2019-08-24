<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>narea</title>
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
		$query = sprintf("select count(*) from maker where vf=true and nation='%d'",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		return($qo['count']);
	}
	$whoami = getStaffInfo($handle);

//	Var_Dump::display($whoami);

	pg_query($handle,"begin");
	pg_query($handle,"LOCK nation IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from nation");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [%s]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into nation(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("kana='%s'",pg_escape_string($_REQUEST['kana']));
		$set[] = sprintf("code2='%s'",pg_escape_string($_REQUEST['code2']));
		$set[] = sprintf("code3='%s'",pg_escape_string($_REQUEST['code3']));
		$set[] = sprintf("currency='%d'",$_REQUEST['currency']);
		$set[] = sprintf("number='%d'",$_REQUEST['number']);
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update nation set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update nation set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d)=[%s]\n",$qr,$query); ?> --><?php
?>
<a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select nation.* from nation where nation.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
//Var_dump::display($qo);
			$name = $qo['name'];
			$kana = $qo['kana'];
			$code2= $qo['code2'];
			$code3= $qo['code3'];
			$currency = $qo['currency'];
			$narea = $qo['narea'];
			$remark = $qo['remark'];
			$number = $qo['number'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$kana = '';
			$code2= '';
			$code3= '';
			$currency = 0;
			$remark = "";
			$narea = 0;
			$number = 0;

			$refer = 0;
		}
?>
<p class="title1">国：編集
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
		<table width="57%">
				<tr>
						<td width="13%" class="th-edit">name</td>
						<td width="30%" class="td-edit"><label>
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="32" maxlength="128" />
						</label></td>
						<td width="11%" class="th-edit">読み</td>
						<td width="46%" class="td-edit"><input name="kana" type="text" id="kana" value="<?php printf("%s",$kana); ?>" size="32" maxlength="128" /></td>
				</tr>
				<tr>
						<td class="th-edit">番号・記号</td>
						<td class="td-edit"><label>
								番号
												<input name="number" type="text" id="number" value="<?php printf("%d",$number); ?>" size="3" maxlength="3" /> 
								3文字
								<input name="code3" type="text" id="code3" value="<?php printf("%s",$code3); ?>" size="3" maxlength="3" /></label>
						2文字
						<label><input name="code2" type="text" id="code2" value="<?php printf("%s",$code2); ?>" size="3" maxlength="3" />
						</label></td>
						<td class="th-edit">地域</td>
						<td class="td-edit"><select name="narea" id="narea">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select * from narea where vf=true order by weight desc");
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
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">通貨</td>
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
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit">&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td colspan="3" class="td-edit"><label>
								<textarea name="remark" cols="64" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td colspan="3" class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
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
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'nation.udate desc,nation.code3'),
			array('name'=>'名称','text'=>'nation.code3'),
			array('name'=>'地域','text'=>'narea.weight desc,nation.code3'),
			array('name'=>'Maker/Shipperの数','text'=>'ms desc,nation.code3'),
		);
		if(isset($_REQUEST['exec'])){
			$order = $_REQUEST['order'];
		}
		else{
			$order =0;
		}
?>
<p class="title1">国 <a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit">新規登録</a></p>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td width="7%" class="th-edit">並べ替え</td>
						<td width="64%" class="td-edit"><select name="order" id="order" onchange="this.form.submit()">
								<?php
	for($a=0,$b=count($__oList); $b--; $a++){
		$selected = sprintf("%s",$a==$order? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$__oList[$a]['name']); ?></option>
								<?php
	}
?>
						</select>
										<input name="exec" type="hidden" id="exec" value="on" /></td>
				</tr>
		</table>
</form>
<p></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">

				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">地域</td>
						<td width="95%" class="th-edit">CODE</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="95%" class="th-edit">通貨</td>
						<td width="95%" class="th-edit">Ms</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
				<?php
				$qq = array();
				$qq[] = sprintf("select nation.id,count(maker.id) as ms,narea.weight,narea.name as aname,nation.name,nation.code3,nation.kana,nation.udate,staff.nickname,currency.id as currency,currency.name as cname,currency.code as ccode");
				$qq[] = sprintf("from nation left join maker on maker.nation=nation.id join staff on nation.ustaff=staff.id join currency on nation.currency=currency.id join narea on nation.narea=narea.id");
				$qq[] = sprintf("where nation.vf=true");
//				$qq[] = sprintf("and maker.vf=true");
				$qq[] = sprintf("group by nation.id, narea.weight,narea.name,nation.name,nation.kana,nation.code3,nation.udate,staff.nickname,currency.id,currency.name,currency.code");
				$qq[] = sprintf("order by %s",$__oList[$order]['text']);
	$query = implode(" ",$qq);
	$nr = pg_query($handle,$query);
?><!-- <?php printf("Query(%d)=[%s]",$nr,$query); ?> --><?php
	$ns = pg_num_rows($nr);
//Var_dump::display($query);
	for($b=0; $b<$ns; $b++){
		$no = pg_fetch_array($nr,$b);
		$currency = $no['currency']? sprintf("%s",$no['ccode']):"";
?>
				<tr ms="<?php printf("%d",$no['ms']); ?>">
						<td class="td-edit"><a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit&amp;id=<?php printf("%d",$no['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$no['aname']); ?></td>
						<td class="td-edit"><?php printf("%s",$no['code3']); ?></td>
						<td class="td-edit"><span title="<?php printf("%s",$no['kana']); ?>"><?php printf("%s",$no['name']); ?></span></td>
						<td class="td-edit"><span title="<?php printf("%s",$no['cname']); ?>"><?php printf("%s",$currency); ?></span>&nbsp;</td>
						<td class="td-edit"><?php printf("%s",number_format($no['ms'])); ?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($no['udate']),$no['nickname']); ?></td>
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
		switch(elm[a].getAttribute("ms")){
			case '0':
				elm[a].className = 'notWin';
				break;
			default:
				break;
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
