<?php

// �������݃����N�W ver1.05
//
//  ����P�̂ł͓����܂���B
//  �ʓr������Ăяo��PHP��p�ӂ��āA
//   include 'flinks.php';
//  �ł��̃X�N���v�g��ǂݍ��݁A
//   $setting['script']   (�X�N���v�g�̃t�@�C����)
//   $setting['filename'] (�f�[�^������t�@�C��)
//   $setting['imgdir']   (�o�i�[�̂���f�B���N�g���B�ȗ��B1.04���z���)
//   $setting['password'] (�p�X���[�h�B�ȗ���)
//   $setting['tagsort']  (1.01���B�^�O����ёւ��邩�B�ȗ���)
//   $setting['combobox'] (1.02���B�^�O���R���{�{�b�N�X�ŕ\�����邩�B�ȗ���)
//   $setting['lockfile'] (1.03���B�t�@�C�����b�N�̂��߂̈ꎞ�t�@�C���B�ȗ���)
//  ��ݒ肵�āA
//   Main();
//  ���Ăяo�����Ƃŏ��߂ē����܂��B
//  �o�i�[�͎��O�ŃA�b�v���[�h���邩�AURL���ڎw�肷��
//  �K�v������̂ő����s�ւ�������܂���B
//  ��̓I�ɂ͕t����links.php���Q�Ƃ��Ă��������B
//
//  ���̃X�N���v�g���g�͌����Ƃ��ĉ��ς��Ȃ��ł��������B
//  �������AMainPage�֐�����HTML�͕K�v�ɉ����ď��������Ă��悢�ł��傤�B
//
//  ��ҁF����(http://tgws.fromc.jp/ mifumi323@tgws.fromc.jp)

function Main()	// ������Ăяo���ĊJ�n
{
	global $_REQUEST;
	switch ($_REQUEST['mode']) {
	case 'score':	Score(); break;
	case 'put':	Put(); break;
	case 'remove':	Remove(); break;
	default:	MainPage(); break;
	}
}

function MainPage()	// �ʏ�\��
{
	global $setting, $_REQUEST, $_SERVER;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<title>�����N�W</title>
</head><body class=wide>
<h1 style="float:left;">�����N�W</h1>
<?php
	$alldata = ReadLinks($setting['filename']);
	$linkdata = $alldata['link'];
	$tagdata = $alldata['tag'];
	$find = stripslashes($_REQUEST['tag']);
	$query = stripslashes($_SERVER['QUERY_STRING']);
	$query2 = urlencode($query);
	if ($setting['combobox'])
	{
		print <<<END
<form action="$setting[script]" method="get" style="text-align:right;">
<select name=tag>
END;
	}else {
		print <<<END
<p style="text-align:right;font-size:80%;">
END;
	}
	if ($setting['tagsort'])
	{
		ksort($tagdata);
		reset($tagdata);
	}
	foreach ($tagdata as $tag => $num) {
		$tagurl = urlencode($tag);
		if ($tag != $find) {
			if ($setting['combobox'])
			{
				print <<<END
<option value="$tag">$tag($num)</option>
END;
			}else {
				print <<<END
<nobr><a href="{$setting[script]}?tag={$tagurl}">$tag($num)</a></nobr>/
END;
			}
		}else {
			if ($setting['combobox'])
			{
				print <<<END
<option value="$tag" selected>$tag($num)</option>
END;
			}else {
				print <<<END
<nobr><b>$tag($num)</b></nobr>/
END;
			}
		}
	}
	$num = count($linkdata);
	if ($find=='') {
		if ($setting['combobox'])
		{
			print <<<END
<option value="" selected>���ׂ�($num)</option>
</select><input type=submit value="�\��"></form>
END;
		}else {
			print <<<END
<nobr><b>���ׂ�($num)</b></nobr></p>
END;
		}
	}else {
		if ($setting['combobox'])
		{
			print <<<END
<option value="">���ׂ�($num)</option>
</select><input type=submit value="�\��"></form>
END;
		}else {
			print <<<END
<nobr><a href="$setting[script]">���ׂ�($num)</a></nobr></p>
END;
		}
	}
	if ($setting['password']!='') $passform =
		'<tr><td>password</td><td><input type=password name=pass></td></tr>';
	print <<<END
<form action="$setting[script]" method=POST name=b style="text-align:center;">
<input type=hidden name=query value="$query">
<table cellspacing=0 cellpadding=0 border=0 align=right>
<tr><td>���O/�摜</td><td><input type=text name=name title="�摜��"></td></tr>
<tr><td>URL</td><td><input type=text name=url title="�uhttp://�v�͏ȗ��\"></td></tr>
<tr><td>�^�O</td><td><input type=text name=tag title="�u,�v�ŋ�؂�"></td></tr>
$passform
<tr><td><input type=radio name=id value=0 onclick="document.b.name.value='';document.b.url.value='';document.b.tag.value='';" checked>�V�K</td><td><select name=mode><option value=put>�ǉ�/�C��</option><option value=remove>�폜</option></select><input type=submit value="OK"></td></tr>
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
		$imgfile = TryGetImage($link['name']);
		$banner = isset($imgfile)?"<img src=\"$imgfile\">":$link['name'];
		$tag = implode(',',$link['tag']);
		$name = str_replace("'","\\'",$link['name']);
		$url = $link['url'];
		if (strpos($url,'://')===false) $url = 'http://'.$url;
		print <<<END
<nobr><input type=radio name=id value=$link[id] onclick="document.b.name.value='$name';document.b.url.value='$url';document.b.tag.value='$tag';"><a href="$url" name=$link[id] onclick="window.open('$url','_blank');location.href='$setting[script]?mode=score&amp;id=$link[id]&amp;query=$query2';return false;" title="$tag,$link[score]">$banner</a></nobr>
END;
	}
?>
</form></body></html>
<?php
}

function Score()	// �����N�t��
{
	global $setting, $_REQUEST;
	if (!Lock()) die('�G���[�F���b�N���s');
	$alldata = ReadLinks($setting['filename']);
	$linkdata = $alldata['link'];
	$query = stripslashes($_REQUEST['query']);
	if ($query!='') $query="?$query";
	ScoreLink($linkdata, $_REQUEST['id']);
	WriteLinks($setting['filename'], $linkdata);
	Unlock();
	header("Location: $setting[script]$query");
}

function Put()	// �ǉ��E�C��
{
	global $setting, $_REQUEST;
	if (stripslashes($_REQUEST['pass'])!=$setting['password']) die('�G���[�I');
	$query = stripslashes($_REQUEST['query']);
	if ($query!='') $query="?$query";
	if ($_REQUEST['name']!='' && $_REQUEST['url']!='') {
		if (!Lock()) die('�G���[�F���b�N���s');
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
		Unlock();
	}
	header("Location: $setting[script]$query");
}

function Remove()	// �폜
{
	global $setting, $_REQUEST;
	if (stripslashes($_REQUEST['pass'])!=$setting['password']) die('�G���[�I');
	$query = stripslashes($_REQUEST['query']);
	if ($query!='') $query="?$query";
	if ($_REQUEST['id']>0) {
		if (!Lock()) die('�G���[�F���b�N���s');
		$alldata = ReadLinks($setting['filename']);
		$linkdata = $alldata['link'];
		$tagdata = $alldata['tag'];
		RemoveLink($linkdata, $_REQUEST['id']);
		WriteLinks($setting['filename'], $linkdata);
		Unlock();
	}
	header("Location: $setting[script]$query");
}


//--���������͂��̃v���O�����̓��ɐ[�������Ȃ̂ł���--//

function ReadLinks($filename)	// �ǂݍ���
{
	$linkdata = array();
	$tagdata = array();
	if ($fp = @fopen($filename, 'r')) {
		while (!feof($fp)) {
			$array = explode('<>', fgets($fp));
			if (is_array($array)&&count($array)>=5) {
				$id=$array[0]; $url=$array[1]; $name=$array[2]; $score=$array[3];
				$tag=array_filter(explode(',',$array[4]),'NotNullString');
				if (!is_array($tag)||count($tag)==0) $tag = array('�^�O����');
				array_push($linkdata,
					array('id'=>$id,'url'=>$url,'name'=>$name,'score'=>$score,'tag'=>$tag));
				foreach ($tag as $t) $tagdata[$t]++;
			}
		}
		fclose($fp);
	}
	return array('link'=>$linkdata,'tag'=>$tagdata);
}

function WriteLinks($filename, $linkdata)	// �����o��
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

function PutLink(&$linkdata, $newdata)	// �ǉ��E�ҏW
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

function RemoveLink(&$linkdata, $id)	// �폜
{
	global $removeid;
	$removeid = $id;
	$linkdata = array_filter($linkdata,'DifferentID');
}

function ScoreLink(&$linkdata, $id)	// �X�R�A�t��
{
	$rate = count($linkdata); $rate /= $rate+1;
	foreach ($linkdata as $key=>$value) {
		if ($value['id']==$id) $linkdata[$key]['score']++;
		$linkdata[$key]['score'] *= $rate;
	}
	usort($linkdata, 'ScoreComp');
	reset($linkdata);
}

function ScoreComp($a, $b)	// �X�R�A�ɂ�鏇���t��
{
	if ($a['score'] == $b['score']) return 0;
	return ($a['score'] < $b['score']) ? 1 : -1;
}

function NotNullString($var)	// ������̒��g�𔻒�
{
	return $var!='';
}

function DifferentID($var)	// ID���قȂ邩�ǂ����𔻒�
{
	global $removeid;
	return $var['id']!=$removeid;
}

function Lock()	// ���b�N����(������true)
{
	global $setting, $is_locked;
	$lockfile = $setting['lockfile'];
	if (!$lockfile) return true;
	clearstatcache();
	// PHP�̓f�t�H���g��30�b�Ń^�C���A�E�g����̂Œʏ�̎��s��60�b�ȏ�O�̃��b�N�t�@�C�����c�邱�Ƃ͂Ȃ�(=�ȑO�Ɉُ�I������)
	if (is_dir($lockfile) && time()-filemtime($lockfile)>60) Unlock(true);
	$retry = 5;
	while (!@mkdir($lockfile, 0755)) {
		if (--$retry <= 0) return false;
		sleep(1);
	}
	return $is_locked=true;
}

function Unlock($forceunlock=false)	// ���b�N���Ă����烍�b�N��������
{
	global $setting, $is_locked;
	if (!$is_locked&&!$forceunlock) return;
	$lockfile = $setting['lockfile'];
	if (!$lockfile) return;
	if (is_dir($lockfile)) rmdir($lockfile);
	$is_locked = false;
}

function TryGetImage($filename, $imgdir=NULL)	// �摜�擾(���s������NULL)
{
	if (strpos($filename,'://')) {
		return $filename;
	}
	if (!isset($imgdir)) {
		global $setting;
		$imgdir = $setting['imgdir'];
	}
	if (is_array($imgdir)) {
		foreach ($imgdir as $dir) {
			$file = TryGetImage($filename, $dir);
			if (isset($file)) return $file;
		}
		return NULL;
	}else {
		if (is_file($imgdir.$filename)) {
			return $imgdir.$filename;
		}else {
			return NULL;
		}
	}
}

?>