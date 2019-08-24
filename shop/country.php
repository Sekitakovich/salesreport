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
	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
		default:
		if(isset($_REQUEST['id'])){
  $id=$_REQUEST['id'];

		}
		else{
			$id = 0;
		}
?>
<p class="title1">国の選択
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	return(true);
}
		</script>
</p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="29%">
				<tr>
						<td width="7%" class="th-edit">narea</td>
						<td width="26%" class="td-edit"><label></label><label></label>
								<select name="narea" id="narea" onchange="arrangeC(this.form,this.value)">
										<option value="0">-- 親 --</option>
								</select></td>
						<td width="8%" class="th-edit">nation</td>
						<td width="25%" class="td-edit"><select name="nation" id="nation">
								<option value="0">-- 子 --</option>
						</select></td>
						<td width="34%" class="th-edit">
								<input type="submit" name="exec" id="exec" value="決定" />
						</td>
				</tr>
		</table>
</form>
<script type="text/javascript">
function arrangeP(F,id)
{
	var elm = F.elements['narea'];
	var parameters = '?query=select id,name from narea where vf=true order by weight desc';
	var ooo = new Ajax.Request('../query.php',{method:'get',asynchronous:false,parameters:parameters});
	var ppp = explode('\n',ooo.transport.responseText);

	var a;
	var b;
	var c;
	var length = ppp.length;
	
	elm.options.length = length+1;
	for(a=0,b=length,c=1; b--; a++,c++){
		var xxx = explode(',',ppp[a]);
		elm.options[c].text = xxx[1];
		elm.options[c].value = xxx[0];
	}

}
</script>
<script type="text/javascript">
function arrangeC(F,parent)
{
	if(parent){
		var elm = F.elements['nation'];
		var parameters = sprintf("?query=select id,code3,kana from nation where vf=true and narea=%d order by name",parent);
		var ooo = new Ajax.Request('../query.php',{method:'get',asynchronous:false,parameters:parameters});
		var ppp = explode('\n',ooo.transport.responseText);
	
		var a;
		var b;
		var c;
		var length = ppp.length;
		
		elm.options.length = length+1;
		for(a=0,b=length,c=1; b--; a++,c++){
			var xxx = explode(',',ppp[a]);
			elm.options[c].text = sprintf("%s (%s)",xxx[1],xxx[2]);
			elm.options[c].value = xxx[0];
		}
		elm.options.selectedIndex = 0;
	}
}
</script>
<script language="JavaScript" type="text/javascript">
window.onload = function(){
	arrangeP(document.edit,0);
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
