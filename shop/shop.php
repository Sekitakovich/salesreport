<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>shop</title>
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
		$refer = 0;
		$table = array("daily","staff");	
		for($a=0,$b=count($table); $b--; $a++){
			$query = sprintf("select count(*) from %s where vf=true and shop='%d'",$table[$a],$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			if($refer = $qo['count']){
				break;
			}
		}
		return($refer);
	}

	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
	pg_query($handle,"LOCK shop IN EXCLUSIVE MODE");
	$thisMode = isset($_REQUEST['mode'])? $_REQUEST['mode']:'';
	
	switch($thisMode){
//--------------------------------------------------------------------
	case "psave":
		$shop = $_REQUEST['id'];
		$yy = $_REQUEST['yy'];
		$mm = $_REQUEST['mm'];
		$days = $_REQUEST['days'];
		$rate = $_REQUEST['rate'];
		$target = $_REQUEST['target'];
		$last = $_REQUEST['last'];
		$result = $_REQUEST['result'];
		$book = $_REQUEST['book'];
		$member = $_REQUEST['member'];
		$visitor = $_REQUEST['visitor'];
		$open = $_REQUEST['open'];
		$modify = $_REQUEST['modify'];
		$note =$_REQUEST['note'];

		for($a=0,$b=$days,$dd=1; $b--; $a++,$dd++){
			if($modify[$a]=='t'){
				$query = sprintf("select id from daily where vf=true and shop='%d' and yyyymmdd='%d-%d-%d'",
					$shop,$yy,$mm,$dd);
				$qr = pg_query($handle,$query);
				$qs = pg_num_rows($qr);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
				if($qs){
					$qo = pg_fetch_array($qr);
					$id = $qo['id'];
				}
				else{
					$query = sprintf("select max(id) from daily");
					$qr = pg_query($handle,$query);
	?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
					$qo = pg_fetch_array($qr);
					$id=$qo['max']+1;
					$query = sprintf("insert into daily(id,shop,yyyymmdd) values('%d','%d','%d-%d-%d')",$id,$shop,$yy,$mm,$dd);
					$qr = pg_query($handle,$query);
	?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
				}
				$set = array();
				$set[] = sprintf("rbase='%f'",$result[$a]);
				$set[] = sprintf("tbase='%f'",$target[$a]);
				$set[] = sprintf("lbase='%f'",$last[$a]);
				$set[] = sprintf("bbase='%f'",$book[$a]);
				$set[] = sprintf("result='%d'",$result[$a]*$rate);
				$set[] = sprintf("target='%d'",$target[$a]*$rate);
				$set[] = sprintf("last='%d'",$last[$a]*$rate);
				$set[] = sprintf("book='%d'",$book[$a]*$rate);
				$set[] = sprintf("member='%d'",$member[$a]);
				$set[] = sprintf("visitor='%d'",$visitor[$a]);
				$set[] = sprintf("open=%s",isset($open[$a])? "false":"true");
				$set[] = sprintf("note='%s'",pg_escape_string($note[$a]));
				$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
				$query = sprintf("update daily set %s where id='%d'",implode(",",$set),$id);
				$qr = pg_query($handle,$query);
	?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			}
		}
?>
<a href="shop.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "daily":
		$id = $_REQUEST['id'];
		$ym = explode("-",$_REQUEST['ym']);
		$thisY = $ym[0];
		$thisM = $ym[1];
		$query = sprintf("select shop.*,currency.rate,currency.name as cname from shop,currency where shop.id='%d' and shop.currency=currency.id",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$name = $qo['name'];
		$cname = $qo['cname'];
		$rate = $qo['rate'];
?>
<p class="title1">売上データの編集 <?php printf("%s - %d年%d月 単位:%s (×%.2f円)",$name,$thisY,$thisM,$cname,$rate); ?></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list2" onsubmit="return checkTheList(this)">
		<script language="JavaScript" type="text/javascript">
function setSum(F,offset)
{
	var days = parseInt(F.elements['days'].value);
	var a;
	var d;
	var tSum = 0;
	var lSum = 0;
	var rSum = 0;
	var bSum = 0;
	var mSum = 0;
	var vSum = 0;

	var dst = 'modify['+offset+']';
	F.elements[dst].value = 't';
	
	for(a=0,d=1; a<days; a++,d++){
//
		var dst = 'target['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 目標額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			tSum += val;
		}
//
		var dst = 'last['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 昨年度の売上金額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			lSum += val;
		}
//
		var dst = 'result['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 売上金額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			rSum += val;
		}
//
		var dst = 'book['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 取りおきに有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			bSum += val;
		}
//
		var dst = 'member['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 顧客に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			mSum += val;
		}
//
		var dst = 'visitor['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 客数に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			vSum += val;
		}
	}
	F.elements['tSum'].value = tSum;
	F.elements['lSum'].value = lSum;
	F.elements['rSum'].value = rSum;
	F.elements['bSum'].value = bSum;
	F.elements['mSum'].value = mSum;
	F.elements['vSum'].value = vSum;
//
	var day = document.getElementById(sprintf("day%02d",offset));
	day.style.backgroundColor = "#8888FF";
	F.elements['exec'].disabled = '';
//	
}
		</script>
		<script language="JavaScript" type="text/javascript">
function checkTheList(F)
{
	var mes = new Array();
	var err = 0;
	var yy = parseInt(F.elements['yy'].value);
	var mm = parseInt(F.elements['mm'].value);
	var days = parseInt(F.elements['days'].value);
	var rSum = parseInt(F.elements['rSum'].value);
	var tSum = parseInt(F.elements['tSum'].value);
	var lSum = parseInt(F.elements['lSum'].value);
	var bSum = parseInt(F.elements['bSum'].value);
	var mSum = parseInt(F.elements['mSum'].value);
	var vSum = parseInt(F.elements['vSum'].value);

	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else{
		var ask = new Array();
		var a;
		var b;
		var c;
		var rs;
		var cname = '<?php printf("%s",$cname); ?>';

		a=0;
		rs=0;
//
		for(b=0,c=1; b<days; b++,c++){
			if(F.elements['modify['+b+']'].value=='t'){
				rs++;
				var tV = parseInt(F.elements['target['+b+']'].value);
				var lV = parseInt(F.elements['last['+b+']'].value);
				var rV = parseInt(F.elements['result['+b+']'].value);
				var bV = parseInt(F.elements['book['+b+']'].value);
				var isClose = F.elements['open['+b+']'].checked;
				if(isClose == false && (tV==0 || lV==0 || rV==0 || bV==0)){
					ask[a++] = sprintf("警告：%2d日 金額0の項目があります",c);
				}
			}
		}
//
		ask[a++] = sprintf("%d年%d月 (対象レコード数:%d)",yy,mm,rs);
		ask[a++] = sprintf("昨年計:%s %s",number_format(lSum,0),cname);
		ask[a++] = sprintf("予算計:%s %s",number_format(tSum,0),cname);
		ask[a++] = sprintf("売上計:%s %s",number_format(rSum,0),cname);
		ask[a++] = sprintf("取りおき計:%s %s",number_format(bSum,0),cname);
		ask[a++] = sprintf("顧客:%s名",number_format(mSum,0));
		ask[a++] = sprintf("客数:%s名",number_format(vSum,0));
		ask[a++] = sprintf("");
		ask[a++] = sprintf("この内容で登録します。よろしいですか?");
		return confirm(ask.join('\n'));
	}
}
		</script>
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">日</td>
						<td class="th-editDigit" width="3%">曜</td>
						<td class="th-editDigit" width="3%">休</td>
						<td class="th-editDigit" width="3%">昨年</td>
						<td width="1%" class="th-editDigit">予算</td>
						<td width="95%" class="th-editDigit">売上</td>
						<td width="95%" class="th-editDigit">取りおき</td>
						<td width="95%" class="th-editDigit">顧客</td>
						<td width="95%" class="th-editDigit">客数</td>
						<td width="95%" class="th-edit">特記事項</td>
				</tr>
				<?php
	$days = date("t",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // この月が何日あるか
	$dow = date("w",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // 
	$tu = strtotime(date("Y-m-d"));
	$week = array('日','月','火','水','木','金','土');

	$lsum = 0;
	$tSum = 0;
	$rSum = 0;
	$bSum = 0;
	$mSum = 0;
	$vSum = 0;

	for($a=0,$d=1; $d<=$days; $a++,$d++,$dow++){
		$query = sprintf("select * from daily where vf=true and shop='%d' and yyyymmdd='%d-%d-%d'",$id,$thisY,$thisM,$d);
		$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		if($qs = pg_num_rows($qr)){
			$qo = pg_fetch_array($qr);
			$target = $qo['tbase'];
			$result = $qo['rbase'];
			$book = $qo['bbase'];
			$last = $qo['lbase'];
			$member = $qo['member'];
			$visitor = $qo['visitor'];
			$note = $qo['note']? $qo['note']:'　';
			$open = $qo['open'];
		}
		else{
			$target = 0;
			$result = 0;
			$book = 0;
			$last = 0;
			$member = 0;
			$visitor = 0;
			$note = '　';
			$open = 't';
		}
		$cavg = $visitor? $result/$visitor:0;
		$win = $target? ((float)$result/(float)$target)*100.0:0;
		$ppy = $last? ((float)$result/(float)$last)*100.0:0;

		$lSum += $last;
		$tSum += $target;
		$rSum += $result;
		$bSum += $book;
		$mSum += $member;
		$vSum += $visitor;

		$passed = strtotime(sprintf("%d-%d-%d",$thisY,$thisM,$d))<=$tu? 1:0;

//
		$query = sprintf("select * from holiday where vf=true and yyyymmdd='%d-%d-%d'",$thisY,$thisM,$d);
		$hr = pg_query($handle,$query);
		$hs = pg_num_rows($hr);
		if($hs){
			$ho = pg_fetch_array($hr);
		}
		$isHoliday = $hs;
		$hName = sprintf("%s",$hs? $ho['name']:"");
		$oc = $open=='f'? "checked=\"checked\"":"";
//
?>
				<tr id="<?php printf("day%02d",$a); ?>">
						<td class="td-editDigit" dow="<?php printf("%s",$dow%7); ?>" isholiday="<?php printf("%d",$isHoliday); ?>"><?php printf("%d",$d); ?></td>
						<td class="td-editDigit" dow="<?php printf("%s",$dow%7); ?>"><?php printf("%s",$week[$dow%7]); ?></td>
						<td class="td-editDigit"><input onchange="setSum(this.form,<?php printf("%d",$a); ?>)" <?php printf("%s",$oc); ?> name="open[<?php printf("%d",$a); ?>]" type="checkbox" id="open[<?php printf("%d",$a); ?>]" value="t"  /></td>
						<td class="td-editDigit"><input name="last[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="last[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$last); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><input name="target[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="target[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$target); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><label>
						<input name="result[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="result[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$result); ?>" size="8" maxlength="8" />
						</label></td>
						<td class="td-editDigit"><input name="book[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="book[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$book); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><input name="member[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="member[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$member); ?>" size="3" maxlength="8" /></td>
						<td class="td-editDigit"><input name="visitor[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="visitor[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$visitor); ?>" size="3" maxlength="8" /></td>
						<td class="td-editDigit"><label>
						<textarea onchange="setSum(this.form,<?php printf("%d",$a); ?>)" name="note[<?php printf("%d",$a); ?>]" cols="64" rows="4" id="note[<?php printf("%d",$a); ?>]"><?php printf("%s",$note); ?></textarea>
						</label>
<input name="modify[<?php printf("%d",$a); ?>]" type="hidden" id="modify[<?php printf("%d",$a); ?>]" value="f" />
						</td>
				</tr>
				<?php
	}
?>
				<tr>
						<td class="th-edit">計</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%"><input name="lSum" type="text" class="input-Digit" id="lSum" value="<?php printf("%d",$lSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="tSum" type="text" class="input-Digit" id="tSum" value="<?php printf("%d",$tSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="rSum" type="text" class="input-Digit" id="rSum" value="<?php printf("%d",$rSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="bSum" type="text" class="input-Digit" id="bSum" value="<?php printf("%d",$bSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="mSum" type="text" class="input-Digit" id="mSum" value="<?php printf("%d",$mSum); ?>" size="6" maxlength="6" readonly="true" /></td>
						<td class="th-editDigit"><input name="vSum" type="text" class="input-Digit" id="vSum" value="<?php printf("%d",$vSum); ?>" size="6" maxlength="6" readonly="true" /></td>
						<td class="th-editDigit">
						<input name="exec" type="submit" disabled="disabled" id="exec" value="登録" /></td>
				</tr>
		</table>
		<label>
		<input name="mode" type="hidden" id="mode" value="psave" />
		</label>
		<label>
		<input name="yy" type="hidden" id="yy" value="<?php printf("%d",$thisY);?>" />
		</label>
		<label>
		<input name="mm" type="hidden" id="mm" value="<?php printf("%d",$thisM);?>" />
		</label>
		<label>
		<input name="days" type="hidden" id="days" value="<?php printf("%d",$days); ?>" />
		</label>
		<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
		<input name="rate" type="hidden" id="rate" value="<?php printf("%f",$rate); ?>" />
		<br />
		<script language="JavaScript" type="text/javascript">
window.onload = function()
{
//
	var elm = document.getElementsByTagName('TD');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		switch(elm[a].getAttribute("dow")){
			case '6':
				elm[a].style.color = "#0000FF";
				elm[a].style.fontWeight = "bold";
				break;
			case '0':
				elm[a].style.color = "#FF0000";
				elm[a].style.fontWeight = "bold";
				break;
			default:
				if(elm[a].getAttribute("isHoliday")=='1'){
					elm[a].style.color = "#00FF00";
					elm[a].style.fontWeight = "bold";
				}
				break;
		}
	}
}
		</script>
</form>
<?php
		break;
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from shop");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into shop(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
// staffの自動生成
			$query = sprintf("select max(id) from staff");
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$sid=$qo['max']+1;
			$account = $_REQUEST['account'];
			if($account==''){
				$account = sprintf("%06d",$sid);
			}
			$password = $_REQUEST['password'];
			$md5 = md5($_REQUEST['password']);
			$nickname = sprintf("staff of %s",$_REQUEST['name']);
			
			$query = sprintf("insert into staff(id,istaff,ustaff) values('%d','%d','%d')",$sid,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$query = sprintf("update staff set shop='%d',dset='{%d}',aset='{%d}',account='%s',password='%s',md5='%s',nickname='%s',name[1]='%s',name[2]='%s',kana[1]='%s',kana[2]='%s' where id='%d'",
	$id,
	$_REQUEST['division'],
	$_REQUEST['area'],
	$account,
	$password,
	$md5,
	$nickname,
	$account,
	$account,
	$account,
	$account,
	$sid);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
//
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("division='%d'",$_REQUEST['division']);
		$set[] = sprintf("area='%d'",$_REQUEST['area']);
		$set[] = sprintf("currency='%d'",$_REQUEST['currency']);
		$set[] = sprintf("url='%s'",$_REQUEST['url']);
		$set[] = sprintf("tenant='%d'",$_REQUEST['tenant']);
		$set[] = sprintf("another='%d'",$_REQUEST['another']);
		$set[] = sprintf("ps='%s'",implode("-",$_REQUEST['ps']));
		$set[] = sprintf("pe='%s'",implode("-",$_REQUEST['pe']));
		$set[] = sprintf("dtp='%s'",$_REQUEST['dtp']);
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update shop set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update shop set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="shop.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

			$query = sprintf("select shop.*,currency.rate from shop,currency where shop.id='%d' and shop.currency=currency.id",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$division = $qo['division'];
			$area = $qo['area'];
			$ps = explode("-",$qo['ps']);
			$pe = explode("-",$qo['pe']);
			$currency = $qo['currency'];
			$rate = $qo['rate'];
			$url = $qo['url'];
			$tenant = $qo['tenant'];
			$another = $qo['another'];
			$dtp = $qo['dtp'];

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$refer = 0;
			$division = 0;
			$area = 0;
			$ps = $tt;
			$pe = $tt;
			$currency = 0;
			$rate = 0; // 無意味
			$url = "";
			$tenant = 0;
			$another = 0;
			$dtp = "";
		}
?>
<p class="title1">店舗：編集</p>
<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須です";
	}
	if(F.elements['currency'].value==0){
		mes[err++] = "通貨設定は必須です";
	}
	if(F.elements['division'].value==0){
		mes[err++] = "事業部を選択してください";
	}
	if(F.elements['area'].value==0){
		mes[err++] = "エリアを選択してください";
	}
	if(F.elements['id'].value==0){ // 新規登録
		if(F.elements['password'].value==''){
			mes[err++] = "パスワードは必須です";
		}
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
<br />
<br />
*新規登録の場合のみアカウント：パスワードのフィールドが出る<br />
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">名称</td>
						<td width="24%" class="td-edit"><label>
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="64" maxlength="256" />
						</label></td>
						<td width="7%" class="th-edit">通貨</td>
						<td width="64%" class="td-edit"><select name="currency" id="currency">
								<option value="0">-- 選択してください --</option>
								<?php
		$cd = array();
		$ex = array();
		$query = sprintf("SELECT currency.id,count(*) from shop join currency on shop.currency=currency.id where shop.vf=true and currency.id<>0 group by shop.currency,currency.id order by count desc");						
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$ex[] = $qo['id'];
			$query = sprintf("select * from currency where id='%d'",$qo['id']);
			$cr = pg_query($handle,$query);
			$co = pg_fetch_array($cr);
			$cd[] = $co;
		}		
		$query = sprintf("SELECT currency.* from currency where currency.vf=true and id not in (%s) order by code",implode(",",$ex));						
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$cd[] = $qo;
		}		
		for($a=0,$b=count($cd); $b--; $a++){
			$qo = $cd[$a];			
			$selected = sprintf("%s",$qo['id']==$currency? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (%s)",$qo['code'],$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">事業部</td>
						<td class="td-edit"><label>
						<select name="division" id="division">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select * from division where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$division? $__XHTMLselected:"");
			$dName=getDivisionName($handle,$qo['id']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
								<?php
		}
?>
						</select>
						</label></td>
						<td class="th-edit">エリア</td>
						<td class="td-edit"><select name="area" id="area">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select * from area where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$area? $__XHTMLselected:"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">テナント</td>
						<td class="td-edit"><select name="tenant" id="tenant">
								<option value="0">-- なし --</option>
								<?php
		$query = sprintf("select * from tenant where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$tenant? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
						<td class="th-edit">ID at RFL</td>
						<td class="td-edit"><input name="another" type="text" class="input-Digit" id="another" value="<?php printf("%d",$another); ?>" size="3" maxlength="3" /></td>
				</tr>
				<tr elmtype="createStaff">
						<td class="th-edit">アカウント</td>
						<td class="td-edit"><label>
								<input name="account" type="text" id="account" size="6" maxlength="6" />
						</label>
						*未入力なら自動生成</td>
						<td class="th-edit">パスワード</td>
						<td class="td-edit"><label><input name="password" type="password" id="password" size="6" maxlength="6" />
						</label>
						*未入力の場合ログイン不可</td>
				</tr>
				<tr>
						<td class="th-edit">開店日</td>
						<td class="td-edit"><select name="ps[0]" id="ps[0]" onchange="leapAdjust(this.form,'ps')">
								<?php
for($a=1987; $a<=$tt[0]+1; $a++){
	$selected=sprintf("%s",$a==$ps[0]? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
								<?php
}
?>
						</select>
年
<select name="ps[1]" id="ps[1]" onchange="leapAdjust(this.form,'ps')">
		<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$ps[1]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select>
月
<select name="ps[2]" id="ps[2]">
		<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$ps[2]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select>
日</td>
						<td class="th-edit">閉店日</td>
						<td class="td-edit"><select name="pe[0]" id="pe[0]" onchange="leapAdjust(this.form,'pe')">
								<?php
for($a=1987; $a<=$tt[0]+1; $a++){
	$selected=sprintf("%s",$a==$pe[0]? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
								<?php
}
?>
						</select>
年
<select name="pe[1]" id="pe[1]" onchange="leapAdjust(this.form,'pe')">
		<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$pe[1]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select>
月
<select name="pe[2]" id="pe[2]">
		<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$pe[2]? $__XHTMLselected:"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select>
*閉店日＝開店日とすることで閉店日未定扱いとする </td>
				</tr>
				<tr>
						<td class="th-edit">URL</td>
						<td class="td-edit"><label>
								<input name="url" type="text" id="url" value="<?php printf("%s",$url); ?>" size="64" maxlength="256" />
						</label></td>
						<td class="th-edit">CV</td>
						<td class="td-edit"><input type="text" id="dtp" name="dtp" size="16" maxlength="24" value="<?php echo($dtp); ?>"></td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td class="td-edit"><textarea name="remark" cols="32" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea></td>
						<td class="th-edit">登録</td>
						<td class="td-edit"><input name="exec" type="submit" id="exec" value="実行" />
								<input name="mode" type="hidden" id="mode" value="save" />
								<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
								<span id="void">
								<input name="delete" type="checkbox" id="delete" value="t" />
削除する</span></td>
				</tr>
		</table>
</form>
<?php
	if($id){
?>
<form id="analyze" name="analyze" method="post" action="">
<p class="title1">(参考：解析資料)</p>
		<table width="12%">
		<tr>
				<th class="th-edit">W</th>
				<th class="th-edit">avg</th>
				<th class="th-edit">max</th>
				<th class="th-edit">min</th>
				<th class="th-edit">sample(s)</th>
		</tr>
<?php
	$dow = array(
		array('value'=>1,'name'=>'月'),
		array('value'=>2,'name'=>'火'),
		array('value'=>3,'name'=>'水'),
		array('value'=>4,'name'=>'木'),
		array('value'=>5,'name'=>'金'),
		array('value'=>6,'name'=>'土'),
		array('value'=>0,'name'=>'日'),
	);
	for($a=0,$b=count($dow); $b--; $a++){
		$query = sprintf("SELECT count(*),avg(result),max(result),min(result) from daily where vf=true and open=true and entered=true and shop='%d' and date_part('dow',yyyymmdd)='%d'",$id,$dow[$a]['value']);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
?>
<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
		<tr>
				<td width="2%" class="td-edit"><?php printf("%s",$dow[$a]['name']); ?></td>
				<td width="3%" class="td-editDigit">￥<?php printf("%s",number_format($qo['avg'],0)); ?></td>
				<td width="95%" class="td-editDigit">￥<?php printf("%s",number_format($qo['max'],0)); ?></td>
				<td width="95%" class="td-editDigit">￥<?php printf("%s",number_format($qo['min'],0)); ?></td>
				<td width="95%" class="td-editDigit"><?php printf("%d",$qo['count']); ?></td>
		</tr>
<?php
	}
?>
</table>
		<p>IW=ISO週番号 H=休日</p>
		<table width="12%">
				<tr>
						<th class="th-edit">Y</th>
						<th class="th-edit">IW</th>
						<th class="th-edit">H</th>
						<th class="th-edit">target</th>
						<th class="th-edit">result</th>
						<th class="th-edit">ratio</th>
				</tr>
				<?php
	$query = sprintf("SELECT date_part('year',min(yyyymmdd)) as minY,to_char(min(yyyymmdd),'IW') as minW,date_part('year',max(yyyymmdd)) as maxY,to_char(max(yyyymmdd),'IW') as maxW from daily where vf=true and entered=true and shop='%d'",$id);
	$qr = pg_query($handle,$query);
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
	$qo = pg_fetch_array($qr);
	$minY = $qo['miny'];
	$maxY = $qo['maxy'];
	$minW = $qo['minw'];
	$maxW = $qo['maxw'];
	
	for($a=$minY; $a<=$maxY; $a++){
		$sW = ($a==$minY)? $minW:1;
		$eW = ($a==$maxY)? $maxW:53;
		for($b=$sW; $b<=$eW; $b++){
			$query = sprintf("select sum(target) as target,sum(result) as result from daily where vf=true and shop='%d' and date_part('year',yyyymmdd)='%04d' and to_char(yyyymmdd,'IW')='%02d'",$id,$a,$b);
			$qr = pg_query($handle,$query);
?>
				<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
				<?php
			$qo = pg_fetch_array($qr);
			$target = $qo['target'];
			$result = $qo['result'];
			$ratio = $target? (($result*100)/$target):0;
			$query = sprintf("select count(*) from holiday where vf=true and date_part('year',yyyymmdd)='%04d' and to_char(yyyymmdd,'IW')='%02d'",$a,$b);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$hc = $qo['count'];
			?>
				<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
				<tr>
						<td width="2%" class="td-edit"><?php printf("%04d",$a); ?></td>
						<td width="2%" class="td-edit"><?php printf("%d",$b); ?></td>
						<td width="3%" class="td-editDigit"><?php printf("%s",number_format($hc,0)); ?></td>
						<td width="3%" class="td-editDigit">￥<?php printf("%s",number_format($target,0)); ?></td>
						<td width="3%" class="td-editDigit">￥<?php printf("%s",number_format($result,0)); ?></td>
						<td width="3%" class="td-editDigit"><?php printf("%d",$ratio); ?>％</td>
				</tr>
				<?php
		}
	}
?>
		</table>
</form>
<?php
	}
?>
<script language="JavaScript" type="text/javascript">
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
//
	if(document.edit.elements['id'].value!='0'){
		var elm = document.getElementsByTagName('TR');
		for(a=0,b=elm.length; b--; a++){
			switch(elm[a].getAttribute('elmtype')){
				case 'createStaff':
					elm[a].className = 'notDisplay';
					break;
				default:
					break;
			}
		}
	}
//	
	editPrepare('edit','exec'); // in common.js
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'shop.udate desc'),
			array('name'=>'事業部','text'=>'division.weight desc,division.name,area.weight desc,shop.name'),
			array('name'=>'エリア','text'=>'area.weight desc,area.name,division.weight desc,shop.name'),
			array('name'=>'テナント','text'=>'tenant.weight desc,area.weight desc,area.name,division.weight desc,shop.name'),
			array('name'=>'店名','text'=>'shop.name,division.weight desc,area.weight desc'),
			array('name'=>'開店時期(降順)','text'=>'shop.ps desc,area.weight desc,division.weight desc,shop.name'),
			array('name'=>'開店時期(昇順)','text'=>'shop.ps,area.weight desc,division.weight desc,shop.name'),
		);

		if(isset($_REQUEST['exec'])){
			$division = $_REQUEST['division'];
			$area = $_REQUEST['area'];
			$order = $_REQUEST['order'];
			$kw = $_REQUEST['kw'];
			$tenant = $_REQUEST['tenant'];
			$_SESSION['shop']=array('division'=>$division,'area'=>$area,'kw'=>$kw,'order'=>$order,'tenant'=>$tenant);
		}
		else if(isset($_SESSION['shop'])){
			$division = $_SESSION['shop']['division'];
			$area = $_SESSION['shop']['area'];
			$order = $_SESSION['shop']['order'];
			$kw = $_SESSION['shop']['kw'];
			$tenant = $_SESSION['shop']['tenant'];
		}
		else{
			$division = 0;
			$area = 0;
			$order =0;
			$kw = "";
			$tenant = 0;
		}
?>
<p class="title1">店舗検索 <a href="shop.php?mode=edit">新規登録</a></p>
<form id="" name="" method="post" action="">
		<table width="44%">

				<tr>
						<td width="5%" class="th-edit">事業部</td>
						<td width="24%" class="td-edit"><label>
								<select name="division" id="division" onchange="this.form.submit()">
										<option value="0">-- 全て --</option>
										<?php
//		$query = sprintf("select * from division where vf=true and id in (SELECT distinct division from shop where vf=true) order by weight desc");
		$query = sprintf("select * from division where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$division? $__XHTMLselected:"");
			$dName=getDivisionName($handle,$qo['id']);
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
										<?php
		}
?>
								</select>
								以下
						</label></td>
						<td width="7%" class="th-edit">エリア</td>
						<td width="64%" class="td-edit"><select name="area" id="area" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
//		$query = sprintf("select * from area where vf=true and id in (SELECT distinct area from shop where vf=true) order by weight desc");
		$query = sprintf("select * from area where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$area? $__XHTMLselected:"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
								<?php
		}
?>
						</select>
						以下</td>
				</tr>

				<tr>
						<td class="th-edit">テナント</td>
						<td class="td-edit"><select name="tenant" id="tenant" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$query = sprintf("select * from tenant where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$tenant? $__XHTMLselected:"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$qo['name']); ?></option>
								<?php
		}
?>
						</select></td>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit"><label for="another"></label></td>
				</tr>
				<tr>
						<td class="th-edit">キーワード</td>
						<td class="td-edit"><label></label>
								<label>店名に
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
		$qq = array();
		$qq[] = sprintf("select dtp,tenant.name as tname,tenant.id as tid,shop.*,division.id as did,area.id as aid,currency.id as cid,currency.name as cname,staff.nickname,((cast(now() as date) between shop.ps and shop.pe) or (shop.ps=shop.pe and cast(now() as date)>=shop.ps)) as active");
		$qq[] = sprintf("from shop join division on shop.division=division.id join area on shop.area=area.id join currency on shop.currency=currency.id join staff on shop.ustaff=staff.id join tenant on shop.tenant=tenant.id");
		$qq[] = sprintf("where shop.vf=true");
		if($division){
			$tree = divisionTree($handle,$division); $tree[]=$division;
			$qq[] = sprintf("and division.id in (%s)",implode(",",$tree));
		}
		if($area){
			$tree = areaTree($handle,$area); $tree[]=$area;
			$qq[] = sprintf("and area.id in (%s)",implode(",",$tree));
		}
		if($kw){
			$kw = strtoupper($kw);
			$dst = array('shop.name');
			$ooo = array();
			for($a=0,$b=count($dst); $b--; $a++){
				$ooo[] = sprintf("upper(%s) like '%%%s%%'",$dst[$a],pg_escape_string($kw));
			}
			$ppp = implode(" or ",$ooo);
			$qq[] = sprintf("and %s",$ppp);
		}
		if($tenant){
			$qq[] = sprintf("and shop.tenant='%d'",$tenant);
		}
		$qq[] = sprintf("order by %s",$__oList[$order]['text']);
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<p class="title1">検索結果 (<?php printf("%s",number_format($qs)); ?>件)</p>
		<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
		<script type="text/javascript">
function openURL(elm)
{
	var url = elm.getAttribute('url');
	if(url){
		window.open(url);
	}
}
		</script>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="17%">
				<tr>
						<td width="2%" class="th-edit">&nbsp;</td>
						<td width="2%" class="th-edit">ID</td>
						<td width="2%" class="th-edit">CV</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="1%" class="th-edit">事業部</td>
						<td width="1%" class="th-edit">エリア</td>
						<td width="95%" class="th-edit">テナント</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$dName=getDivisionName($handle,$qo['did']);
		$aName=getAreaName($handle,$qo['aid']);
		$tName = $qo['tname']? $qo['tname']:"　";

		$ps = dt2JP($qo['ps']);
		$pe = ($qo['ps']==$qo['pe'])? "----":dt2JP($qo['pe']);
		$dtp = $qo['dtp'];
		$active = $qo['active'];
?>
				<tr title="<?php printf("最終更新日時 %s by %s",ts2JP($qo['udate']),$qo['nickname']); ?>" active="<?php printf("%s",$active); ?>">
						<td class="td-edit"><a href="shop.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><img src="../images/page_edit_16x16.png" alt="修正" width="16" height="16" border="0" /></a></td>
						<td class="td-editDigit"><?php printf("%d",$qo['id']); ?></td>
						<td class="td-edit"><?php echo($dtp); ?></td>
						<td class="td-edit">
						<span onclick="openURL(this)" url="<?php printf("%s",$qo['url']); ?>"><?php printf("%s",$qo['name']); ?></span>						</td>
						<td class="td-edit"><a href="division.php?mode=edit&amp;id=<?php printf("%d",$qo['did']); ?>"><?php printf("%s",$dName); ?></a></td>
						<td class="td-edit"><a href="area.php?mode=edit&amp;id=<?php printf("%d",$qo['aid']); ?>"><?php printf("%s",$aName); ?></a></td>
						<td class="td-edit"><a href="tenant.php?mode=edit&amp;id=<?php printf("%d",$qo['tid']); ?>"><?php printf("%s",$tName); ?></a></td>
				</tr>
<?php
	}
?>
		</table>
</form>
<script type="text/javascript">
window.onload = function()
{
	var elm = document.getElementsByTagName('SPAN');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		var url = elm[a].getAttribute('url');
		switch(url){
			case '':
			case null:
				break;
			default:
				elm[a].style.fontWeight = 'bold';
				elm[a].style.cursor = 'pointer';
				elm[a].title = sprintf("クリックでwebsite(%s)を開きます",url);
				break;
		}
	}

	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		var active = elm[a].getAttribute('active');
		switch(active){
			case 'f':
				elm[a].style.backgroundColor = "#808080";
				elm[a].style.color = "#FFFFFF";
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
