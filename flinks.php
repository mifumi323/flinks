<?php

// 浮き沈みリンク集 ver1.01
//
//  これ単体では動きません。
//  別途これを呼び出すPHPを用意して、
//   include 'flinks.php';
//  でこのスクリプトを読み込み、
//   $setting['script']   (スクリプトのファイル名)
//   $setting['filename'] (データを入れるファイル)
//   $setting['imgdir']   (バナーのあるディレクトリ。省略可)
//   $setting['password'] (パスワード。省略可)
//   $setting['tagsort']  (タグを並び替えるか。省略可)
//  を設定して、
//   Main();
//  を呼び出すことで初めて動きます。
//  バナーは自前でアップロードするか、URL直接指定する
//  必要があるので多少不便かもしれません。
//  具体的には付属のlinks.phpを参照してください。
//
//  このスクリプト自身は原則として改変しないでください。
//  ただし、MainPage関数内のHTMLは必要に応じて書き換えてもよいでしょう。
//
//  作者：美文(http://tgws.fromc.jp/ mifumi323@tgws.fromc.jp)

function Main()	// これを呼び出して開始
{
	global $_REQUEST;
	switch ($_REQUEST['mode']) {
	case 'score':	Score(); break;
	case 'put':	Put(); break;
	case 'remove':	Remove(); break;
	default:	MainPage(); break;
	}
}

function MainPage()	// 通常表示
{
	global $setting, $_REQUEST, $_SERVER;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<title>リンク集</title>
</head><body class=wide>
<h1 style="float:left;">リンク集</h1>
<?php
	$alldata = ReadLinks($setting['filename']);
	$linkdata = $alldata['link'];
	$tagdata = $alldata['tag'];
	$find = stripslashes($_REQUEST['tag']);
	$query = stripslashes($_SERVER['QUERY_STRING']);
	$query2 = urlencode($query);
	print <<<END
<p style="text-align:right;font-size:80%">
END;
	if ($setting['tagsort'])	// 1.01で追加
	{
		ksort($tagdata);
		reset($tagdata);
	}
	foreach ($tagdata as $tag => $num) {
		$tagurl = urlencode($tag);
		if ($tag != $find) {
			print <<<END
<nobr><a href="{$setting[script]}?tag={$tagurl}">$tag($num)</a></nobr>/
END;
		}else {
			print <<<END
<nobr><b>$tag($num)</b></nobr>/
END;
		}
	}
	$num = count($linkdata);
	if ($find=='') {
		print <<<END
<nobr><b>すべて($num)</b></nobr></p>
END;
	}else {
		print <<<END
<nobr><a href="$setting[script]">すべて($num)</a></nobr></p>
END;
	}
	if ($setting['password']!='') $passform =
		'<tr><td>password</td><td><input type=password name=pass></td></tr>';
	print <<<END
<form action="$setting[script]" method=POST name=b style="text-align:center;">
<input type=hidden name=query value="$query">
<table cellspacing=0 cellpadding=0 border=0 align=right>
<tr><td>名前/画像</td><td><input type=text name=name title"画像可"></td></tr>
<tr><td>URL</td><td><input type=text name=url title="「http://」以外で始まるURLは不可"></td></tr>
<tr><td>タグ</td><td><input type=text name=tag title="「,」で区切る"></td></tr>
$passform
<tr><td><input type=radio name=id value=0 onclick="document.b.name.value='';document.b.url.value='';document.b.tag.value='';" checked>新規</td><td><select name=mode><option value=put>追加/修正</option><option value=remove>削除</option></select><input type=submit value="OK"></td></tr>
</table>
END;
	foreach ($linkdata as $link) {
		if ($find!='') {
			$found = false;
			foreach ($link['tag'] as $t) {
				if ($t==$find) {
					$found = true;
					continue;
				}
			}
			if (!$found) continue;
		}
		$banner = is_file($setting['imgdir'].$link['name'])?
			"<img src=\"$setting[imgdir]$link[name]\">":
			(strpos($link['name'],'://')?
			"<img src=\"$link[name]\">":$link['name']);
		$tag = implode(',',$link['tag']);
		$name = str_replace("'","\\'",$link['name']);
		print <<<END
<nobr><input type=radio name=id value=$link[id] onclick="document.b.name.value='$name';document.b.url.value='$link[url]';document.b.tag.value='$tag';"><a href="http://$link[url]" name=$link[id] onclick="window.open('http://$link[url]','_blank');location.href='$setting[script]?mode=score&amp;id=$link[id]&amp;query=$query2';return false;" title="$tag,$link[score]">$banner</a></nobr>
END;
	}
?>
</form></body></html>
<?php
}

function Score()	// ランク付け
{
	global $setting, $_REQUEST;
	$alldata = ReadLinks($setting['filename']);
	$linkdata = $alldata['link'];
	$query = stripslashes($_REQUEST['query']);
	if ($query!='') $query="?$query";
	ScoreLink($linkdata, $_REQUEST['id']);
	WriteLinks($setting['filename'], $linkdata);
	header("Location: $setting[script]$query");
}

function Put()	// 追加・修正
{
	global $setting, $_REQUEST;
	if (stripslashes($_REQUEST['pass'])!=$setting['password']) die('エラー！');
	$query = stripslashes($_REQUEST['query']);
	if ($query!='') $query="?$query";
	if ($_REQUEST['name']!='' && $_REQUEST['url']!='') {
		$alldata = ReadLinks($setting['filename']);
		$linkdata = $alldata['link'];
		$tagdata = $alldata['tag'];
		$new = array(
			'id'	=> $_REQUEST['id']>0?$_REQUEST['id']:time(),
			'name'	=> stripslashes($_REQUEST['name']),
			'url'	=> str_replace('http://','',stripslashes($_REQUEST['url'])),
			'tag'	=> stripslashes($_REQUEST['tag']),
			);
		PutLink($linkdata, $new);
		WriteLinks($setting['filename'], $linkdata);
	}
	header("Location: $setting[script]$query");
}

function Remove()	// 削除
{
	global $setting, $_REQUEST;
	if (stripslashes($_REQUEST['pass'])!=$setting['password']) die('エラー！');
	$query = stripslashes($_REQUEST['query']);
	if ($query!='') $query="?$query";
	if ($_REQUEST['id']>0) {
		$alldata = ReadLinks($setting['filename']);
		$linkdata = $alldata['link'];
		$tagdata = $alldata['tag'];
		RemoveLink($linkdata, $_REQUEST['id']);
		WriteLinks($setting['filename'], $linkdata);
	}
	header("Location: $setting[script]$query");
}


//--ここから先はこのプログラムの特に深い部分なのである--//

function ReadLinks($filename)	// 読み込み
{
	$linkdata = array();
	$tagdata = array();
	if ($fp = @fopen($filename, 'r')) {
		while (!feof($fp)) {
			$array = explode('<>', fgets($fp));
			if (is_array($array)&&count($array)>=5) {
				$id=$array[0]; $url=$array[1]; $name=$array[2]; $score=$array[3];
				$tag=array_filter(explode(',',$array[4]),'NotNullString');
				if (!is_array($tag)||count($tag)==0) $tag = array('タグ無し');
				array_push($linkdata,
					array('id'=>$id,'url'=>$url,'name'=>$name,'score'=>$score,'tag'=>$tag));
				foreach ($tag as $t) $tagdata[$t]++;
			}
		}
		fclose($fp);
	}
	return array('link'=>$linkdata,'tag'=>$tagdata);
}

function WriteLinks($filename, $linkdata)	// 書き出し
{
	$fp = fopen($filename, 'w');
	foreach ($linkdata as $l) {
		if (!is_array($l['tag'])) $l['tag'] = explode(',',$l['tag']);
		if (!is_array($l['tag'])) $l['tag'] = array();
		$tag = implode(',',array_filter($l['tag'],'NotNullString'));
		fwrite($fp, "$l[id]<>$l[url]<>$l[name]<>$l[score]<>$tag<>\n");
	}
	fclose($fp);
}

function PutLink(&$linkdata, $newdata)	// 追加・編集
{
	$found = false;
	$newdata['score']=0;
	foreach ($linkdata as $key=>$value) {
		if ($value['id']==$newdata['id']) {
			$newdata['score'] = $linkdata[$key]['score'];
			$linkdata[$key] = $newdata;
			$found = true;
		}
	}
	if (!$found) array_push($linkdata,$newdata);
	usort($linkdata, 'ScoreComp');
	reset($linkdata);
}

function RemoveLink(&$linkdata, $id)	// 削除
{
	global $removeid;
	$removeid = $id;
	$linkdata = array_filter($linkdata,'DifferentID');
}

function ScoreLink(&$linkdata, $id)	// スコア付け
{
	$rate = count($linkdata); $rate /= $rate+1;
	foreach ($linkdata as $key=>$value) {
		if ($value['id']==$id) $linkdata[$key]['score']++;
		$linkdata[$key]['score'] *= $rate;
	}
	usort($linkdata, 'ScoreComp');
	reset($linkdata);
}

function ScoreComp($a, $b)	// スコアによる順序付け
{
	if ($a['score'] == $b['score']) return 0;
	return ($a['score'] < $b['score']) ? 1 : -1;
}

function NotNullString($var)	// 文字列の中身を判定
{
	return $var!='';
}

function DifferentID($var)	// IDが異なるかどうかを判定
{
	global $removeid;
	return $var['id']!=$removeid;
}
?>