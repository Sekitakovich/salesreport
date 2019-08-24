<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>shop</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<script language="JavaScript" type="text/javascript" src="../prototype.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
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
		
		$mlot = $_REQUEST['mlot'];
		$myen = $_REQUEST['myen'];

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
				if($result[$a]) $set[] = sprintf("entered=true");
				$set[] = sprintf("rbase='%f'",$result[$a]);
				$set[] = sprintf("tbase='%f'",$target[$a]);
				$set[] = sprintf("lbase='%f'",$last[$a]);
				$set[] = sprintf("bbase='%f'",$book[$a]);
				$set[] = sprintf("result='%d'",$result[$a]*$rate);
				$set[] = sprintf("target='%d'",$target[$a]*$rate);
				$set[] = sprintf("last='%d'",$last[$a]*$rate);
				$set[] = sprintf("book='%d'",$book[$a]*$rate);
				$set[] = sprintf("member='%d'",$member[$a]);

				$set[] = sprintf("mlot='%d'",$mlot[$a]);
				$set[] = sprintf("myen='%d'",$myen[$a]);

				$set[] = sprintf("visitor='%d'",$visitor[$a]);
				$set[] = sprintf("open=%s",isset($open[$a])? "false":"true");
				$set[] = sprintf("note='%s'",pg_escape_string($note[$a]));
				$set[] = sprintf("udate=now(),etime=now(),ustaff='%d'",$whoami['id']);
				$query = sprintf("update daily set %s where id='%d'",implode(",",$set),$id);
				$qr = pg_query($handle,$query);
	?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			}
		}
?>
<a href="dedit.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	default:
	$__ym = date("Y-m");
	$tt = explode("-",$__ym);
	if(isset($_REQUEST['exec'])){
		$id = $_REQUEST['id'];
		$ym = explode("-",$_REQUEST['ym']);
	}
	else{
		$id = 0;
		$ym = $tt;
	}
	
?>
<script language="JavaScript" type="text/javascript">
function editStart(F)
{
	if(F.elements['id'].value=='0'){
	}
	else{
		F.submit();
	}
}
</script>
<form action="" method="get" enctype="application/x-www-form-urlencoded" name="menu" target="_self" id="menu">
		<table width="16%">
				<tr>
						<th width="11%" class="th-edit"><input name="exec" type="hidden" id="exec" value="t" />
						店舗</th>
						<th width="11%" class="td-edit"><label>
								<select name="id" id="id" onchange="editStart(this.form)">
										<option value="0">-- 店舗を選択してください --</option>
<?php
	$__dset = getPGSQLarray($whoami['dset']);
	$ppp = array();
	$tree = array();
	for($a=0,$b=count($__dset); $b--; $a++){
		$kkk = divisionTree($handle,$__dset[$a]);
		$kkk[] = $__dset[$a];
		$tree=array_merge($tree,$kkk);
	}
	$query = sprintf("select shop.* from shop,division,area where shop.vf=true and shop.division in (%s) and shop.division=division.id and shop.area=area.id order by division.weight desc,area.weight desc,shop.name",implode(",",$tree));
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$dname = getDivisionName($handle,$qo['division']);
		$aname = getAreaName($handle,$qo['area']);
		$selected = sprintf("%s",$id==$qo['id']? $__XHTMLselected:"");
?>
						<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>">
						<?php printf("%s (%s:%s)",$qo['name'],$dname,$aname); ?>						</option>
<?php
	}
?>
								</select>
						</label></th>
						<th width="11%" class="th-edit">年/月</th>
						<td width="3%" class="td-edit"><label>
								<select name="ym" id="ym" onchange="editStart(this.form)">
										<?php
	$yS = $tt[0]-10;
	$yE = $tt[0]+1;
	for($a=$yS; $a<=$yE; $a++){
		for($b=1; $b<=12; $b++){
			$ymS = sprintf("%d年%d月",$a,$b);
			$ymD = sprintf("%d-%d",$a,$b);
			$selected = sprintf("%s",($a==$ym[0] && $b==$ym[1])? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%s",$ymD); ?>"><?php printf("%s",$ymS); ?></option>
										<?php
		}
	}
?>
								</select>
						</label></td>
				</tr>
</table>
</form>
<p>
		<?php
	if(isset($_REQUEST['exec'])){
		$thisY = $ym[0];
		$thisM = $ym[1];
		$query = sprintf("select shop.*,currency.rate,currency.name as cname from shop,currency where shop.id='%d' and shop.currency=currency.id",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$name = $qo['name'];
		$cname = $qo['cname'];
		$rate = $qo['rate'];
?>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list" onsubmit="return checkTheList(this)">
		<p>
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
	
	var mYSum = 0;
	var mLSum = 0;

	var dst = 'modify['+offset+']';
	F.elements[dst].value = 't';
	
	for(a=0,d=1; a<days; a++,d++){
//
		var dst = 'target['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 予算に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			tSum += val;
			F.elements['open['+a+']'].checked = (val? false:true);
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
//
		var dst = 'myen['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 顧客買上金額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			mYSum += val;
		}
//
//
		var dst = 'mlot['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 顧客買上数に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			mLSum += val;
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
	F.elements['mYSum'].value = mYSum;
	F.elements['mLSum'].value = mLSum;
//
	var day = document.getElementById(sprintf("day%02d",offset));
	day.style.backgroundColor = "#99FFFF";
	F.elements['exec'].disabled = '';
//	
}
		</script>
				<script language="JavaScript" type="text/javascript">
function checkTheList(F)
{
	var mes = new Array();
	var shopname = F.elements['shopname'].value;
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

	var mLSum = parseInt(F.elements['mLSum'].value);
	var mYSum = parseInt(F.elements['mYSum'].value);

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
		ask[a++] = sprintf("対象店舗 %s",shopname);
		ask[a++] = sprintf("%d年%d月 (対象レコード数:%d)",yy,mm,rs);
		ask[a++] = sprintf("昨年計:%s %s",number_format(lSum,0),cname);
		ask[a++] = sprintf("予算計:%s %s",number_format(tSum,0),cname);
		ask[a++] = sprintf("売上計:%s %s",number_format(rSum,0),cname);
		ask[a++] = sprintf("取りおき計:%s %s",number_format(bSum,0),cname);
		ask[a++] = sprintf("顧客:%s名",number_format(mSum,0));
		ask[a++] = sprintf("客数:%s名",number_format(vSum,0));
		ask[a++] = sprintf("顧客買上数計:%s件",number_format(mLSum,0));
		ask[a++] = sprintf("顧客買上金額計:%s %s",number_format(mYSum,0),cname);
		ask[a++] = sprintf("");
		ask[a++] = sprintf("この内容で登録します。よろしいですか?");
		return confirm(ask.join('\n'));
	}
}
		</script>
		</p>
<p>--- import daily parameters  ---</p>
		<table>

				<tr>
						<td width="79" class="th-edit">列の区切り子</td>
						<td width="185" class="td-edit">
						<label><input name="delimiter" type="radio" id="delimiter" value="9" checked="checked" />
						タブ</label>
						<label><input type="radio" name="delimiter" id="delimiter" value="44" />カンマ</label>
						<label><input type="radio" name="delimiter" id="delimiter" value="32" />スペース</label>						</td>
				</tr>
				<tr>
						<td class="th-edit">テキスト 
								<script type="text/javascript">
function zoomArea(F)
{
	F.elements['csv'].rows = F.elements['csv'].rows==3? 32:3;
}
								</script></td>
						<td class="td-edit"><label>
								<textarea name="csv" cols="32" rows="3" id="csv" onclick="zoomArea(this.form)"></textarea>
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">リストへの反映</td>
						<td class="td-edit">
						<label><input onclick="importCSV(this.form)" type="button" name="import" id="import" value="実行" /></label>
						<label><input type="reset" name="resetF" id="resetF" value="やり直し" /></label>
						<script type="text/javascript">
function importCSV(F)
{
	var a;
	var b;
	var vd = 0;

	var days = parseInt(F.elements['days'].value);

	var chr = String.fromCharCode(Form.serialize(F).toQueryParams()['delimiter']); // Thanks to prototype.js
	var row = F.elements['csv'].value.split('\n');

	for(a=0,b=row.length; b--; a++){
		var src = row[a].split(chr);
		var d = src[0];
		var p = src[1];
		var n = src[2];
		
		if(d>0 && d<=days){
			var offset = d-1; // しゃあない
			
			var dst = sprintf("last[%d]",offset);
			F.elements[dst].value = p;
			
			var dst = sprintf("target[%d]",offset);
			F.elements[dst].value = n;
	
			setSum(F,offset);
			vd++;
		}
		else{
//			alert(sprintf("%d日は無効です",d));
		}
	}
	if(vd){
		alert(sprintf("%d行(日)の反映を完了しました",vd));
	}
	else{
		alert('有効行がありません');
	}
}
								</script>
						</td>
				</tr>
		</table>
		<p>*各営業日の「予算」に有効な値が入力されていないと「休」チェックは外せません		</p>
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">日</td>
						<td class="th-editDigit" width="3%">曜</td>
						<td class="th-editDigit" width="3%">休</td>
						<td class="th-editDigit" width="3%">昨年</td>
						<td width="1%" class="th-edit">予算</td>
						<td width="95%" class="th-edit">売上</td>
						<td width="95%" class="th-edit">取りおき</td>
						<td width="95%" class="th-edit">客数</td>
						<td width="95%" class="th-edit">顧客カード</td>
						<td width="95%" class="th-edit"><span title="顧客買上数">顧客買上数</span></td>
						<td width="95%" class="th-edit"><span title="顧客買上金額">顧客買上金額</span></td>
						<td width="95%" class="th-edit">特記事項
						<script type="text/javascript">
function zoomTA(dst,onoff)
{
	dst.rows = onoff? 4:1;
}
								</script></td>
				</tr>
				<?php
	$days = date("t",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // この月が何日あるか
	$dow = date("w",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // 
	$tu = strtotime(date("Y-m-d"));
	$week = array('日','月','火','水','木','金','土');

	$lSum = 0;
	$tSum = 0;
	$rSum = 0;
	$bSum = 0;
	$mSum = 0;
	$vSum = 0;
	$mLSum = 0;
	$mYSum = 0;

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
//
			$mlot = $qo['mlot'];
			$myen = $qo['myen'];
//
		}
		else{
			$target = 0;
			$result = 0;
			$book = 0;
			$last = 0;
			$member = 0;
			$visitor = 0;
			$note = '　';
			$open = 'f';
//
			$mlot = 0;
			$myen = 0;
//
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
						<td class="td-editDigit"><input title="昨年" name="last[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="last[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$last); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><input title="予算" name="target[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="target[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$target); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><label>
						<input title="売上" name="result[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="result[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$result); ?>" size="8" maxlength="8" />
						</label></td>
						<td class="td-editDigit"><input title="取りおき" name="book[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="book[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$book); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><input title="客数" name="visitor[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="visitor[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$visitor); ?>" size="3" maxlength="8" /></td>
						<td class="td-editDigit"><input title="新規顧客カード数" name="member[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="member[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$member); ?>" size="3" maxlength="8" /></td>
						<td class="td-editDigit"><input title="顧客買上数" name="mlot[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="mlot[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$mlot); ?>" size="3" maxlength="8" /></td>
						<td class="td-editDigit"><input title="顧客買上金額" name="myen[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="myen[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$myen); ?>" size="8" maxlength="12" /></td>
						<td class="td-editDigit"><label>
						<textarea title="特記事項" name="note[<?php printf("%d",$a); ?>]" cols="32" rows="1" id="note[<?php printf("%d",$a); ?>]" onmouseover="zoomTA(this,true)" onmouseout="zoomTA(this,false)" onchange="setSum(this.form,<?php printf("%d",$a); ?>)"><?php printf("%s",$note); ?></textarea>
						</label>
<input name="modify[<?php printf("%d",$a); ?>]" type="hidden" id="modify[<?php printf("%d",$a); ?>]" value="f" />						</td>
				</tr>
				<?php
	}
?>
				<tr>
						<td class="th-edit">計</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%"><input name="lSum" type="text" class="input-Digit" id="lSum" value="<?php printf("%d",$lSum); ?>" size="8" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="tSum" type="text" class="input-Digit" id="tSum" value="<?php printf("%d",$tSum); ?>" size="8" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="rSum" type="text" class="input-Digit" id="rSum" value="<?php printf("%d",$rSum); ?>" size="8" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="bSum" type="text" class="input-Digit" id="bSum" value="<?php printf("%d",$bSum); ?>" size="8" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="vSum" type="text" class="input-Digit" id="vSum" value="<?php printf("%d",$vSum); ?>" size="6" maxlength="6" readonly="true" /></td>
						<td class="th-editDigit"><input name="mSum" type="text" class="input-Digit" id="mSum" value="<?php printf("%d",$mSum); ?>" size="6" maxlength="6" readonly="true" /></td>
						<td class="th-editDigit"><input name="mLSum" type="text" class="input-Digit" id="mLSum" value="<?php printf("%d",$mLSum); ?>" size="6" maxlength="6" readonly="true" /></td>
						<td class="th-editDigit"><input name="mYSum" type="text" class="input-Digit" id="mYSum" value="<?php printf("%d",$mYSum); ?>" size="6" maxlength="6" readonly="true" /></td>
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
		<input name="shopname" type="hidden" id="shopname" value="<?php printf("%s",$name); ?>" />
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
	}
?>
<?php
		break;
//--------------------------------------------------------------------
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</p>
</body>
</html>
