<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>purchase</title>
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
	pg_query($handle,"LOCK purchase IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from purchase");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into purchase(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("brand='%d'",$_REQUEST['brand']);
		$set[] = sprintf("type='%d'",$_REQUEST['type']);
		$set[] = sprintf("shipper='%d'",$_REQUEST['shipper']);
	$value = pg_escape_string(mb_convert_kana($_REQUEST['number'],"rnas"));
		$set[] = sprintf("number='%s'",$value);
		$set[] = sprintf("exhibition='%d'",$_REQUEST['exhibition']);
		$set[] = sprintf("pdate='%s'",implode("-",$_REQUEST['pdate']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("volume='%d'",$_REQUEST['volume']);
		$set[] = sprintf("price='%f'",$_REQUEST['price']);
		$set[] = sprintf("discount='%f'",$_REQUEST['discount']);
		$set[] = sprintf("amount='%f'",$_REQUEST['amount']);
		$set[] = sprintf("season='%d'",$_REQUEST['season']);
		
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update purchase set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update purchase set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
// 資料の追加
	$attachment = $_FILES['attachment'];
	if($size = $attachment['size']){
//		$name = $attachment['name'];
		$name = str_replace(" ","+",$attachment['name']); // 半角スペースは×
		$type = $attachment['type'];
		$body = file_get_contents($attachment['tmp_name']);
		$note = $_REQUEST['note'];
		$query = sprintf("select max(id) from attachment");
		$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		$qo = pg_fetch_array($qr);
		$aid=$qo['max']+1;
		$filename = sprintf("attachment/%08d",$aid);
		if(move_uploaded_file($attachment['tmp_name'],$filename)){
			$query = sprintf("insert into attachment(id,name,type,size,parent,target,note,istaff) values('%d','%s','%s','%d','purchase','%d','%s','%d')",
				$aid,
				pg_escape_string($name),
				$type,$size,
				$id,
				pg_escape_string($note),
				$whoami['id']);
			$qr = pg_query($handle,$query);
		}
	}
//
// 資料の削除
	$av = $_REQUEST['av'];
	for($a=0,$b=count($av); $b--; $a++){
		$query = sprintf("update attachment set vf=false where id='%d'",$av[$a]);
		$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
	}
//
?>
<a href="purchase.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select purchase.* from purchase where purchase.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$remark = $qo['remark'];
			$brand = $qo['brand'];
			$exhibition = $qo['exhibition'];
			$number = $qo['number'];
			$volume = $qo['volume'];
			$amount = $qo['amount'];
			$price = $qo['price'];
			$discount = $qo['discount'];
			$type = $qo['type'];
			$shipper = $qo['shipper'];
			$season = $qo['season'];

			$query = sprintf("select * from payplan where vf=true and purchase='%d' order by pdate",$id);
			$pr = pg_query($handle,$query);
			$pays = pg_num_rows($pr);
			$paymuch = array();
			$paydate = array();
			for($a=0; $a<$pays; $a++){
				$po = pg_fetch_array($pr,$a);
				$paymuch[$a] = $po['much'];
				$paydate[$a] = explode("-",$po['pdate']);
			}

			$pdate = explode("-",$qo['pdate']);

			$refer = 0;
		}
		else{
			$id = 0;
			$remark = "";
			$brand = 0;
			$exhibition = 0;
			$number ='';
			$volume = 0;
			$amount = 0;
			$price = 0;
			$discount = 0;
			$type = 0;
			$shipper = 0;
			$season = 0;
			
			$pays =1;
			$paymuch = 0;
			$paydate = array();
			for($a=0; $a<6; $a++){
				$paydate[$a] = $tt;
			}
			$pdate = explode("-",date("Y-m-d"));

			$refer = 0;
		}
?>
<p class="title1">発注：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	if(F.elements['delete'].checked){
		return confirm('このレコードを削除しますか?');
	}
	else{
		var mes = new Array();
		var a;
		var err = 0;
		var elm;
	
		if(F.elements['brand'].value==0){
			mes[err++] = "ブランドは必須です";
		}
		if(F.elements['season'].value==0){
			mes[err++] = "seasonは必須です";
		}
		if(parseInt(F.elements['volume'].value)==0){
			mes[err++] = "volumeが無効です";
		}
		if(findZC(F.elements['price'].value) || parseFloat(F.elements['price'].value)==0.0){
			mes[err++] = "priceが無効です";
		}
		if(parseFloat(F.elements['amount'].value)==0.0){
			mes[err++] = "amountが無効です";
		}
		
// Ajax! Ajax! Ajax!
		var parameters = sprintf("?query=select count(*) from purchase where vf=true and number='%s' and id<>%d",F.elements['number'].value,F.elements['id'].value);
		var ooo = new Ajax.Request('../query.php',{method:'get',asynchronous:false,parameters:parameters});
		var count = parseInt(ooo.transport.responseText);
// Ajax! Ajax! Ajax!
		if(count){
			mes[err++] = "発注番号が重複しています";
		}
		if(findZC(F.elements['number'].value)){
			mes[err++] = "発注番号が無効です";
		}

		if(err){
			alert(mes.join('\n'));
			return false;
		}
		else{
			var warn = 0;
			var value;
			var dst = new Array(
				{'name':'number','text':'発注番号'},
				{'name':'volume','text':'数量'},
				{'name':'amount','text':'総額'}
			);
			for(a=0,b=dst.length; b--; a++){
				value = F.elements[dst[a]['name']].value;
				if(value==''){
					mes[warn++] = sprintf("【警告】%s が未入力です",dst[a]['text']);
				}
			}
			if(value=F.elements['attachment'].value,value.match(/\s/)){
				mes[warn++] = sprintf("【警告】ファイル名(%s)の半角スペースは+に変換されます",value);
			}
			if(parseInt(F.elements['as'].value)){
				elm=F.elements['av[]'];
				for(a=0,b=elm.length; b--; a++){
					if(elm[a].checked){
						mes[warn++] = sprintf("【警告】%s は削除されます",elm[a].getAttribute('file'));
					}
				}
			}
			if(warn){
				alert(mes.join('\n'));
			}
			
			return(confirm('この内容で登録してよろしいですか?'))
		}
	}
}
		</script>
		<script type="text/javascript">
function setParams(F)
{
	var brand = F.elements['brand'];
	if(parseInt(brand.value)){
		var currency = brand.options[brand.selectedIndex].getAttribute('currency');
		F.elements['currency'].value = currency;
		switch(parseInt(F.elements['type'].value)){
			case 1:
				F.elements['discount'].value = brand.options[brand.selectedIndex].getAttribute('db');
				calcAmount(F);
				break;
			case 2:
				F.elements['discount'].value = brand.options[brand.selectedIndex].getAttribute('ds');
				calcAmount(F);
			default: break;
		}
	}
}
		</script>
		<script type="text/javascript">
function calcAmount(F)
{
	var T = parseFloat(F.elements['price'].value);
	var D = parseFloat(F.elements['discount'].value);
	var A = T-((T*D));
	F.elements['amount'].value = sprintf("%.2f",A);
}
								</script>
</p>
<form action="" method="post" enctype="multipart/form-data" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="34%">
				<tr>
						<td width="2%" class="th-edit">brand</td>
						<td width="98%" class="td-edit"><select name="brand" id="brand" onchange="setParams(this.form)">
								<option value="0">-- 選択してください --</option>
								<?php
		$ddd = array();
		$qq = array();
		$qq[] = sprintf("select brand.*,nation.code3,currency.code as ccode,currency.name as cname");
		$qq[] = sprintf("from brand join nation on brand.nation=nation.id join currency on brand.currency=currency.id");
		$qq[] = sprintf("where brand.vf=true order by brand.name");
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$brand? $__XHTMLselected:"");
			$dv = getPGSQLarray($qo['discount']);
			$so = getPGSQLarray($qo['shipper']);
			$pn = getPGSQLarray($qo['pnote']);
?>
								<option shipper="<?php printf("%s",implode(",",$so)); ?>" db="<?php printf("%.2f",$dv[0]); ?>" ds="<?php printf("%.2f",$dv[1]); ?>" pb="<?php printf("%s",rawurlencode($pn[0])); ?>" ps="<?php printf("%s",rawurlencode($pn[1])); ?>" currency="<?php printf("%s (%s)",$qo['ccode'],$qo['cname']); ?>" <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['name'],$qo['code3']); ?></option>
								<?php
		}
?>
						</select></td>
						<td width="98%" class="th-edit">type</td>
						<td width="98%" class="td-edit"><label>
								<select name="type" id="type" onchange="setParams(this.form)">
<?php
	for($a=0,$b=count($__pType); $b--; $a++){
		$selected = sprintf("%s",$__pType[$a]['value']==$type? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$__pType[$a]['value']); ?>"><?php printf("%s",$__pType[$a]['text']); ?></option>
<?php
	}
?>
								</select>
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">発注日</td>
						<td class="td-edit"><span class="td-nowrap">
								<select name="pdate[0]" id="pdate[0]" onchange="leapAdjust(this.form,'pdate')">
										<?php
	for($a=$tt[0]-100; $a<=$tt[0]; $a++){
		$selected=sprintf("%s",$a==$pdate[0]? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
										<?php
	}
?>
										</option>
								</select>
年
<select name="pdate[1]" id="pdate[1]" onchange="leapAdjust(this.form,'pdate')">
		<?php
	for($a=1; $a<=12; $a++){
		$selected=sprintf("%s",$a==$pdate[1]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
		<?php
	}
?>
		</option>
</select>
月
<select name="pdate[2]" id="pdate[2]">
		<?php
	for($a=1; $a<=31; $a++){
		$selected=sprintf("%s",$a==$pdate[2]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?>
		<?php
	}
?>
		</option>
</select>
日</span></td>
						<td class="th-edit">No</td>
						<td class="td-edit"><label></label>
						<input name="number" type="text" id="number" value="<?php printf("%s",$number); ?>" size="32" maxlength="64" /></td>
				</tr>
				<tr>
						<td class="th-edit">season</td>
						<td class="td-edit"><select name="season" id="season">
								<option value="0" ps="<?php printf("%s",implode("-",$min)); ?>" pe="<?php printf("%s",implode("-",$max)); ?>">-- 選択してください --</option>
								<?php
		$ddd = array();
		$query = sprintf("select * from season where vf=true order by year desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$pp = getPGSQLarray($qo['purchase']);
			$selected = sprintf("%s",$qo['id']==$season? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> ps="<?php printf("%s",$pp[0]); ?>" pe="<?php printf("%s",$pp[1]); ?>" value="<?php printf("%s",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
						<td class="th-edit">exhibition</td>
						<td class="td-edit"><select name="exhibition" id="exhibition">
								<option value="0">---</option>
								<?php
		$ddd = array();
		$query = sprintf("select * from exhibition where vf=true order by ps desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$exhibition? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">volume</td>
						<td class="td-edit"><label>
								<input name="volume" type="text" class="input-Digit" id="volume" value="<?php printf("%d",$volume); ?>" size="6" maxlength="8" />
						</label></td>
						<td class="th-edit">shipper</td>
						<td class="td-edit"><select name="shipper" id="shipper">
								<option value="0">---</option>
								<?php
		$ddd = array();
		$query = sprintf("select maker.*,nation.code3 from maker join nation on maker.nation=nation.id where maker.vf=true and maker.attribute&%d<>0 order by maker.name",CORP_ATTR_SHIPPER);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$shipper? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['name'],$qo['code3']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">price						</td>
						<td class="td-edit">total(T)
								<input name="price" type="text" class="input-Digit" id="price" onchange="calcAmount(this.form)" value="<?php printf("%.2f",$price); ?>" size="12" maxlength="24" /> 
								discount(D)
								<input name="discount" type="text" class="input-Digit" id="discount" onchange="calcAmount(this.form)" value="<?php printf("%.2f",$discount); ?>" size="6" maxlength="6" /></td>
						<td class="th-edit">amount (T-(T*D))</td>
						<td class="td-edit"><label>
								<input name="amount" type="text" class="input-Digit" id="amount" value="<?php printf("%.2f",$amount); ?>" size="12" maxlength="24" />
								<input name="currency" type="text" id="currency" size="12" maxlength="64" readonly="true" />
						</label></td>
				</tr>
<?php
	$query = sprintf("select attachment.*,staff.nickname from attachment join staff on attachment.istaff=staff.id where attachment.vf=true and attachment.parent='purchase' and attachment.target='%d' order by attachment.udate desc",$id);
	$ar = pg_query($handle,$query);
	$as = pg_num_rows($ar);
	if($as){
?>
				<tr>
						<td class="th-edit">添付資料
						<script type="text/javascript">
function outFile(name,id)
{
	var url = sprintf("output.php/%s?id=%d",encodeURIComponent(name),id);
	window.location = url;
}
								</script></td>
						<td colspan="3" class="td-edit">
						<table width="22%">
								<tr>
										<td width="7%" class="th-edit">×
<?php /* 要素がひとつだと[]が配列とみなされない(Fuck!)のでこの見えないやつをダミーに使う 2008-10-30 */ ?>
<input name="av[]" type="checkbox" class="notDisplay" id="av[]" value="0" file="" /></td>
										<td width="7%" class="th-edit">DL</td>
										<td width="7%" class="th-edit">name</td>
										<td width="6%" class="th-edit">type</td>
										<td width="6%" class="th-edit">size</td>
										<td width="81%" class="th-edit">at</td>
								</tr>
<?php
	for($a=0; $a<$as; $a++){
		$ao = pg_fetch_array($ar,$a);
?>
								<tr>
										<td class="td-edit"><label>
												<input name="av[]" file="<?php printf("%s",$ao['name']); ?>" type="checkbox" id="av[]" value="<?php printf("%d",$ao['id']); ?>" />
										</label></td>
										<td class="td-edit"><img title="Click to start download!" onclick="outFile('<?php printf("%s",$ao['name']); ?>','<?php printf("%d",$ao['id']); ?>')" src="../images/download_manager.png" width="16" height="16" border="0" /></td>
										<td class="td-edit">
										<span title="<?php printf("%s",$ao['note']); ?>"><?php printf("%s",$ao['name']); ?></span>										</td>
										<td class="td-edit"><?php printf("%s",$ao['type']); ?></td>
										<td class="td-editDigit"><?php printf("%s",number_format($ao['size'])); ?></td>
										<td class="td-edit"><?php printf("%s by %s",ts2JP($ao['idate']),$ao['nickname']); ?></td>
								</tr>
<?php
	}
?>
						</table>						</td>
				</tr>
<?php
	}
?>
				<tr>
						<td class="th-edit">資料の追加
						<input name="as" type="hidden" id="as" value="<?php printf("%d",$as); ?>" /></td>
						<td colspan="3" class="td-edit"><input name="attachment" type="file" id="attachment" size="64" maxlength="128" /> 
						note 
								<label>
								<input name="note" type="text" id="note" size="48" maxlength="256" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td colspan="3" class="td-edit"><textarea name="remark" cols="64" rows="8" id="remark"><?php printf("%s",$remark); ?></textarea></td>
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

	setParams(document.forms['edit']);
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
		$query = sprintf("select min(pdate),max(pdate) from purchase");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$min = explode("-",$qo['min']);
		$max = explode("-",$qo['max']);
		$__dMin = $min[0]-5;
		$__dMax = $max[0]+5;
	
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'purchase.udate desc,purchase.pdate desc,brand.name desc'),
			array('name'=>'発注日','text'=>'purchase.pdate desc,brand.name'),
			array('name'=>'ブランド','text'=>'brand.name,purchase.pdate desc'),
			array('name'=>'exhibition','text'=>'exhibition.ps desc,purchase.pdate desc'),
		);

		if(isset($_REQUEST['exec'])){
			$brand = $_REQUEST['brand'];
			$exhibition = $_REQUEST['exhibition'];
			$order = $_REQUEST['order'];
			$season = $_REQUEST['season'];
			
			$_SESSION['purchase'] = array('brand'=>$brand,'exhibition'=>$exhibition,'order'=>$order,'season'=>$season);
		}
		else if(isset($_SESSION['purchase'])){
			$bo = $_SESSION['purchase'];
			$brand = $bo['brand'];
			$exhibition = $bo['exhibition'];
			$order = $bo['order'];
			$season = $bo['season'];
		}
		else{
			$brand = 0;
			$exhibition = 0;
			$order = 0;
			$season = 0;
		}
?>
<p class="title1">発注(experimental - under construction) <a href="purchase.php?mode=edit">新規登録</a></p>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td class="th-edit">対象期間</td>
						<td colspan="3" class="td-edit"><label>
								<select name="season" id="season" onchange="this.form.submit()">
										<option value="0">-- 全て --</option>
										<?php
		$ddd = array();
		$query = sprintf("select * from season where vf=true order by year desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$pp = getPGSQLarray($qo['purchase']);
			$selected = sprintf("%s",$qo['id']==$season? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> ps="<?php printf("%s",$pp[0]); ?>" pe="<?php printf("%s",$pp[1]); ?>" value="<?php printf("%s",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
										<?php
		}
?>
								</select></label>
								<label></label>
						<label></label></td>
				</tr>
				<tr>
						<td width="5%" class="th-edit">brand</td>
						<td width="24%" class="td-edit"><select name="brand" id="brand" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select brand.*,nation.code3,currency.code as ccode,currency.name as cname from brand join (maker join (nation join currency on nation.currency=currency.id) on maker.nation=nation.id) on brand.maker=maker.id where brand.vf=true and brand.id in (select distinct brand from purchase where vf=true) order by brand.name");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$brand? $__XHTMLselected:"");
?>
								<option currency="<?php printf("%s (%s)",$qo['ccode'],$qo['cname']); ?>" <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['name'],$qo['code3']); ?></option>
								<?php
		}
?>
						</select></td>
						<td width="7%" class="th-edit">exhibition</td>
						<td width="64%" class="td-edit"><select name="exhibition" id="exhibition" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select * from exhibition where vf=true and id in (select distinct exhibition from purchase where vf=true) order by ps desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$exhibition? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit"><label></label>
										<label></label></td>
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
		$qq = array();
		$qq[] = sprintf("select season.name as scode,shipper.name as sname,nation.code3,currency.code,currency.rate*purchase.amount as yen,exhibition.name as exname,purchase.*,brand.name as brand,staff.nickname");
		$qq[] = sprintf("from purchase join season on purchase.season=season.id join maker as shipper on purchase.shipper=shipper.id join exhibition on purchase.exhibition=exhibition.id join (brand join currency on brand.currency=currency.id join nation on brand.nation=nation.id) on purchase.brand=brand.id join staff on purchase.ustaff=staff.id");
		$qq[] = sprintf("where purchase.vf=true");
		if($season){
			$qq[] = sprintf("and purchase.season='%d'",$season);
		}
		if($brand){
			$qq[] = sprintf("and purchase.brand='%d'",$brand);
		}
		if($exhibition){
			$qq[] = sprintf("and purchase.exhibition='%d'",$exhibition);
		}
		$qq[] = sprintf("order by %s",$__oList[$order]['text']);
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
?>
<p class="title1">発注履歴 (<?php printf("%s",number_format($qs)); ?>件 - ￥<span id="totalY">??????</span> ※現時点での為替レートによる換算)</p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">season</td>
						<td width="2%" class="th-edit">発注日</td>
						<td width="2%" class="th-edit">brand</td>
						<td width="2%" class="th-edit">shipper</td>
						<td width="2%" class="th-edit">No</td>
						<td width="1%" class="th-edit">exhibition</td>
						<td width="1%" class="th-edit">price</td>
						<td width="1%" class="th-edit">dis.</td>
						<td width="1%" class="th-edit">amount</td>
						<td width="1%" class="th-edit">￥</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	$totalY = 0;
	$data = pg_fetch_all($qr);
	for($a=0; $a<$qs; $a++){
//		$qo = pg_fetch_array($qr,$a);
		$qo = $data[$a];
		$totalY += $qo['yen'];
?>
				<tr elmtype="purchase" active="<?php printf("%s",$active); ?>">
						<td class="td-edit"><a href="purchase.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-edit"><?php printf("%s",$qo['scode']); ?></td>
						<td class="td-edit"><?php printf("%s",dt2JP($qo['pdate'])); ?></td>
						<td class="td-edit"><?php printf("%s",cutoffStr($qo['brand'])); ?></td>
						<td class="td-edit"><?php printf("%s",cutoffStr($qo['sname'])); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['number']); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['exname']); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($qo['price'],2)); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($qo['discount'],2)); ?></td>
						<td class="td-editDigit"><?php printf("%s (%s)",number_format($qo['amount'],2),$qo['code']); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($qo['yen'],2)); ?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
				<tr>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($totalY,2)); ?></td>
						<td class="th-edit">&nbsp;</td>
				</tr>
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
		if(elm[a].getAttribute("elmtype")=='purchase'){
			if(elm[a].getAttribute("active")=='t'){
//				elm[a].style.backgroundColor = "#FF8080";
				elm[a].style.background = '#FFCCFF';
			}
		}
	}
	var elm = document.getElementById('totalY');
	elm.innerHTML = sprintf("%s",number_format(<?php printf("%d",$totalY); ?>,0));
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
