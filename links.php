<?php

// 呼び出し側PHP
//
//  これをコピーすれば複数のリンク集を作れるし、
//  Main();の前後に処理を書けば
//  ある程度の機能のカスタマイズも可能。

include 'flinks.php';

$setting['script'] = 'links.php';
$setting['filename'] = 'links.dat';
$setting['imgdir'] = 'banner/';
$setting['password'] = '';

Main();

?>