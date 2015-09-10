<?php

//--------
// Gmail用
//--------
// SMTPサーバのホスト名
$SMTP_SERVER_NAME = 'tls://smtp.gmail.com';
// ポート番号
$PORT_NUMBER = '465';
// タイムアウト時間
$TIMEOUT = '3';
// SMTP-AUTHユーザー名（Gメールの場合は送信元のメールアドレス）
$SMTP_USER_NAME = 'XXXXXXXXXX';
// SMTP-AUTHパスワード（2段階認証をしている場合は、各アプリ毎に設定するアプリパスワード。 https://support.google.com/mail/answer/185833 などを参考に設定する）
$SMTP_PASSWORD = 'XXXXXXXXXX';
// SMTPクライアントのホスト名
$SMTP_CLIENT_NAME = 'localhost';
