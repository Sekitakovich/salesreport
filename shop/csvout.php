<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	pg_query($handle,"begin");
	$whoami = getStaffInfo($handle);
	$csv = $_SESSION['csv'];

	$_ct = "application/octet-stream";
	//$_ct = "application/vnd.ms-excel";
	
	$_cd = "attachment";
	$_fn = "Report.CSV";
	
	header(sprintf("Content-type: %s",$_ct));
	header(sprintf("Content-disposition: %s; filename=%s",$_cd,$_fn));
	for($a=0,$b=count($csv); $b--; $a++){
		printf("%s\n",mb_convert_encoding($csv[$a],"SJIS-win"));
	}

	if($whoami['alog']=='t'){
		$ua = pg_escape_string($_SERVER['HTTP_USER_AGENT']);
		$ip = pg_escape_string($_SERVER['REMOTE_ADDR']);
		$referer = pg_escape_string($_SERVER['HTTP_REFERER']);
		$text = pg_escape_string(implode("\n",$csv));
		
		$query = sprintf("select max(id) from csvlog");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$query = sprintf("insert into csvlog(id,staff,ua,ip,csv,referer) values('%d','%d','%s','%s','%s','%s')",$qo['max']+1,$whoami['id'],$ua,$ip,$text,$referer);
		$qr = pg_query($handle,$query);
	}	
	pg_query($handle,"commit");
	pg_close($handle);
}
?>