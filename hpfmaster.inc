<?php
set_time_limit(0);
ini_set("memory_limit","-1");
session_start();

$pgconnect = "dbname=hpfmaster user=postgres password=postgres";

$__XHTMLselected  = "selected=\"selected\"";
$__XHTMLchecked   = "checked=\"checked\"";

$__mailEncoding = "ISO-2022-JP";

//require_once("Var_Dump.php");
//Var_Dump::displayInit(array('display_mode' => 'HTML4_Table'));

$tt=explode("-",date("Y-m-d-H-i-s-t"));

// シーズン

$__estMax = 12;


// 発注タイプ
$__pType = array(
	array('value'=>0,'text'=>'----'),
	array('value'=>1,'text'=>'Bulk'),
	array('value'=>2,'text'=>'Sample'),
);

// 属性
define ('ATTR_STAFF_CLERK',0x00000001);
define ('ATTR_STAFF_BUYER',0x00000002);
define ('ATTR_STAFF_DIREC',0x00000004);
define ('ATTR_STAFF_PRESS',0x00000008);
define ('ATTR_STAFF_ADMIN',0x10000000);

$__dAttr = array(
	array('name'=>'Clerk (PIC at HPF)','mask'=>ATTR_STAFF_CLERK,'icon'=>'phone.png'),
	array('name'=>'Buyer','mask'=>ATTR_STAFF_BUYER,'icon'=>'money.png'),
	array('name'=>'Director','mask'=>ATTR_STAFF_DIREC,'icon'=>'eye.png'),
	array('name'=>'Press','mask'=>ATTR_STAFF_PRESS,'icon'=>'pencil.png'),
	array('name'=>'Admin','mask'=>ATTR_STAFF_ADMIN,'icon'=>'preferences-system.png'),
);

// 権限
define ('PERM_MASTER_EDIT',  0x00000001);
define ('PERM_REPORT_FULL',  0x00000002);
define ('PERM_EXPORT_CSV',   0x00000004);
define ('PERM_DAILY_EDIT',   0x00000010);
define ('PERM_ORDER_EDIT',   0x00000020);
define ('PERM_DELIV_EDIT',   0x00000040);
define ('PERM_VIEW_PAYPLAN', 0x00000080);

$__dPerm = array(
	array('name'=>'マスターテーブルの登録・修正','mask'=>PERM_MASTER_EDIT,'icon'=>'000805.gif'),
	array('name'=>'全店舗の売上レポート出力','mask'=>PERM_REPORT_FULL,'icon'=>'000811.gif'),
	array('name'=>'売上レポートのCSV形式出力','mask'=>PERM_EXPORT_CSV,'icon'=>'000818.gif'),
	array('name'=>'売上データの登録・修正','mask'=>PERM_DAILY_EDIT,'icon'=>'000807.gif'),
	array('name'=>'発注データの登録・修正','mask'=>PERM_ORDER_EDIT,'icon'=>'000813.gif'),
	array('name'=>'配送データの登録・修正','mask'=>PERM_DELIV_EDIT,'icon'=>'000804.gif'),
	array('name'=>'支払予定の検索','mask'=>PERM_VIEW_PAYPLAN,'icon'=>'000816.gif'),
);

// maker/shipper属性
define ('CORP_ATTR_MAKER',0x00000001);
define ('CORP_ATTR_SHIPPER',0x00000002);
$__cAttr = array(
	array('name'=>'Maker','mask'=>CORP_ATTR_MAKER,'icon'=>''),
	array('name'=>'Shipper','mask'=>CORP_ATTR_SHIPPER,'icon'=>''),
);

function dt2JP($ts)
{
	$week = array('日','月','火','水','木','金','土');
	$unix = strtotime($ts);
	$oooo = date("Y-m-d-w",$unix);
	$pppp = explode("-",$oooo);
	$qqqq = sprintf("%d月%d日(%s)",$pppp[1],$pppp[2],$week[$pppp[3]]);
//	$qqqq = sprintf("%04d年%d月%d日(%s)",$pppp[0],$pppp[1],$pppp[2],$week[$pppp[3]]);
//	$qqqq = sprintf("%04d年%d月%d日",$pppp[0],$pppp[1],$pppp[2]);
	return($qqqq);
}

function dt2JPmd($ts)
{
	$unix = strtotime($ts);
	$oooo = date("Y-m-d-w",$unix);
	$pppp = explode("-",$oooo);
	$qqqq = sprintf("%d月%d日",$pppp[1],$pppp[2]);
	return($qqqq);
}

function ts2JP($ts)
{
	$week = array('日','月','火','水','木','金','土');
	$unix = strtotime($ts);
	$oooo = date("Y-m-d-w-H-i-s",$unix);
	$pppp = explode("-",$oooo);
	$qqqq = sprintf("%04d年%d月%d日(%s) %02d時%02d分",
		$pppp[0],$pppp[1],$pppp[2],$week[$pppp[3]],
		$pppp[4],$pppp[5]
	);
	return($qqqq);
}

function getPGSQLarray($src)
{
	$ex=array('{','}');
	$src=str_replace($ex,'',$src);
	if($src){
		$dst=explode(',',$src);
		for($a=0,$b=count($dst); $b--; $a++){
			$dst[$a]=trim($dst[$a],'\"');
		}
	}
	else $dst=array();
	return($dst);
}

function getStaffInfo($handle,$id=0)
{
	$qq = array();
	$qq[] = sprintf("select staff.*");
	$qq[] = sprintf("from staff");
	if($id){
		$qq[] = sprintf("where staff.id=%d",$id);
	}
	else{
		$qq[] = sprintf("where staff.account='%s' and vf=true",$_SERVER["REMOTE_USER"]);
	}
	$query = implode(" ",$qq);
	if($qr = pg_query($handle,$query)){
		$qo = pg_fetch_array($qr);
	}
	else{
		$qo = array();
	}
	return($qo);
}


function getDivisionName($handle,$id)
{
  $name = array();
  for($loop=true; $loop; ){
    $query = sprintf("select * from division where id='%d'",$id);
    $qr = pg_query($handle,$query);
    $qo = pg_fetch_array($qr);
    $name[] = $qo['name'];
    if($parent = $qo['parent']){
      $id=$parent;
    }
    else break;
  }
  return(implode(" > ",array_reverse($name)));
}

function getAreaName($handle,$id)
{
  $name = array();
  for($loop=true; $loop; ){
    $query = sprintf("select * from area where id='%d'",$id);
    $qr = pg_query($handle,$query);
    $qo = pg_fetch_array($qr);
    $name[] = $qo['name'];
    if($parent = $qo['parent']){
      $id=$parent;
    }
    else break;
  }
  return(implode(" > ",array_reverse($name)));
}

//
// $parent以下のarea.idを再帰的に列挙する(注:$parent自身は配列に含まれない)
//
function areaTree($handle,$parent)
{
    $tree = array($parent);
    $tree = array();
    $query = sprintf("select id from area where vf=true and parent='%d'",$parent);
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
        $qo = pg_fetch_array($qr,$a);
        $id = $qo['id'];
        $tree[] = $id;
        $tree = array_merge($tree,areaTree($handle,$id));
    }
    return($tree);
}

//
// $parent以下のdivision.idを再帰的に列挙する(注:$parent自身は配列に含まれない)
//
function divisionTree($handle,$parent)
{
    $tree = array($parent);
    $tree = array();
    $query = sprintf("select id from division where vf=true and parent='%d'",$parent);
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
        $qo = pg_fetch_array($qr,$a);
        $id = $qo['id'];
        $tree[] = $id;
        $tree = array_merge($tree,divisionTree($handle,$id));
    }
    return($tree);
}

function cutoffStr($src,$len=18)
{
	if(mb_strlen($src)<=$len){
		$dst = $src;
	}
	else{
		$plus = " ...";
		$size = $len-mb_strlen($plus);
		$dst = sprintf("%s%s",mb_substr($src,0,$size),$plus);
	}
	return($dst);
}

?>
