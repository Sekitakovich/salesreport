// JavaScript Document
function leapAdjust(F,name) // 閏年の補正
{

	var yy = name+'[0]';
	var mm = name+'[1]';
	var dd = name+'[2]';
//	var days = new Array(0,31,28,31,30,31,30,31,31,30,31,30,31);
	var days = array(0,31,28,31,30,31,30,31,31,30,31,30,31); // php.js
	
	var y=F.elements[yy].value;
	if(((y%4==0)&&(y%100!=0))||(y%400==0)){
		days[2]=29;
	}

	var length = days[F.elements[mm].value];

	F.elements[dd].options.length=length;
	for(a=0,d=1; a<length; a++,d++){
			F.elements[dd].options[a].text=d;
			F.elements[dd].options[a].value=d;
	}
}

function zoomElement(target,mode)
{
	target.style.fontWeight = mode? 'bold':'';
	target.style.fontSize = mode? 'large':'';
}

/*
*	表示順をリスト上で変更し、結果をidS[?]に反映する関数
*	思いのほか苦労した(笑)
*/
function swapRow(F,src,pn)
{
	var length = F.length.value;
	src = parseInt(src); // なんなんだこれ

	if(src == 0 && pn == 'P'){
		;
	}
	else if(src == length-1 && pn == 'N'){
		;
	}
	else{
		var a;
		var b;
		var dst = pn=='N'? src+1:src-1;
		var __idS = new Array();
		var __old = new Array();
		var ooo;
		var ppp;
	
		for(a=0,b=length; b--; a++){
			__idS[a] = F.elements['idS['+a+']'].value;
		}

		ooo = __idS[src];
		__idS[src] = __idS[dst];
		__idS[dst] = ooo;

		ooo = document.getElementById('row['+src+']');
		ppp = document.getElementById('row['+dst+']');

		var cells = ooo.cells.length;
		for(a=1; a<cells; a++){ // [0]は書き換えないところがミソ
			var iH = ooo.cells[a].innerHTML;
			ooo.cells[a].innerHTML = ppp.cells[a].innerHTML;
			ppp.cells[a].innerHTML = iH;
		}
/*
*
*/
		for(a=0,b=length; b--; a++){
			F.elements['idS['+a+']'].value = __idS[a];
		}
//
		F.elements['update'].disabled = '';
//
	}
}

function setPersonalView(id,bgshow,bgrepeat,bgposition)
{
	if(bgshow=='t'){
		var repeat = new Array('repeat','repeat-x','repeat-y','no-repeat');
		var position = new Array('left','center','right','top','middle','bottom');
		document.body.style.backgroundRepeat = repeat[bgrepeat];
		document.body.style.backgroundPosition = position[bgposition];
		document.body.style.backgroundImage = sprintf("url(staff/%06d.PNG)",id); // ちとここに課題残してたりする
	}
}

function editMaster(dst)
{
	var option = dst.options[dst.selectedIndex];
	var id = parseInt(option.value);
	if(id){
		var table = dst.name;
		var url = sprintf("%s.php?mode=edit&id=%d",table,id);
		var who = option.text;
		if(confirm(sprintf("%s の編集に移動しますか?",who))){
			window.location = url;
		}
	}
}

// Blink! Blink! Blink!
var blankAt = 0;
var passedSecs = 0;

function atSecs()
{
	var elm = document.getElementsByTagName('*');
	var a,b;
	for(a=0,b=elm.length; b--; a++){
		var dst = elm[a];
		if(dst.getAttribute("blink")=='on'){
			dst.style.visibility = ((passedSecs%blankAt))? 'visible':'hidden';
		}
	}
	passedSecs++;
}

/*
*	classNameは','区切りで複数指定可能
*	blSecsは表示秒数(blSecs間表示→1秒消す、の繰り返し)
*/
function startBlink(blSecs)
{
	blankAt = blSecs+1; // ここがミソ
	setInterval(atSecs,1000*1);
}

/*
*	フォーム内のエレメントいずれかが変更されたら[登録]ボタンを有効にする
*
*	注：submitボタンは最初のひとつだけ
*/
function editPrepare(nameF,nameS){
	var elm = Form.getElements(nameF);
	var dst = document.forms['edit'].elements[nameS];
	var a;
	var b;

	if(dst){
		for(a=0,b=elm.length; b--; a++){
			switch(elm[a].type){
			case 'button':
				Event.observe(elm[a], 'click',function(e){dst.disabled=false});
				break;
			case 'text':
			case 'textarea':
			case 'password':
				Event.observe(elm[a], 'keypress',function(e){dst.disabled=false});
				Event.observe(elm[a], 'change',function(e){dst.disabled=false});
				break;
			case 'file':
			case 'select-one':
				Event.observe(elm[a], 'change',function(e){dst.disabled=false});
				break;
			case 'checkbox':
			case 'radio':
				Event.observe(elm[a], 'click',function(e){dst.disabled=false});
				break;
/*
			case 'submit':
				Event.observe(elm[a], 'click',function(e){dst.focus()});
				break;
*/
			default:
				break;
			}
		}
		dst.disabled = true;
	}
	return;
}

function fillEmptyCells() // 空セルに枠がつかないIEのための処理(涙)
{
	var elm = document.getElementsByTagName('TD');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].innerHTML==''){
			if(elm[a].innerText==''){
//				elm[a].innerText = '&nbsp;'; // なんでダメなの???
				elm[a].innerText = ' ';
			}
		}
	}
	return;
}

/*
*	ちと便利な関数をば (引数文字列kwはrawurlencodeすること)
*/
function openGoogle(kw)
{
	var src = decodeURIComponent(kw);
	if(confirm(sprintf("'%s' をGoogleで検索しますか?",src))){
		var url = sprintf("http://www.google.com/search?q=%s",kw);
		var name = 'google';
		var win = window.open(url,name);
		win.focus();
	}
}

function findZC(str){ // strで与えられた文字列に全角文字が含まれるか調べる
	var a;
	var b;
	for(a=0,b=str.length; b--; a++){
		if(escape(str.charAt(a)).length>=4){
			return true;
		}
	}
	return false;
}


