<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>staff</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
<link href="../jscalendar/calendar-green.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
#staffList {
	position:relative;
	width:100%;
	height:480px;
	z-index:1;
	overflow: auto;
	visibility: inherit;
}
-->
</style>
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<script language="JavaScript" type="text/javascript" src="../jscalendar/calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="../jscalendar/calendar-setup.js"></script>
<script language="JavaScript" type="text/javascript" src="../jscalendar/lang/calendar-jp-utf8.js"></script>
<?php
	function isRefer($handle,$id)
	{
		$refer = 0;
		$table = array("shop","division","area","currency","brand","maker","exhibition","nation","narea","season","staff");	
		for($a=0,$b=count($table); $b--; $a++){
			$query = sprintf("select count(*) from %s where vf=true and (istaff='%d' or ustaff='%d')",$table[$a],$id,$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			if($refer = $qo['count']){
				break;
			}
		}
		return($refer);
	}

	pg_query($handle,"begin");
	pg_query($handle,"LOCK staff IN EXCLUSIVE MODE");

	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from staff");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into staff(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("nickname='%s'",pg_escape_string($_REQUEST['nickname']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
		$set[] = sprintf("account='%s'",pg_escape_string($_REQUEST['account']));
		$set[] = sprintf("password='%s'",pg_escape_string($_REQUEST['password']));
			$set[] = sprintf("md5='%s'",md5($_REQUEST['password']));
		$set[] = sprintf("sex='%s'",pg_escape_string($_REQUEST['sex']));
		$set[] = sprintf("bday='%s'",$_REQUEST['bday']);
//
		$set[] = sprintf("dset='{%s}'",isset($_REQUEST['dset'])? implode(",",$_REQUEST['dset']):"");
		$set[] = sprintf("aset='{%s}'",isset($_REQUEST['aset'])? implode(",",$_REQUEST['aset']):"");
//		$set[] = sprintf("bset='{%s}'",isset($_REQUEST['bset'])? implode(",",$_REQUEST['bset']):"");
		$set[] = sprintf("tset='{%s}'",isset($_REQUEST['tset'])? implode(",",$_REQUEST['tset']):"");

		$set[] = sprintf("dcheck='%s'",$_REQUEST['dcheck']);
		$set[] = sprintf("acheck='%s'",$_REQUEST['acheck']);
		$set[] = sprintf("tcheck='%s'",$_REQUEST['tcheck']);
		$set[] = sprintf("shop='%d'",$_REQUEST['shop']);
		$set[] = sprintf("alog=%s",isset($_REQUEST['alog'])? "true":"false");
//
		$perm = 0;
		if(isset($_REQUEST['perm'])){
			for($a=0,$b=count($_REQUEST['perm']); $b--; $a++){
				$perm|=$_REQUEST['perm'][$a];
			}
		}
		$set[] = sprintf("perm='%d'",$perm);
		for($attribute=0,$a=0,$b=count($_REQUEST['attribute']); $b--; $a++){
			$attribute|=$_REQUEST['attribute'][$a];
		}		
		$set[] = sprintf("attribute='%d'",$attribute);
//		
		for($a=0; $a<2; $a++){
			$set[] = sprintf("name[%d]='%s'",$a+1,pg_escape_string($_REQUEST['name'][$a]));
			$set[] = sprintf("kana[%d]='%s'",$a+1,pg_escape_string($_REQUEST['kana'][$a]));
			$set[] = sprintf("mail[%d]='%s'",$a+1,pg_escape_string($_REQUEST['mail'][$a]));
			$set[] = sprintf("callme[%d]='%s'",$a+1,pg_escape_string($_REQUEST['callme'][$a]));
		}
		$set[] = sprintf("rome='%s'",pg_escape_string($_REQUEST['rome']));

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update staff set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update staff set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="staff.php">もどる</a>
<script language="JavaScript" type="text/javascript">
window.onload = function()
{
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select staff.* from staff where staff.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$nickname = $qo['nickname'];
			$remark = $qo['remark'];
			$name = getPGSQLarray($qo['name']);
			$kana = getPGSQLarray($qo['kana']);
			$account = $qo['account'];
			$password = $qo['password'];
			$sex = $qo['sex'];
			$bday = $qo['bday'];
			$dset = getPGSQLarray($qo['dset']);
			$aset = getPGSQLarray($qo['aset']);
			$perm = $qo['perm'];
			$attribute = $qo['attribute'];
			$dcheck = $qo['dcheck'];
			$acheck = $qo['acheck'];
			$mail = getPGSQLarray($qo['mail']);
//			$bset = getPGSQLarray($qo['bset']);
			$tset = getPGSQLarray($qo['tset']);
			$tcheck = $qo['tcheck'];
			$shop = $qo['shop'];
			$alog = $qo['alog'];
			$rome = $qo['rome'];
			$callme = getPGSQLarray($qo['callme']);

			$refer = isRefer($handle,$qo['id']);
		}
		else{
			$id = 0;
			$nickname = '';
			$remark = "";
			$account = "";
			$password = "";
			$name = array('','');
			$kana = array('','');
			$sex = 't';
			$bday = date("Y-m-d");
			$dset = array();
			$aset = array();
			$perm =0;
			$attribute = 0;
			$dcheck = 't';
			$acheck = 'f';
			$mail = array();
//			$bset = array();
			$tset = array();
			$tcheck = 'f';
			$shop = 0;
			$alog = 't';
			$rome = '';
			$callme = array('','');

			$refer = 0;
		}
		if(count($mail) == 0){
			$mail[0] = $mail[1] = '';
		}
?>
<p class="title1">HPFスタッフ：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var elm;
	var sets;
	var mes = new Array();
	var a;
	var b;
	var err = 0;
// Ajax! Ajax! Ajax!
<?php
	$query = sprintf("select account from staff where vf=true and id<>%d",$id);
?>
	var ooo = new Ajax.Request('../query.php',{method:'get',asynchronous:false,parameters:'?query=<?php printf("%s",$query); ?>'});
	var account = explode('\n',ooo.transport.responseText);
// Ajax! Ajax! Ajax!

	
/*
	for(sets=0,elm=F.elements['dset[]'],a=0,b=elm.length; b--; a++){
		if(elm[a].checked == true){
			sets++;
		}
	}
	if(sets==0){
		mes[err++] = "事業部が選択されていません";
	}
//
	for(sets=0,elm=F.elements['aset[]'],a=0,b=elm.length; b--; a++){
		if(elm[a].checked == true){
			sets++;
		}
	}
	if(sets==0){
		mes[err++] = "エリアが選択されていません";
	}
*/
	if(F.elements['nickname'].value==''){
		mes[err++] = "ハンドルは必須です";
	}
	for(a=0; a<2; a++){
		if(F.elements['name['+a+']'].value==''){
			mes[err++] = "名前は必須です";
			break;
		}
	}
	for(a=0; a<2; a++){
		if(F.elements['kana['+a+']'].value==''){
			mes[err++] = "読みは必須です";
			break;
		}
	}
	if(F.elements['account'].value==''){
		mes[err++] = "アカウントは必須です";
	}
	else if(in_array(F.elements['account'].value,account)){
		mes[err++] = "アカウントが他で使用されています";
	}
	if(F.elements['password'].value==''){
		mes[err++] = "パスワードは必須です";
	}
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
		<script language="JavaScript" type="text/javascript">
function alertShop(F)
{
	var offset;
	if((offset=F.elements['shop'].value)!=0){
		var si = F.elements['shop'].options.selectedIndex;
		var mes = array(
			sprintf("このスタッフは以後%sの従業員として扱われ、",F.elements['shop'].options[si].text),
			'日報入力とレポート閲覧以外の全ての機能・権限が無効となります。よろしいですか?'
		);
		if(confirm(mes.join('\n'))){
			var si = F.elements['shop'].options.selectedIndex;
			var division = F.elements['shop'].options[si].getAttribute('division');
			var area = F.elements['shop'].options[si].getAttribute('area');

			var elm = F.elements['dset[]'];
			for(a=0,b=elm.length; b--; a++){
				if(elm[a].value==division){
					elm[a].checked = true;
				}
			}
			var elm = F.elements['aset[]'];
			for(a=0,b=elm.length; b--; a++){
				if(elm[a].value==area){
					elm[a].checked = true;
				}
			}
			
			var elm = F.elements['perm[]'];
			for(a=0,b=elm.length; b--; a++){
				elm[a].checked = false;
				elm[a].disabled = true;
			}
		}
		else{
			F.elements['shop'].options.selectedIndex = 0;
		}
	}
}
		</script>
</p>
* 事業部 = レポートの際の対象事業部<br />
* エリア = レポートの際の対象エリア <br />
<br />
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="34%">
				<tr>
						<td width="2%" class="th-edit">ハンドル</td>
						<td width="98%" class="td-edit"><label>
								<input name="nickname" type="text" id="nickname" value="<?php printf("%s",$nickname); ?>" size="32" maxlength="128" />
						</label></td>
						<td width="98%" class="th-edit">アカウント</td>
						<td width="98%" class="td-edit"><input name="account" type="text" id="account" value="<?php printf("%s",$account); ?>" size="8" maxlength="8" />
						パスワード
						<input name="password" type="password" id="password" value="<?php printf("%s",$password); ?>" size="8" maxlength="8" /></td>
				</tr>
				<tr>
						<td class="th-edit">名前</td>
						<td class="td-edit">姓
								<label>
								<input name="name[0]" type="text" id="name[0]" value="<?php printf("%s",$name[0]); ?>" size="16" maxlength="64" />
								</label>
						名								<label>
								<input name="name[1]" type="text" id="name[1]" value="<?php printf("%s",$name[1]); ?>" size="16" maxlength="64" />
								</label></td>
						<td class="th-edit">読み</td>
						<td class="td-edit">姓
								<label>
								<input name="kana[0]" type="text" id="kana[0]" value="<?php printf("%s",$kana[0]); ?>" size="16" maxlength="64" />
								</label>
名
<label>
<input name="kana[1]" type="text" id="kana[1]" value="<?php printf("%s",$kana[1]); ?>" size="16" maxlength="64" />
</label></td>
				</tr>
				<tr>
						<td class="th-edit">名前の英語表記</td>
						<td class="td-edit"><label>
								<input name="rome" type="text" id="rome" value="<?php printf("%s",$rome); ?>" size="48" maxlength="128" />
						</label></td>
						<td class="th-edit">連絡先</td>
						<td class="td-edit">TEL 
						<input name="callme[0]" type="text" id="callme[0]" value="<?php printf("%s",$callme[0]); ?>" size="18" maxlength="64" /> 
						FAX
						<input name="callme[1]" type="text" id="callme[1]" value="<?php printf("%s",$callme[1]); ?>" size="18" maxlength="64" /></td>
				</tr>
				<tr>
						<td class="th-edit">メールアドレス(PC)</td>
						<td class="td-edit"><label>
								<input name="mail[0]" type="text" id="mail[0]" value="<?php printf("%s",$mail[0]); ?>" size="48" maxlength="256" />
						</label></td>
						<td class="th-edit">メールアドレス(携帯)</td>
						<td class="td-edit"><input name="mail[1]" type="text" id="mail[1]" value="<?php printf("%s",$mail[1]); ?>" size="48" maxlength="256" /></td>
				</tr>
				<tr>
						<td class="th-edit">性別</td>
						<td class="td-edit"><span class="td-nowrap">
								<label><input name="sex" type="radio" value="true"<?php printf("%s",$sex=='t'? $__XHTMLchecked:""); ?> />
男性</label>
<label><input name="sex" type="radio" value="false"<?php printf("%s",$sex!='t'? $__XHTMLchecked:""); ?> />
女性</label></span></td>
						<td class="th-edit">生年月日</td>
						<td class="td-edit"><span class="td-nowrap">
								<label>
<input name="bday" type="text" id="bday" value="<?php printf("%s",$bday); ?>" size="10" maxlength="10" readonly="true" />
</label>
								<label>
								<input type="button" name="bcal" id="bcal" value="カレンダーを開く" />
								</label>
						</span></td>
				</tr>
				<tr>
						<td class="th-edit">権限</td>
						<td class="td-edit"><?php
for($a=0,$b=count($__dPerm); $b--; $a++){
	$checked = sprintf("%s",($perm&$__dPerm[$a]['mask'])? $__XHTMLchecked:"");
?>
								<label>
								<input <?php printf("%s",$checked); ?> name="perm[]" type="checkbox" id="perm[]" value="<?php printf("%d",$__dPerm[$a]['mask']); ?>" />
								<?php printf("%s",$__dPerm[$a]['name']); ?><br />
								</label>
								<?php
}
?></td>
						<td class="th-edit">属性(experimental)</td>
						<td class="td-edit"><?php
for($a=0,$b=count($__dAttr); $b--; $a++){
	$checked = sprintf("%s",($attribute&$__dAttr[$a]['mask'])? $__XHTMLchecked:"");
?>
								<label>
								<input <?php printf("%s",$checked); ?> name="attribute[]" type="checkbox" id="attribute[]" value="<?php printf("%d",$__dAttr[$a]['mask']); ?>" />
								<?php printf("%s",$__dAttr[$a]['name']); ?><br />
								</label>
								<?php
}
?></td>
				</tr>
				<tr>
						<td class="th-edit">店舗</td>
						<td class="td-edit"><label><select name="shop" id="shop" onchange="alertShop(this.form)">
								<option value="0">---</option>
								<?php
		$query = sprintf("select shop.*,division.id as did,area.id as aid from shop,division,area where shop.vf=true and shop.division=division.id and shop.area=area.id order by division.weight desc,area.weight desc,shop.name");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$shop? $__XHTMLselected:"");
			$dName=getDivisionName($handle,$qo['did']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>" division="<?php printf("%d",$qo['did']); ?>" area="<?php printf("%d",$qo['aid']); ?>"><?php printf("%s (%s)",$qo['name'],$dName); ?></option>
								<?php
		}
?>
						</select></label></td>
						<td class="th-edit">アクセスログ</td>
						<td class="td-edit"><label>
<?php
			$checked = sprintf("%s",$alog=='t'? $__XHTMLchecked:"");
?>
								<input <?php printf("%s",$checked); ?> name="alog" type="checkbox" id="alog" value="t" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">事業部 初期状態</td>
						<td class="td-edit"><label>
								<label><input <?php printf("%s",$dcheck=='t'? $__XHTMLchecked:""); ?> name="dcheck" type="radio" id="dcheck" value="t" />選択</label>
						<label><input <?php printf("%s",$dcheck!='t'? $__XHTMLchecked:""); ?> name="dcheck" type="radio" id="dcheck" value="f" />
非選択</label></td>
						<td class="th-edit">エリア 初期状態</td>
						<td class="td-edit"><label><input <?php printf("%s",$acheck=='t'? $__XHTMLchecked:""); ?> name="acheck" type="radio" id="acheck" value="t" />選択</label>
		<label><input <?php printf("%s",$acheck!='t'? $__XHTMLchecked:""); ?> name="acheck" type="radio" id="acheck" value="f" />非選択</label></td>
				</tr>
				<tr>
						<td class="th-edit">事業部</td>
						<td class="td-edit"><?php
		$query = sprintf("select * from division where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$checked = sprintf("%s",in_array($qo['id'],$dset)? $__XHTMLchecked:"");
			$dName=getDivisionName($handle,$qo['id']);
?>
								<label><input <?php printf("%s",$checked); ?> name="dset[]" type="checkbox" id="dset[]" value="<?php printf("%d",$qo['id']); ?>" />
								<?php printf("%s",$dName); ?> 以下</label><br />
								<?php
		}
?></td>
						<td class="th-edit">エリア</td>
						<td class="td-edit"><?php
		$query = sprintf("select * from area where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$checked = sprintf("%s",in_array($qo['id'],$aset)? $__XHTMLchecked:"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<label><input <?php printf("%s",$checked); ?> name="aset[]" type="checkbox" id="aset[]" value="<?php printf("%d",$qo['id']); ?>" />
								<?php printf("%s",$dName); ?> 以下</label><br />
								<?php
		}
?></td>
				</tr>
				<tr>
						<td class="th-edit">テナント</td>
						<td class="td-edit"><?php
		$query = sprintf("select * from tenant where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$checked = sprintf("%s",in_array($qo['id'],$tset)? $__XHTMLchecked:"");
			$tName=$qo['name'];
?>
								<label>
								<input <?php printf("%s",$checked); ?> name="tset[]" type="checkbox" id="tset[]" value="<?php printf("%d",$qo['id']); ?>" />
								<?php printf("%s",$tName); ?></label>
								<br />
								<?php
		}
?></td>
						<td class="th-edit">テナント 初期状態</td>
						<td class="td-edit"><label>
								<input <?php printf("%s",$tcheck=='t'? $__XHTMLchecked:""); ?> name="tcheck" type="radio" id="tcheck" value="t" />
								選択</label>
								<label>
								<input <?php printf("%s",$tcheck!='t'? $__XHTMLchecked:""); ?> name="tcheck" type="radio" id="tcheck" value="f" />
非選択</label></td>
				</tr>
<!--
				<tr>
						<td class="th-edit">取扱ブランド(ex)</td>
						<td class="td-edit"><?php
	$query = sprintf("select brand.* from brand where brand.vf=true and brand.exclusive=true order by brand.weight desc,brand.name");
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$checked = sprintf("%s",in_array($qo['id'],$bset)? $__XHTMLchecked:"");
?>
										<label>
										<input name="bset[]" type="checkbox" id="bset[]" value="<?php printf("%d",$qo['id']); ?>" <?php printf("%s",$checked);?> />
										<?php printf("%s",$qo['name']); ?></label>
										<br />
										<?php
	}
?>
						</td>
						<td class="th-edit">取扱ブランド(並)</td>
						<td class="td-edit"><?php
	$query = sprintf("select brand.* from brand where brand.vf=true and brand.exclusive=false order by brand.weight desc,brand.name");
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$checked = sprintf("%s",in_array($qo['id'],$bset)? $__XHTMLchecked:"");
?>
										<label>
										<input name="bset[]" type="checkbox" id="bset[]" value="<?php printf("%d",$qo['id']); ?>" <?php printf("%s",$checked);?> />
										<?php printf("%s",$qo['name']); ?></label>
										<br />
										<?php
	}
?></td>
				</tr>
-->
				<tr>
						<td class="th-edit">備考</td>
						<td class="td-edit"><label>
								<textarea name="remark" cols="48" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
						</label></td>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit">&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
								<input name="mode" type="hidden" id="mode" value="save" />
								<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
								<span id="void">
								<input name="delete" type="checkbox" id="delete" value="t" />
削除する</span></td>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit">&nbsp;</td>
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
	else{
	}
	editPrepare('edit','exec'); // in common.js
	Calendar.setup({inputField:"bday",ifFormat:"%Y-%m-%d",button:"bcal",eventName:"click"});
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$__stList = array(
			array('name'=>'全て','text'=>''),
			array('name'=>'店舗スタッフのみ','text'=>'S'),
			array('name'=>'社内スタッフのみ','text'=>'C'),
		);
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'staff.udate desc'),
			array('name'=>'ハンドル','text'=>'staff.nickname'),
			array('name'=>'アカウント','text'=>'staff.account'),
			array('name'=>'初回登録日時','text'=>'staff.idate desc'),
		);

		if(isset($_REQUEST['exec'])){
			$dset = $_REQUEST['dset'];
			$aset = $_REQUEST['aset'];
			$perm = $_REQUEST['perm'];
			$attribute = $_REQUEST['attribute'];
			$order = $_REQUEST['order'];
			$kw = $_REQUEST['kw'];
			$_SESSION['staff']=array('dset'=>$dset,'aset'=>$aset,'perm'=>$perm,'attribute'=>$attribute,'kw'=>$kw,'order'=>$order);
		}
		else if(isset($_SESSION['staff'])){
			$dset = $_SESSION['staff']['dset'];
			$aset = $_SESSION['staff']['aset'];
			$perm = $_SESSION['staff']['perm'];
			$attribute = $_SESSION['staff']['attribute'];
			$kw = $_SESSION['staff']['kw'];
			$order = $_SESSION['staff']['order'];
		}
		else{
			$dset = 0;
			$aset = 0;
			$perm = 0;
			$attribute = 0;
			$order =0;
			$kw = "";
		}
?>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">事業部</td>
						<td width="24%" class="td-edit"><label>
								<select name="dset" id="dset" onchange="this.form.submit()">
										<option value="0">-- 全て --</option>
										<?php
		$ddd = array();
		$query = sprintf("select dset from staff where vf=true");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$ddd = array_merge($ddd,getPGSQLarray($qo['dset']));
		}
		
		$query = sprintf("select * from division where vf=true and id in (%s) order by weight desc",implode(",",$ddd));
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$dset? $__XHTMLselected:"");
			$dName=getDivisionName($handle,$qo['id']);
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
										<?php
		}
?>
								</select>
						</label></td>
						<td width="7%" class="th-edit">エリア</td>
						<td width="64%" class="td-edit"><select name="aset" id="aset" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select aset from staff where vf=true");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$ddd = array_merge($ddd,getPGSQLarray($qo['aset']));
		}
		
		$query = sprintf("select * from area where vf=true and id in (%s) order by weight desc",implode(",",$ddd));
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$aset? $__XHTMLselected:"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">権限</td>
						<td class="td-edit"><select name="perm" id="perm" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		for($a=0,$b=count($__dPerm); $b--; $a++){
			$selected = sprintf("%s",$__dPerm[$a]['mask']==$perm? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$__dPerm[$a]['mask']); ?>"><?php printf("%s",$__dPerm[$a]['name']); ?></option>
								<?php
		}
?>
						</select></td>
						<td class="th-edit">属性</td>
						<td class="td-edit"><select name="attribute" id="attribute" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		for($a=0,$b=count($__dAttr); $b--; $a++){
			$selected = sprintf("%s",$__dAttr[$a]['mask']==$attribute? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$__dAttr[$a]['mask']); ?>"><?php printf("%s",$__dAttr[$a]['name']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">キーワード</td>
						<td class="td-edit"><label></label>
								アカウント/ハンドル/本名に
										<input name="kw" type="text" id="kw" value="<?php printf("%s",$kw); ?>" size="16" maxlength="64" />
を含む</td>
						<td class="th-edit">並べ替え</td>
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
								<input type="submit" name="go" id="go" value="更新" />
						<input name="exec" type="hidden" id="exec" value="on" /></td>
				</tr>
		</table>
</form>
<?php
		$qq = array();
		$qq[] = sprintf("select staff.*,shop.name as shopname");
		$qq[] = sprintf("from staff,division,shop");
		$qq[] = sprintf("where staff.vf=true");
		$qq[] = sprintf("and staff.division=division.id");
		$qq[] = sprintf("and staff.shop=shop.id");
		if($dset){
			$qq[] = sprintf("and '%d'=any(staff.dset)",$dset);
		}
		if($aset){
			$qq[] = sprintf("and '%d'=any(staff.aset)",$aset);
		}
		if($perm){
			$qq[] = sprintf("and staff.perm&%d<>0",$perm);
		}
		if($attribute){
			$qq[] = sprintf("and staff.attribute&%d<>0",$attribute);
		}
		if($kw){
			$kw = strtoupper($kw);
			$dst = array('staff.name[1]','staff.name[2]','staff.kana[1]','staff.kana[2]','staff.nickname','staff.account');
			$ooo = array();
			for($a=0,$b=count($dst); $b--; $a++){
				$ooo[] = sprintf("upper(%s) like '%%%s%%'",$dst[$a],$kw);
			}
			$qq[] = sprintf("and (%s)",implode(" or ",$ooo));
		}
		$qq[] = sprintf("order by %s",$__oList[$order]['text']);
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><p class="title1">HPFスタッフ(<?php printf("%s",number_format($qs)); ?>名) <a href="staff.php?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">アカウント</td>
						<td width="2%" class="th-edit">ハンドル</td>
						<td width="95%" class="th-edit">権限</td>
						<td width="95%" class="th-edit">属性</td>
						<td width="2%" class="th-edit">事業部</td>
						<td width="2%" class="th-edit">エリア</td>
						<td width="95%" class="th-edit">テナント</td>
				</tr>
<?php
//								
	$dMaster = array();
	$query = sprintf("select id,name from division where vf=true order by id");							
	$sr = pg_query($handle,$query);
	$ss = pg_num_rows($sr);
	for($a=0; $a<$ss; $a++){
		$so = pg_fetch_array($sr,$a);
		$dMaster[$so['id']] = $so['name'];
	}
	$aMaster = array();
	$query = sprintf("select id,name from area where vf=true order by id");							
	$sr = pg_query($handle,$query);
	$ss = pg_num_rows($sr);
	for($a=0; $a<$ss; $a++){
		$so = pg_fetch_array($sr,$a);
		$aMaster[$so['id']] = $so['name'];
	}
//								
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$us = getStaffInfo($handle,$qo['ustaff']);

//Var_dump::display($qo);

		$name = getPGSQLarray($qo['name']);
		$kana = getPGSQLarray($qo['kana']);
		$mail = getPGSQLarray($qo['mail']);
		$mref = $mail? sprintf("mailto:%s",$mail[0]):"javascript:void(0)";


		$dset = getPGSQLarray($qo['dset']);
		$aset = getPGSQLarray($qo['aset']);
		$tset = getPGSQLarray($qo['tset']);
?>
				<tr shop="<?php printf("%d",$qo['shop']) ?>" title="<?php printf("最終更新日時 %s by %s",ts2JP($qo['udate']),$us['nickname']); ?>">
						<td class="td-edit"><a href="staff.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['account']); ?></td>
						<td class="td-edit"><span title="<?php printf("%s",$qo['nickname']); ?>"><?php printf("%s",cutoffStr($qo['nickname'])); ?></span> <a href="<?php printf("%s",$mref); ?>"><img type="mail" address="<?php printf("%s",$mail[0]); ?>" src="../images/mail_generic.png" title="<?php printf("%s",$mail[0]); ?>" width="16" height="16" border="0" class="notDisplay" /></a></td>
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
?>
								　						</td>
						<td class="td-edit"><?php
for($aa=0,$bb=count($__dAttr); $bb--; $aa++){
	$attr = $__dAttr[$aa];
	$icon = sprintf("../images/%s",$attr['icon']);
	$alt = $attr['name'];
	$show = $attr['mask']&$qo['attribute'];
?>
										<img type="perm" src="<?php printf("%s",$icon); ?>" alt="" width="16" height="16" border="0" class="notDisplay" title="<?php printf("%s",$alt); ?>" show="<?php printf("%d",$show); ?>" />
										<?php
}
?>						</td>
						<td class="td-edit"><select name="division" id="division" onchange="editMaster(this)">
								<option value="0" selected="selected"><?php printf("%d",count($dset)); ?></option>
								<?php
	for($aa=0,$bb=count($dset); $bb--; $aa++){
		$value = $dset[$aa];
//		$text = getDivisionName($handle,$value);
		$text = $dMaster[$value];
?>
								<option value="<?php printf("%d",$value); ?>"><?php printf("%s",$text); ?></option>
								<?php
	}
?>
						</select></td>
						<td class="td-edit"><select name="area" id="area" onchange="editMaster(this)">
								<option value="0" selected="selected"><?php printf("%d",count($aset)); ?></option>
								<?php
	for($aa=0,$bb=count($aset); $bb--; $aa++){
		$value = $aset[$aa];
//		$text = getAreaName($handle,$value);
		$text = $aMaster[$value];
?>
								<option value="<?php printf("%d",$value); ?>"><?php printf("%s",$text); ?></option>
								<?php
	}
?>
						</select></td>
						<td class="td-edit"><select name="tenant" id="tenant" onchange="editMaster(this)">
								<option value="0" selected="selected"><?php printf("%d",count($tset)); ?></option>
								<?php
	for($aa=0,$bb=count($tset); $bb--; $aa++){
		$value = $tset[$aa];
		$query = sprintf("select * from tenant where id='%d'",$value);
		$tr = pg_query($handle,$query);
		$to = pg_fetch_array($tr);
		$text = $to['name'];
?>
								<option value="<?php printf("%d",$value); ?>"><?php printf("%s",$text); ?></option>
								<?php
	}
?>
						</select></td>
				</tr>
<?php
	}
?>
		</table>
</form>
<script language="JavaScript" type="text/javascript">
window.onload = function()
{
	var a;
	var b;
	var elm = document.getElementsByTagName('TR');
	for(a=0,b=elm.length; b--; a++){
		switch(elm[a].getAttribute('shop')){
			case '0':
			case null:
				break;
			default:
				elm[a].style.backgroundColor = '#FFCCFF';
				break;
		}
	}
//
	var elm = document.getElementsByTagName('IMG');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("type")=='mail' && elm[a].getAttribute("address")!=''){
			elm[a].className = 'setDisplay';
		}
		if(elm[a].getAttribute("type")=='perm' && elm[a].getAttribute('show')!=0){
			elm[a].className = 'setDisplay';
		}
	}
//
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	}
?>
</body>
</html>
<?php
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
