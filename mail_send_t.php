<?php

// 対象日は前日
$target_day = date("Ymd", strtotime("-1 day"));
// 前日のアクセスログファイルの内容を全て取得する
$contents = file_get_contents('/opt/nginx/logs/access.log' . '-' . $target_day);

// ファイルパスを配列のキー名にし、値をアクセスされた数にして、配列に格納していく
$access_details = array();
$rows = explode("\n", $contents);
// 1行ずつ内容を整理していく
foreach ($rows as $row)
{
  $cols = explode(" ", $row);
  if (7 <= count($cols))
  {
    // cols[6]の中にアクセスパスが入っている
    if (true === array_key_exists($cols[6], $access_details))
    {
      $access_details[$cols[6]]++;
    }
    else
    {
      $access_details[$cols[6]] = 1;
    }
  }
}

arsort($access_details);

$body = '';
// 本文の内容は「ファイルパス アクセス数」として1行ずつ表示させる
foreach ($access_details as $file_path => $access_count)
{
  $body .= $file_path . ' ' . $access_count . "\n";
}

$ret = null;

$library_path = '/opt/final_magic';

require_once($library_path . '/mail_send.php');

$mail_obj = new mail_send();
$mail_obj->set_from('adihcu0@gmail.com');
$mail_obj->set_name_from('送信元サーバー');
$mail_obj->set_to('adihcu0@gmail.com');
$mail_obj->set_name_to('自分');
$mail_obj->set_cc('');
$mail_obj->set_name_cc('');
$mail_obj->set_bcc('');
$mail_obj->set_name_bcc('');
// 件名を「対象日 アクセス数」とする
$mail_obj->set_subject(date('Y/m/d', strtotime($target_day)) . ' アクセス数');
$mail_obj->set_body($body);
$ret = $mail_obj->send_mail($library_path);

echo $ret . "\n";
echo $mail_obj->get_smtp_response_message() . "\n";
