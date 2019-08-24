<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>payplan</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
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
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	default:
	
		$msMAX =12;

		if(isset($_REQUEST['exec'])){
			$yy = $_REQUEST['yy'];
			$mm = $_REQUEST['mm'];
			$ms = $_REQUEST['ms'];
		}
		else{
			$yy = $tt[0];
			$mm = $tt[1];
			$ms = 0;
		}
?>
<p class="title1">支払予測 (experimental - under construction)</p>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">対象期間</td>
						<td width="95%" class="td-edit"><label></label>
								<label><select name="yy" id="yy">
										<?php
for($a=$tt[0]-2; $a<=$tt[0]+2; $a++){
	$selected=sprintf("%s",$a==$yy? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
										<?php
}
?>
								</select>
						</label>
								<label> 年
								<select name="mm" id="mm">
										<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$mm? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
										<?php
}
?>
								</select>
								</label>
								<label> 月</label>
								+
								<label>
								<select name="ms" id="ms">
<?php
	for($a=0; $a<=$msMAX; $a++){
		$selected=sprintf("%s",$a==$ms? $__XHTMLselected:"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
<?php
	}
?>
								</select>
								</label> 
								<label>
ヶ月
<input type="submit" name="show" id="show" value="再表示" />
<input name="exec" type="hidden" id="exec" value="on" />
</label></td>
				</tr>
		</table>
</form>
<?php
    function __ymSort($a,$b)
    {
        if($a['Y']==$b['Y']){
	        if($a['M']==$b['M']){
		        if($a['brand']==$b['brand']){
					return(strcmp($a['pdate'],$b['pdate']));
				}
				else{
					return(strcmp($a['brand'],$b['brand']));
				}
			}
			else{
	            return($a['M']-$b['M']);
			}
        }
        else{
            return($a['Y']-$b['Y']);
        }
    }
	
	$mTable = array(12,1,2,3,4,5,6,7,8,9,10,11);
	$mS = $mm;
	$yS = $yy;
	$mE = $mTable[($mm+$ms)%12];
	$yE = $yy+(($mm+$ms)>12? 1:0);

	$sVal = sprintf("%04d%02d",$yS,$mS);
	$eVal = sprintf("%04d%02d",$yE,$mE);

//	printf("yS=%d mS=%d yE=%d mE=%d",$yS,$mS,$yE,$mE);
	
    $qq = array();
    $qq[] = sprintf("SELECT purchase.pdate,purchase.number,season.year,season.name as sname,stype.nickname,brand.name as bname,purchase.amount,currency.rate,currency.code as ccode,currency.id as cid,estimate.pays,estimate.yoffset,estimate.month,estimate.percentage");
    $qq[] = sprintf("from purchase join (season join stype on season.stype=stype.id) on purchase.season=season.id join (brand join currency on brand.currency=currency.id join estimate on estimate.brand=brand.id) on purchase.brand=brand.id");
    $qq[] = sprintf("where purchase.vf=true");
    $qq[] = sprintf("and estimate.stype=stype.id");
    $query = implode(" ",$qq);
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);

    $schedule = array();
	$ss =0;

    for($a=0; $a<$qs; $a++){
        $qo = pg_fetch_array($qr,$a);
		$pdate = $qo['pdate'];
		$number = $qo['number'];
		$sname = $qo['sname'];
        $bname = $qo['bname'];
        $amount = $qo['amount'];
        $rate = $qo['rate'];
        $ccode = $qo['ccode'];
        $cid = $qo['cid'];
        $year = $qo['year'];
        $pays = $qo['pays'];
        $yoffset = getPGSQLarray($qo['yoffset']);
        $month = getPGSQLarray($qo['month']);
        $percentage = getPGSQLarray($qo['percentage']);
        for($b=0; $b<$pays; $b++){
            $per = $percentage[$b];
            $pay = ($amount/100)*$per;
            $yen = $pay*$rate;

			$Y = $year+$yoffset[$b];
			$M = $month[$b];

			$cVal = sprintf("%04d%02d",$Y,$M);
			if($cVal>=$sVal && $cVal<=$eVal){
	            $schedule[] = array('pdate'=>$pdate,'number'=>$number,'Y'=>$Y,'M'=>$M,'amount'=>$amount,'pay'=>$pay,'cid'=>$cid,'cur'=>$ccode,'per'=>$per,'yen'=>$yen,'brand'=>$bname,'pays'=>$pays,'count'=>$b+1,'season'=>$sname);
				$ss++;
			}
        }

    }
    usort($schedule,__ymSort);
		
		
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
?>
<p class="title1">支払予測 (<?php printf("%s",number_format($ss)); ?>件 - ￥<span id="totalY">??????</span> ※現時点での為替レートによる換算)</p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">年度</td>
						<td width="2%" class="th-edit">月</td>
						<td width="1%" class="th-edit">brand</td>
						<td width="1%" class="th-edit">at</td>
						<td width="1%" class="th-edit">number</td>
						<td width="1%" class="th-edit">season</td>
						<td width="1%" class="th-edit">count</td>
						<td width="1%" class="th-edit">amount</td>
						<td width="1%" class="th-edit">％</td>
						<td width="1%" class="th-edit">pay</td>
						<td width="1%" class="th-edit">通貨</td>
						<td width="1%" class="th-edit">￥</td>
				</tr>
<?php
	$totalY = 0;
	$csv = array();
	for($a=0; $a<$ss; $a++){
		$qo = $schedule[$a];
		$totalY += $qo['yen'];
		$line = array();
		$line[] = sprintf("%d",$qo['Y']);
		$line[] = sprintf("%d",$qo['M']);
		$line[] = sprintf("%s",$qo['brand']);
		$line[] = sprintf("%s",$qo['pdate']);
		$line[] = sprintf("%s",$qo['number']);
		$line[] = sprintf("%s",$qo['season']);
		$line[] = sprintf("%d of %d",$qo['count'],$qo['pays']);
		$line[] = sprintf("%d",$qo['amount']);
		$line[] = sprintf("%d",$qo['per']);
		$line[] = sprintf("%d",$qo['pay']);
		$csv[] = implode(",",$line);
?>
				<tr elmtype="payplan" active="<?php printf("%s",$active); ?>">
						<td class="td-editDigit"><?php printf("%d",$qo['Y']); ?></td>
						<td class="td-editDigit"><?php printf("%d",$qo['M']); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['brand']); ?></td>
						<td class="td-edit"><?php printf("%s",dt2JP($qo['pdate'])); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['number']); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['season']); ?></td>
						<td class="td-editDigit"><?php printf("%d/%d",$qo['count'],$qo['pays']); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($qo['amount'])); ?></td>
						<td class="td-editDigit"><?php printf("%d",$qo['per']); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($qo['pay'])); ?></td>
						<td class="td-edit"><a href="currency.php?mode=edit&id=<?php printf("%d",$qo['cid']); ?>"><?php printf("%s",$qo['cur']); ?></a></td>
						<td class="td-editDigit"><?php printf("%s",number_format($qo['yen'])); ?></td>
				</tr>
<?php
	}
	$_SESSION['csv'] = $csv;
?>
				<tr>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($totalY)); ?></td>
				</tr>
		</table>
		<p>
				<label>
				<input name="csv" type="button" id="csv" onclick="MM_goToURL('parent','csvout.php');return document.MM_returnValue" value="Export to CSV" />
				</label>
		</p>
</form>
<script language="JavaScript" type="text/javascript">
window.onload = function()
{
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
//console.info(elm[a].getAttribute("elmtype"));
		if(elm[a].getAttribute("elmtype")=='payplan'){
			if(elm[a].getAttribute("active")=='t'){
//				elm[a].style.backgroundColor = "#FF8080";
				elm[a].style.background = '#FFCCFF';
			}
		}
	}
	var elm = document.getElementById('totalY');
//	elm.innerHTML = sprintf("%s",number_format(<?php printf("%d",$totalY); ?>,0));
	elm.innerHTML = '<?php printf("%s",number_format($totalY)); ?>';
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
