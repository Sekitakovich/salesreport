<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>currency</title>
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
	pg_query($handle,"LOCK currency IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from currency");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into currency(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("denomination='%s'",pg_escape_string($_REQUEST['denomination']));
		$set[] = sprintf("code='%s'",pg_escape_string($_REQUEST['code']));
		$set[] = sprintf("number='%d'",$_REQUEST['number']);
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("rate='%f'",$_REQUEST['rate']);
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update currency set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update currency set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="currency.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			function isRefer($handle,$id)
			{
				$table = array("nation","shop");
				for($a=0,$b=count($table); $b--; $a++){
					$query = sprintf("select count(*) from %s where vf=true and currency='%d'",$table[$a],$id);
					$qr = pg_query($handle,$query);
					$qo = pg_fetch_array($qr);
					if($qo['count']){
						return($qo['count']);
					}
				}
				return(0);
			}
			$query = sprintf("select currency.* from currency where currency.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$weight = $qo['weight'];
			$rate = $qo['rate'];
			$code = $qo['code'];
			$number = $qo['number'];
			$denomination = $qo['denomination'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$weight = 0;
			$rate = 0;
			$code = '';
			$number = 0;
			$denomination = '';

			$refer = 0;
		}
?>
<p class="title1">通貨：編集
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
						<td class="th-edit">名称 (ISO4217)</td>
						<td class="td-edit"><input name="denomination" type="text" id="denomination" value="<?php printf("%s",$denomination); ?>" size="32" maxlength="64" /></td>
				</tr>
				<tr>
						<td class="th-edit">Code (ISO4217)</td>
						<td class="td-edit"><input name="code" type="text" id="code" value="<?php printf("%s",$code); ?>" size="3" maxlength="6" /></td>
				</tr>
				<tr>
						<td class="th-edit">Number (ISO4217)</td>
						<td class="td-edit"><input name="number" type="text" id="number" value="<?php printf("%s",$number); ?>" size="3" maxlength="6" /></td>
				</tr>
				<tr>
						<td width="2%" class="th-edit">別称</td>
						<td width="98%" class="td-edit"><label>
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="32" maxlength="64" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">日本円換算レート</td>
						<td class="td-edit"><label>
								<input name="rate" type="text" class="input-Digit" id="rate" value="<?php printf("%.3f",$rate); ?>" size="10" maxlength="20" />
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
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'currency.udate desc'),
			array('name'=>'通貨コード','text'=>'currency.code'),
		);
		if(isset($_REQUEST['exec'])){
			$order = $_REQUEST['order'];
		}
		else{
			$order =0;
		}

		$qq = array();
		$qq[] = sprintf("SELECT currency.*,staff.nickname");
		$qq[] = sprintf("from currency join staff on currency.ustaff=staff.id");
		$qq[] = sprintf("where currency.vf=true");
		$qq[] = sprintf("order by %s",$__oList[$order]['text']);
		$query = implode(" ",$qq);
//Var_dump::display($query);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<p class="title1">通貨 <a href="currency.php?mode=edit">新規登録</a></p>
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
						<td width="2%" class="th-edit">Code</td>
						<td width="2%" class="th-edit">Number</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="1%" class="th-edit">レート(日本円換算)</td>
						<td width="95%" class="th-edit">B</td>
						<td width="95%" class="th-edit">M/S</td>
						<td width="95%" class="th-edit">N</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		
		$query = sprintf("select count(*) from brand where vf=true and currency='%d'",$qo['id']);
		$nr = pg_query($handle,$query);
		$no = pg_fetch_array($nr); $BS = $no['count'];
		$query = sprintf("select count(*) from maker where vf=true and currency='%d'",$qo['id']);
		$nr = pg_query($handle,$query);
		$no = pg_fetch_array($nr); $MS = $no['count'];
		$query = sprintf("select count(*) from nation where vf=true and currency='%d'",$qo['id']);
		$nr = pg_query($handle,$query);
		$no = pg_fetch_array($nr); $NS = $no['count'];
?>
				<tr ns="<?php printf("%d",$NS); ?>" ms="<?php printf("%d",$MS); ?>" bs="<?php printf("%d",$BS); ?>" rate="<?php printf("%f",$qo['rate']); ?>">
						<td class="td-edit"><a href="currency.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['code']); ?></td>
						<td class="td-edit"><?php printf("%d",$qo['number']); ?></td>
						<td class="td-edit"><span title="<?php printf("%s",$qo['name']); ?>"><?php printf("%s",cutoffStr($qo['name'])); ?></span></td>
						<td class="td-edit"><?php printf("× %s",number_format($qo['rate'],3)); ?></td>
						<td class="td-editDigit"><?php printf("%d",$BS); ?></td>
						<td class="td-editDigit"><?php printf("%d",$MS); ?></td>
						<td class="td-editDigit"><?php printf("%d",$NS); ?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
		</table>
</form><script language="JavaScript" type="text/javascript">
window.onload = function()
{
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	var notice = 0;
	for(a=0,b=elm.length; b--; a++){
		var ms = parseInt(elm[a].getAttribute("ms"));
		var bs = parseInt(elm[a].getAttribute("bs"));
		var ns = parseInt(elm[a].getAttribute("ns"));
		if(ms>0||bs>0||ns>0){
			var rate = parseFloat(elm[a].getAttribute("rate"));
			if(rate==0.0){
				elm[a].style.fontWeight = 'bold';
				notice++;
			}
			elm[a].style.background = '#FFCCFF';
		}
	}
	if(notice && <?php printf("%s",isset($_REQUEST['exec'])? "false":"true") ?>){
		alert(sprintf("非参照レコードで日本円換算レート未設定のものが%d行あります",notice));
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
