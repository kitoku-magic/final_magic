<?php

/**
 * ブラウザからリクエストされた際に一番最初に呼ばれるスクリプト
 *
 */

$framework_base_path = '/opt/final_magic';

// フレームワークのディレクトリを設定
set_include_path(get_include_path() . PATH_SEPARATOR . $framework_base_path);

// オートローダーの設定
require_once($framework_base_path . DIRECTORY_SEPARATOR . 'auto_loader.php');
spl_autoload_register(array(new auto_loader(), 'load'));

// コントローラーの処理実行
$controller = new controller();
$controller->set_framework_base_path($framework_base_path);
$controller->set_base_path('アプリケーションベースディレクトリのフルパス');
$controller->execute('アプリケーションの設定ファイル名');
