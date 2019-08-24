<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	pg_query($handle,"begin");
	$whoami = getStaffInfo($handle);
	$topPage = sprintf("%s",$whoami['shop']? "daily.php":"dailyreport.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>HPF Sales Report</title>
</head>

<frameset rows="64,*" frameborder="no" border="0" framespacing="0">
		<frame src="menu.php" name="topFrame" scrolling="No" noresize="noresize" id="topFrame" title="topFrame" />
		<frame src="<?php printf("%s",$topPage); ?>" name="mainFrame" id="mainFrame" title="mainFrame" />
</frameset>
<noframes><body>
</body>
</noframes></html>
<?php
	pg_query($handle,"commit");
	pg_close($handle);
}
?>

