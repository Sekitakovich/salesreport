<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	if($id = $_REQUEST['id']){
		$query = sprintf("select * from attachment where id='%d'",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$name = $qo['name'];
		$file = sprintf("attachment/%08d",$qo['id']);
// IEのファイル名の扱いが自分勝手なのでやむを得ずこうする
		$__browser = get_browser(null,true);

		if($__browser['browser']=="IE"){
			$__Cd = sprintf("Content-disposition: attachment; filename=%s",mb_convert_encoding($name,"sjis-win"));
// 気持ち悪いがこれで確定か ...				
		}
		else if($__browser['browser']=="Safari"){ // Safari ???
			$__Cd = sprintf("Content-disposition: attachment");
		}
		else{
			$name = str_replace(" ","+",$name); // ファイル名に半角スペースが含まれているとそこで切れてしまうのだ
//			$name = urlencode($name); // これだとぐちゃぐちゃ
			$__Cd = sprintf("Content-disposition: attachment; filename=%s",$name);
		}

		$__Ct = sprintf("Content-type: %s",$qo['type']);
		$__Cl = sprintf("Content-length: %d",$qo['size']);
		header($__Ct);
		header($__Cd);
//		header($__Cl);

		echo(file_get_contents($file));
	}
	pg_close($handle);
}
?>
