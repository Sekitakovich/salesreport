<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>個人別設定</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script language="JavaScript" type="text/javascript" src="../common.js"></script>
<script language="JavaScript" type="text/javascript" src="../php.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	case "save":
		if($size=$_FILES['file']['size']){ // アップロードがなされたら
			$src=$_FILES['file']['tmp_name'];
			$dst = sprintf("./staff/%06d.PNG",$whoami['id']);
		    $im = new Imagick();
    		$im->readimage($src);
			$im->writeimage($dst);
			$im->destroy();
			$query = sprintf("update staff set bgfile='%s' where id='%d'",$_FILES['file']['name'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php 		printf("Query(%d) = [%s]",$qr,$query); ?> --><?php

		}
		$query = sprintf("update staff set bgshow=%s,bgrepeat='%d',bgposition='%d' where id='%d'",
			isset($_REQUEST['show'])? "true":"false",
			$_REQUEST['repeat'],
			$_REQUEST['position'],
			$whoami['id']);
		$qr = pg_query($handle,$query);
?><!-- <?php 		printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
		$whoami = getStaffInfo($handle); // 注意!
?>
<a href="personal.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$repeat = array('縦横両方','横のみ','縦のみ','なし');
		$position = array('左','中央','右','上','中心','下');
?>
<p class="title1">個人別設定</p>
<form action="" method="post" enctype="multipart/form-data" name="edit" id="edit">
		<table width="29%">
				<tr>
						<td width="2%" class="th-edit">背景画像</td>
						<td width="98%" class="td-edit"><label>
								<?php printf(" %s ",$whoami['bgfile']); ?><input <?php printf("%s",$whoami['bgshow']=='t'? "checked":"");?> type="checkbox" name="show" id="show" />
								表示する
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">位置</td>
						<td class="td-edit"><?php
	for($a=0,$b=count($position); $b--; $a++){
?>
										<label>
												<input <?php printf("%s",$whoami['bgposition']==$a? "checked":"");?> type="radio" name="position" id="position" value="<?php printf("%d",$a); ?>" />
										<?php printf("%s",$position[$a]); ?></label>
										<br />
										<?php
	}
?>
						</td>
				</tr>

				<tr>
						<td class="th-edit">リピート</td>
						<td class="td-edit">
<?php
	for($a=0,$b=count($repeat); $b--; $a++){
?>
						<label><input <?php printf("%s",$whoami['bgrepeat']==$a? "checked":"");?> type="radio" name="repeat" id="repeat" value="<?php printf("%d",$a); ?>" />
						<?php printf("%s",$repeat[$a]); ?></label>
						<br />
<?php
	}
?>						</td>
				</tr>
				<tr>
						<td class="th-edit">画像の変更</td>
						<td class="td-edit"><input name="file" type="file" id="file" size="64" maxlength="256" /></td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
										<input name="mode" type="hidden" id="mode" value="save" /></td>
				</tr>
		</table>
</form>
<?php
		break;
//--------------------------------------------------------------------
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
<script type="text/javascript">
window.onload = function()
{
	setPersonalView(<?php printf("%d",$whoami['id']); ?>,'<?php printf("%s",$whoami['bgshow']); ?>',<?php printf("%d",$whoami['bgrepeat']); ?>,<?php printf("%d",$whoami['bgposition']); ?>);
}
</script>
</body>
</html>
