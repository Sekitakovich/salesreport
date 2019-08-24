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
/*
		$query = sprintf("select count(*) from shop where vf=true and '%d'=any(brand)",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		return($qo['count']);
*/
		return(0);
	}
	$whoami = getStaffInfo($handle);

//	Var_Dump::display($whoami);

	pg_query($handle,"begin");
	pg_query($handle,"LOCK brand IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from brand");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into brand(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("kana='%s'",pg_escape_string($_REQUEST['kana']));
		$set[] = sprintf("exclusive='%s'",$_REQUEST['exclusive']);
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("division='{%s}'",implode(",",$_REQUEST['division']));
		$set[] = sprintf("nation='%d'",$_REQUEST['nation']);
		$set[] = sprintf("currency='%d'",$_REQUEST['currency']);
		$set[] = sprintf("maker='%d'",$_REQUEST['maker']);
		$set[] = sprintf("url='%s'",pg_escape_string($_REQUEST['url']));
		$set[] = sprintf("code='%s'",pg_escape_string($_REQUEST['code']));
		$set[] = sprintf("nickname='%s'",pg_escape_string($_REQUEST['nickname']));

		$set[] = sprintf("pic='%s'",pg_escape_string($_REQUEST['pic']));
		for($a=0; $a<2; $a++){
			$set[] = sprintf("mail[%d]='%s'",$a+1,pg_escape_string($_REQUEST['mail'][$a]));
			$set[] = sprintf("discount[%d]='%3.2f'",$a+1,$_REQUEST['discount'][$a]);
			$set[] = sprintf("discex[%d]='%s'",$a+1,$_REQUEST['discex'][$a]=='t'? "t":"f");
		}
		for($a=0; $a<3; $a++){
			$set[] = sprintf("callme[%d]='%s'",$a+1,pg_escape_string($_REQUEST['callme'][$a]));
		}
		$set[] = sprintf("category='{%s}'",implode(",",$_REQUEST['category']));
		$set[] = sprintf("clerk='{%s}'",implode(",",$_REQUEST['clerk']));

		$set[] = sprintf("pnote[1]='%s'",pg_escape_string($_REQUEST['_ps']));
		$set[] = sprintf("pnote[2]='%s'",pg_escape_string($_REQUEST['_pb']));

		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update brand set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update brand set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
// estimateをinsert/update
	for($s=0,$ss=$_REQUEST['ss']; $ss--; $s++){
		$eid = $_REQUEST['eid'][$s];
		if($eid==0){
			$query = sprintf("select max(id) from estimate");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$eid=$qo['max']+1;
			$query = sprintf("insert into estimate(id,istaff,ustaff) values('%d','%d','%d')",$eid,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("brand='%d'",$id);
		$set[] = sprintf("stype='%d'",$_REQUEST['stid'][$s]);
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
		$set[] = sprintf("pays='%d'",$_REQUEST['pays'][$s]);

		for($a=0,$b=$_REQUEST['pays'][$s]; $b--; $a++){
			$set[] = sprintf("yoffset[%d]='%d'",$a+1,$_REQUEST['yoffset'][$s][$a]);
			$set[] = sprintf("month[%d]='%d'",$a+1,$_REQUEST['month'][$s][$a]);
			$set[] = sprintf("percentage[%d]='%d'",$a+1,$_REQUEST['percentage'][$s][$a]);
			$set[] = sprintf("memo[%d]='%s'",$a+1,pg_escape_string($_REQUEST['memo'][$s][$a]));
		}		
		$query = sprintf("update estimate set %s where id='%d'",implode(",",$set),$eid);
		$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
	}
//
?>
<a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select brand.*,brand.pnote[1] as _ps,brand.pnote[2] as _pb from brand where brand.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$weight = $qo['weight'];
			$kana = $qo['kana'];
			$exclusive = $qo['exclusive'];
			$division = getPGSQLarray($qo['division']);
			$clerk = getPGSQLarray($qo['clerk']);
			$url = $qo['url'];
			$mail = getPGSQLarray($qo['mail']);
			$callme = getPGSQLarray($qo['callme']);
			$pic = $qo['pic'];
			$category = getPGSQLarray($qo['category']);
			$maker = $qo['maker'];
			$code = $qo['code'];
			$nickname = $qo['nickname'];
			$nation = $qo['nation'];
			$currency = $qo['currency'];
			$discex = getPGSQLarray($qo['discex']);
			
			$_ps = $qo['_ps'];
			$_pb = $qo['_pb'];
			$discount = getPGSQLarray($qo['discount']);

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$weight = 0;
			$kana = '';
			$exclusive = 'f';
			$division = array();
			$clerk = array();
			$url = '';
			$mail = array();
			$callme = array();
			$pic = '';
			$category = array();
			$maker = 0;
			$code = '';
			$nickname = '';
			$nation = 0;
			$currency = 0;
			$discex = array('t','t');

			$_ps = '';
			$_pb = '';
			$discount = array(0.0,0.0);

			$refer = 0;
		}
?>
<p class="title1">ブランド：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var a;
	var b;
	var mes = new Array();
	var err = 0;
//-----------------------------------------------------------------------------------
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須です";
	}
	if(F.elements['currency'].value==0){
		mes[err++] = "currencyは必須です";
	}
//	ここからパターンのチェック
	var ss = parseInt(F.elements['ss'].value);
	var s;
	for(s=0; s<ss; s++){
		var ooo = sprintf("pays[%d]",s);
		var ppp = sprintf("stname[%d]",s);
		var pays = parseInt(F.elements[ooo].value); // 回数
		var total = 0;
		for(a=0; a<pays; a++){
			var dst = sprintf("percentage[%d][%d]",s,a);
			var ooo = parseInt(F.elements[dst].value);
			total += ooo;
		}
		if(total<100){
			mes[err++] = sprintf("%sが100％に達していません",F.elements[ppp].value);
		}
		else if(total>100){
			mes[err++] = sprintf("%sが100％を超えています",F.elements[ppp].value);
		}
	}
//	ここまで
//-----------------------------------------------------------------------------------
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
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="32" maxlength="128" /></label>
						読み<label>		
						<input name="kana" type="text" id="kana" value="<?php printf("%s",$kana); ?>" size="32" maxlength="128" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">略称</td>
						<td class="td-edit"><input name="nickname" type="text" id="nickname" value="<?php printf("%s",$nickname); ?>" size="6" maxlength="6" /> 
								codenumber 
								<input name="code" type="text" id="code" value="<?php printf("%s",$code); ?>" size="8" maxlength="16" /></td>
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
								</select>
								&nbsp;</td>
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
						<td class="th-edit">Maker</td>
						<td class="td-edit"><select name="maker" id="maker">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select maker.*,nation.code3 from maker join nation on maker.nation=nation.id where nation.vf=true and maker.attribute&%d<>0 order by maker.name,nation.code3",CORP_ATTR_MAKER);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$maker? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['name'],$qo['code3']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">カテゴリー</td>
						<td class="td-edit">
<?php
	$query = sprintf("select * from category where vf=true order by name");
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$checked = sprintf("%s",in_array($qo['id'],$category)? $__XHTMLchecked:"");
?>
						<label><input name="category[]" type="checkbox" id="category[]" value="<?php printf("%d",$qo['id']); ?>" <?php printf("%s",$checked); ?> /><?php printf("%s (%s)",$qo['name'],$qo['kana']); ?>						</label><br />
<?php
	}
?>						</td>
				</tr>
				<tr>
						<td class="th-edit">URL</td>
						<td class="td-edit"><input name="url" type="text" id="url" value="<?php printf("%s",$url); ?>" size="64" maxlength="256" /></td>
				</tr>
				<tr>
						<td class="th-edit">PIC(person in charge)</td>
						<td class="td-edit"><input name="pic" type="text" id="pic" value="<?php printf("%s",$pic); ?>" size="64" maxlength="128" /></td>
				</tr>
				<tr>
						<td class="th-edit">連絡先</td>
						<td class="td-edit">TEL
								<input name="callme[0]" type="text" id="callme[0]" value="<?php printf("%s",$callme[0]); ?>" size="18" maxlength="64" />
FAX
<input name="callme[1]" type="text" id="callme[1]" value="<?php printf("%s",$callme[1]); ?>" size="18" maxlength="64" /> 
Moblie
<input name="callme[2]" type="text" id="callme[2]" value="<?php printf("%s",$callme[1]); ?>" size="18" maxlength="64" /></td>
				</tr>
				<tr>
						<td class="th-edit">メールアドレス</td>
						<td class="td-edit">PC
						<input name="mail[0]" type="text" id="mail[0]" value="<?php printf("%s",$mail[0]); ?>" size="32" maxlength="256" /> 
						Mobile
						<input name="mail[1]" type="text" id="mail[1]" value="<?php printf("%s",$mail[1]); ?>" size="32" maxlength="256" /></td>
				</tr>
				<tr>
						<td class="th-edit">Payment(Sample)</td>
						<td class="td-edit"><label>
								<input name="_ps" type="text" id="_ps" value="<?php printf("%s",$_ps); ?>" size="64" maxlength="128" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">Payment(Bulk)</td>
						<td class="td-edit"><label>
								<input name="_pb" type="text" id="_pb" value="<?php printf("%s",$_pb); ?>" size="64" maxlength="128" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">Discount(Sample)</td>
						<td class="td-edit"><label>
								<input name="discount[0]" type="text" id="discount[0]" value="<?php printf("%3.2f",$discount[0]); ?>" size="6" maxlength="6" />
						</label>
						<input name="discex[0]" type="checkbox" value="t" <?php printf("%s",$discex[0]=='t'? $__XHTMLchecked:""); ?> />
						excluded</td>
				</tr>
				<tr>
						<td class="th-edit">Discount(Bulk)</td>
						<td class="td-edit"><label>
								<input name="discount[1]" type="text" id="discount[1]" value="<?php printf("%3.2f",$discount[1]); ?>" size="6" maxlength="6" />
						</label>
								<input name="discex[1]" type="checkbox" value="t" <?php printf("%s",$discex[1]=='t'? $__XHTMLchecked:""); ?> />
excluded</td>
				</tr>
		</table>
		<p class="title1">HPF Side</p>
		<table>
<?php
	$query = sprintf("select stype.* from stype where stype.vf=true order by stype.month");
	$sr = pg_query($handle,$query);
	$ss = pg_num_rows($sr);
?>
				<tr>
						<td width="42" class="th-edit">stype								
								<input name="ss" type="hidden" id="ss" value="<?php printf("%d",$ss); ?>" />
</td>
						<td width="225" class="th-edit">schedule<span class="title1">
								<script type="text/javascript">
function resetPtable(F)
{
	var ss = parseInt(F.elements['ss'].value);
	var s;
	for(s=0; s<ss; s++){
		var ooo = sprintf("pays[%d]",s);
		var pays = parseInt(F.elements[ooo].value);
		var elm = document.getElementsByTagName('TR');
		var a;
		var b;
		for(a=0,b=elm.length; b--; a++){
			if(elm[a].getAttribute('elmtype')=='payment' && elm[a].getAttribute('s')==s){
				var offset = elm[a].getAttribute('offset');
				elm[a].className = (offset>=pays)? 'hideTR':'showTR';
			}
		}
	}
}
								</script>
						</span></td>
				</tr>
<?php
	for($s=0; $s<$ss; $s++){
		$so = pg_fetch_array($sr,$s);
//
		$query = sprintf("select estimate.* from estimate where estimate.vf=true and estimate.brand='%d' and estimate.stype='%d'",$id,$so['id']);
		$er = pg_query($handle,$query);
		$es = pg_num_rows($er);
		if($es){
			$eo = pg_fetch_array($er);
			$eid = $eo['id'];
			$pays = $eo['pays'];
			$yoffset = getPGSQLarray($eo['yoffset']);
			$month = getPGSQLarray($eo['month']);
			$percentage = getPGSQLarray($eo['percentage']);
			$memo = getPGSQLarray($eo['memo']);
		}
		else{
			$eid=0;
			$pays = 1;
			$yoffset = array('0');
			$month = array($so['month']);
			$percentage = array('100');
			$memo = array();
		}
		
//
?>
				<tr>
						<td class="td-edit"><?php printf("%s (%s)",$so['nickname'],$so['name']); ?>
								<input name="stname[<?php printf("%d",$s); ?>]" type="hidden" id="stname[<?php printf("%d",$s); ?>]" value="<?php printf("%s",$so['nickname']); ?>" />
								<input name="stid[<?php printf("%d",$s); ?>]" type="hidden" id="stid[<?php printf("%d",$s); ?>]" value="<?php printf("%d",$so['id']); ?>" />
						<input name="eid[<?php printf("%d",$s); ?>]" type="hidden" id="eid[<?php printf("%d",$s); ?>]" value="<?php printf("%d",$eid); ?>" /></td>
						<td class="td-edit"><table width="38%">
								<tr>
										<td width="7%" class="th-edit"><select name="pays[<?php printf("%d",$s); ?>]" id="pays[<?php printf("%d",$s); ?>]" onchange="resetPtable(this.form)">
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
										<td width="93%" class="th-edit">memo</td>
								</tr>
								<?php
	for($aa=0; $aa<$__estMax; $aa++){
?>
								<tr class="hideTR" elmtype="payment" offset="<?php printf("%d",$aa); ?>" s="<?php printf("%d",$s); ?>">
										<td class="td-editDigit"><?php printf("%d",$aa+1); ?></td>
										<td class="td-editDigit"><label>
												<select <?php printf("%s",$selected); ?> name="yoffset[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]" id="yoffset[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]">
														<?php
	$ys = array(
		array('value'=>'0','name'=>'同年'),
		array('value'=>'1','name'=>'翌年'),
		array('value'=>'2','name'=>'翌々年'),
	);
	for($a=0,$b=count($ys); $b--; $a++){
		$selected = sprintf("%s",$ys[$a]['value']==$yoffset[$aa]? $__XHTMLselected:"");
?>
														<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$ys[$a]['value']); ?>"><?php printf("%s",$ys[$a]['name']); ?></option>
														<?php
	}
?>
												</select>
										</label></td>
										<td class="td-editDigit"><select name="month[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]" id="month[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]">
														<?php
	for($a=1; $a<=12; $a++){
		$selected = sprintf("%s",$a==$month[$aa]? $__XHTMLselected:"");
?>
														<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$a); ?></option>
														<?php
	}
?>
										</select></td>
										<td class="td-editDigit"><select name="percentage[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]" id="percentage[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]">
														<?php
	for($a=1,$b=100; $b--; $a++){
		$selected = sprintf("%s",$a==$percentage[$aa]? $__XHTMLselected:"");
?>
														<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$a); ?></option>
														<?php
	}
?>
										</select></td>
										<td class="td-edit"><label>
												<input name="memo[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]" type="text" id="memo[<?php printf("%d",$s); ?>][<?php printf("%d",$aa); ?>]" value="<?php printf("%s",$memo[$aa]); ?>" size="64" maxlength="128" />
										</label></td>
								</tr>
								<?php
	}
?>

						</table></td>
				</tr>
<?php
	}
?>
		</table>
		<p>&nbsp;</p>
		<table>
				<tr>
						<td class="th-edit">担当者(Clerk)</td>
						<td class="td-edit"><?php
	$query = sprintf("select * from staff where vf=true and ((attribute&%d)=%d) order by nickname",ATTR_STAFF_CLERK,ATTR_STAFF_CLERK);
	$dr = pg_query($handle,$query);
	$ds = pg_num_rows($dr);
	for($aa=0; $aa<$ds; $aa++){
		$do = pg_fetch_array($dr,$aa);
		$did = $do['id'];
		$checked = sprintf("%s",in_array($did,$clerk)? $__XHTMLchecked:"");
?>
								<label>
								<input name="clerk[]" type="checkbox" id="clerk[]" value="<?php printf("%d",$did); ?>" <?php printf("%s",$checked); ?> />
								<?php printf("%s",$do['nickname']); ?></label>
								<br />
								<?php
	}
?></td>
				</tr>
				<tr>
						<td class="th-edit">事業部</td>
						<td class="td-edit">
<?php
	$query = sprintf("select * from division where vf=true order by weight desc");
	$dr = pg_query($handle,$query);
	$ds = pg_num_rows($dr);
	for($aa=0; $aa<$ds; $aa++){
		$do = pg_fetch_array($dr,$aa);
		$did = $do['id'];
		$fullname = getDivisionName($handle,$did);
		$checked = sprintf("%s",in_array($did,$division)? $__XHTMLchecked:"");
?>
						<label><input name="division[]" type="checkbox" id="division[]" value="<?php printf("%d",$did); ?>" <?php printf("%s",$checked); ?> />
						<?php printf("%s (%s)",$do['rome'],$fullname); ?></label><br />
<?php
	}
?>						</td>
				</tr>
				<tr>
						<td class="th-edit">レベル</td>
						<td class="td-edit"><label>
								<input name="exclusive" type="radio" id="exclusive" value="t" <?php printf("%s",$exclusive=='t'? $__XHTMLchecked:""); ?> />
								exclusive</label>
										<label>
												<input name="exclusive" type="radio" id="exclusive" value="f" <?php printf("%s",$exclusive!='t'? $__XHTMLchecked:""); ?> />
												並</label></td>
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
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'brand.udate desc,brand.name'),
			array('name'=>'ブランド名称','text'=>'brand.name'),
			array('name'=>'maker','text'=>'maker.name,brand.name'),
			array('name'=>'Clerk','text'=>'cstaff.name'),
			array('name'=>'narea','text'=>'narea.weight desc,nation.name,brand.name'),
			array('name'=>'nation','text'=>'nation.name,brand.name'),
		);

		if(isset($_REQUEST['exec'])){
			$clerk = $_REQUEST['clerk'];
			$kw = $_REQUEST['kw'];
			$nation = $_REQUEST['nation'];
			$narea = $_REQUEST['narea'];
			$currency = $_REQUEST['currency'];
			$order = $_REQUEST['order'];
			$category = $_REQUEST['category'];
			
			$_SESSION['brand'] = array(
				'clerk'=>$clerk,
				'kw'=>$kw,
				'nation'=>$nation,
				'narea'=>$narea,
				'currency'=>$currency,
				'category'=>$category,
				'order'=>$order
			);
		}
		else if(isset($_SESSION['brand'])){
			$bo = $_SESSION['brand'];
			$clerk = $bo['clerk'];
			$kw = $bo['kw'];
			$nation = $bo['nation'];
			$narea = $bo['narea'];
			$currency = $bo['currency'];
			$order = $bo['order'];
			$category = $bo['category'];
		}
		else{
			$clerk = 0;
			$kw = "";
			$nation =0;
			$currency =0;
			$narea = 0;
			$order = 0;
			$category = 0;
		}
?>
<p class="title1">ブランド <a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit">新規登録</a></p>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">clerk</td>
						<td width="24%" class="td-edit"><select name="clerk" id="clerk" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select * from staff where vf=true and ((attribute&%d)=%d) order by nickname",ATTR_STAFF_CLERK,ATTR_STAFF_CLERK);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$clerk? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$qo['nickname']); ?></option>
								<?php
		}
?>
						</select></td>
						<td width="7%" class="th-edit">category</td>
						<td width="64%" class="td-edit"><select name="category" id="category" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select category.* from category where category.vf=true order by category.name");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$category? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['name'],$qo['kana']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">area</td>
						<td class="td-edit"><select name="narea" id="narea" onchange="this.form.submit()">
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
						</select></td>
						<td class="th-edit">nation</td>
						<td class="td-edit"><select name="nation" id="nation" onchange="this.form.submit()">
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
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">currency</td>
						<td class="td-edit"><select name="currency" id="currency" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$cd = array();
		$ex = array();
//		$query = sprintf("SELECT currency.* from currency where currency.vf=true order by currency.code");
		$query = sprintf("SELECT currency.* from currency where currency.vf=true and id in (select distinct currency from brand where vf=true) order by currency.code");
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
						<td class="th-edit">キーワード</td>
						<td class="td-edit"><label></label>
										<label>名称等に
												<input name="kw" type="text" id="kw" value="<?php printf("%s",$kw); ?>" size="16" maxlength="64" />
												を含む</label></td>
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
										<input name="exec" type="hidden" id="exec" value="on" /></td>
				</tr>
		</table>
</form>
<?php
//	
	$qq = array();
	$qq[] = sprintf("select currency.code as ccode,nation.code3,nation.name as nname,nation.kana as nkana,maker.name as mname,maker.id as mid,brand.category,brand.id,brand.name,brand.nickname as nn,brand.code,brand.nickname as alias,brand.udate,ustaff.nickname");
	$qq[] = sprintf("from brand join currency on brand.currency=currency.id join nation on brand.nation=nation.id join maker on brand.maker=maker.id join staff as ustaff on brand.ustaff=ustaff.id");
	$qq[] = sprintf("where brand.vf=true");
	if($clerk){
		$qq[] = sprintf("and '%d'=any(brand.clerk)",$clerk);
	}
	if($narea){
		$qq[] = sprintf("and narea.id='%d'",$narea);
	}
	if($nation){
		$qq[] = sprintf("and nation.id='%d'",$nation);
	}
	if($kw){
		$kw = strtoupper($kw);
		$dst = array('brand.name','brand.kana','brand.nickname','maker.name');
		$ooo = array();
		for($a=0,$b=count($dst); $b--; $a++){
			$ooo[] = sprintf("upper(%s) like '%%%s%%'",$dst[$a],pg_escape_string($kw));
		}
		$ppp = implode(" or ",$ooo);
		$qq[] = sprintf("and %s",$ppp);
	}
	if($category){
		$qq[] = sprintf("and '%d'=any(brand.category)",$category);
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
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="95%" class="th-edit">NN</td>
						<td width="95%" class="th-edit">category</td>
						<td width="95%" class="th-edit">nation</td>
						<td width="95%" class="th-edit">通貨</td>
						<td width="95%" class="th-edit">maker</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	$ct = array();
	$query = sprintf("select * from category where vf=true");
	$cr = pg_query($handle,$query);
	$cs = pg_num_rows($cr);
	for($b=0; $b<$cs; $b++){
		$co = pg_fetch_array($cr,$b);
		$id = $co['id'];
		$name = $co['name'];
		$ct[$id] = $name;
	}

	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$ooo = getPGSQLarray($qo['category']);
		$c=count($ooo);
		if($c){
			$cn = array();
			for($b=0; $b<$c; $b++){
				$cn[] = sprintf("%s",$ct[$ooo[$b]]);
			}
			$category = implode("/",$cn);
		}
		else $category = '???';
//
?>
				<tr id="row[<?php printf("%d",$a); ?>]">
						<td class="td-edit"><a href="<?php printf("%s",$_SERVER['PHP_SELF']); ?>?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><span class="openURL" title="<?php printf("%s",$qo['name']); ?>" onclick="openGoogle('<?php printf("%s",rawurlencode($qo['name'])); ?>')"><?php printf("%s",cutoffStr($qo['name'])); ?></span>&nbsp;</td>
						<td class="td-edit"><span class="openURL"><?php printf("%s",$qo['nn']); ?></span>&nbsp;</td>
						<td class="td-edit"><?php printf("%s",$category); ?>&nbsp;</td>
						<td class="td-edit"><span title="<?php printf("%s - %s",$qo['nname'],$qo['nkana']); ?>"><?php printf("%s",$qo['code3']); ?></span>&nbsp;</td>
						<td class="td-edit"><?php printf("%s",$qo['ccode']); ?></td>
						<td class="td-edit"><span title="<?php printf("%s",$qo['mname']); ?>"><a href="maker.php?mode=edit&id=<?php printf("%d",$qo['mid']); ?>"><?php printf("%s",cutoffStr($qo['mname'])); ?></a></span>&nbsp;</td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
		</table>
</form>
<script type="text/javascript">
window.onload = function(){
//	fillEmptyCells();
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
