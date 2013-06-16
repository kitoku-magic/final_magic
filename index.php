<?php

/**
 * ブラウザからリクエストされた際に一番最初に呼ばれるスクリプト
 *
 */

// フレームワークのディレクトリを設定
set_include_path(get_include_path() . PATH_SEPARATOR . '/opt/final_magic/');

require_once('controller.php');

$controller = new controller();

// アプリケーションのベースディレクトリを設定
$app_base_dir = 'アプリケーションのベースディレクトリのフルパス';
$controller->set_base_path($app_base_dir);
// 設定ファイルを設定
$controller->set_config($app_base_dir . 'config/app_config.conf');
// コントローラーの処理実行
$controller->execute();
