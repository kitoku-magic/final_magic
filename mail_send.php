<?php

/**
 * メール送信クラス
 *
 * SMTPプロトコルと直接やり取りしてメールを送信するクラス
 *
 * @access  public
 * @create  2015/09/08
 * @version 0.1
 */
class mail_send
{
  /**
   * コンストラクタ
   *
   * @access public
   */
  public function __construct()
  {
    $this->init();
  }

  /**
   * 初期化処理
   *
   * @access public
   */
  public function init()
  {
    $this->smtp_connect = null;
    $this->smtp_response_message = '';
    $this->from = '';
    $this->name_from = '';
    $this->to = '';
    $this->name_to = '';
    $this->cc = '';
    $this->name_cc = '';
    $this->bcc = '';
    $this->name_bcc = '';
    $this->subject = '';
    $this->body = '';
  }

  /**
   * SMTP接続ソケットハンドル設定
   *
   * @access protected
   * @param resource $smtp_connect SMTP接続ソケットハンドル
   */
  protected function set_smtp_connect($smtp_connect)
  {
    $this->smtp_connect = $smtp_connect;
  }

  /**
   * SMTP接続ソケットハンドル取得
   *
   * @access protected
   * @return resource SMTP接続ソケットハンドル
   */
  protected function get_smtp_connect()
  {
    return $this->smtp_connect;
  }

  /**
   * SMTPサーバからのレスポンスメッセージ設定
   *
   * @access protected
   * @param string $smtp_response_message SMTPサーバからのレスポンスメッセージ
   */
  protected function set_smtp_response_message($smtp_response_message)
  {
    $this->smtp_response_message = $smtp_response_message;
  }

  /**
   * SMTPサーバからのレスポンスメッセージ取得
   *
   * @access public
   * @return string SMTPサーバからのレスポンスメッセージ
   */
  public function get_smtp_response_message()
  {
    return $this->smtp_response_message;
  }

  /**
   * 送信元メールアドレス設定
   *
   * @access public
   * @param string $from 送信元メールアドレス
   */
  public function set_from($from)
  {
    $this->from = $from;
  }

  /**
   * 送信元メールアドレス取得
   *
   * @access protected
   * @return string 送信元メールアドレス
   */
  protected function get_from()
  {
    return $this->from;
  }

  /**
   * 送信元の表示名設定
   *
   * @access public
   * @param string $name_from 送信元の表示名
   */
  public function set_name_from($name_from)
  {
    $this->name_from = $name_from;
  }

  /**
   * 送信元の表示名取得
   *
   * @access protected
   * @return string 送信元の表示名
   */
  protected function get_name_from()
  {
    return $this->name_from;
  }

  /**
   * 送信先メールアドレス設定
   *
   * @access public
   * @param string $to 送信先メールアドレス
   */
  public function set_to($to)
  {
    $this->to = $to;
  }

  /**
   * 送信先メールアドレス取得
   *
   * @access protected
   * @return string 送信先メールアドレス
   */
  protected function get_to()
  {
    return $this->to;
  }

  /**
   * 送信先の表示名設定
   *
   * @access public
   * @param string $name_to 送信先の表示名
   */
  public function set_name_to($name_to)
  {
    $this->name_to = $name_to;
  }

  /**
   * 送信先の表示名取得
   *
   * @access protected
   * @return string 送信先の表示名
   */
  protected function get_name_to()
  {
    return $this->name_to;
  }

  /**
   * CC先メールアドレス設定
   *
   * @access public
   * @param string $cc CC先メールアドレス
   */
  public function set_cc($cc)
  {
    $this->cc = $cc;
  }

  /**
   * CC先メールアドレス取得
   *
   * @access protected
   * @return string CC先メールアドレス
   */
  protected function get_cc()
  {
    return $this->cc;
  }

  /**
   * CC先の表示名設定
   *
   * @access public
   * @param string $name_cc 送信先の表示名
   */
  public function set_name_cc($name_cc)
  {
    $this->name_cc = $name_cc;
  }

  /**
   * CC先の表示名取得
   *
   * @access protected
   * @return string 送信先の表示名
   */
  protected function get_name_cc()
  {
    return $this->name_cc;
  }

  /**
   * BCC先メールアドレス設定
   *
   * @access public
   * @param string $bcc BCC先メールアドレス
   */
  public function set_bcc($bcc)
  {
    $this->bcc = $bcc;
  }

  /**
   * BCC先メールアドレス取得
   *
   * @access protected
   * @return string BCC先メールアドレス
   */
  protected function get_bcc()
  {
    return $this->bcc;
  }

  /**
   * BCC先の表示名設定
   *
   * @access public
   * @param string $name_bcc 送信先の表示名
   */
  public function set_name_bcc($name_bcc)
  {
    $this->name_bcc = $name_bcc;
  }

  /**
   * BCC先の表示名取得
   *
   * @access protected
   * @return string 送信先の表示名
   */
  protected function get_name_bcc()
  {
    return $this->name_bcc;
  }

  /**
   * メールの件名設定
   *
   * @access public
   * @param string $subject メールの件名
   */
  public function set_subject($subject)
  {
    $this->subject = $subject;
  }

  /**
   * メールの件名取得
   *
   * @access protected
   * @return string メールの件名
   */
  protected function get_subject()
  {
    return $this->subject;
  }

  /**
   * メール本文設定
   *
   * @access public
   * @param string $body メール本文
   */
  public function set_body($body)
  {
    $this->body = $body;
  }

  /**
   * メール本文取得
   *
   * @access protected
   * @return string メール本文
   */
  protected function get_body()
  {
    return $this->body;
  }

  /**
   * メールを送信する
   *
   * @access public
   * @param string $library_path フレームワークが格納されているディレクトリ
   * @return boolean メール送信に成功ならtrue、失敗ならfalse
   */
  public function send_mail($library_path)
  {
    $ret = false;

    // メールヘッダ内に不正な文字があるかどうかチェック
    if ('' !== ($ret = $this->check_mail_header()))
    {
      $this->set_smtp_response_message($ret);
      return $ret;
    }

    // メアドのドメインパートからキャリア名を取得する
    $career = $this->get_career_name();
    if (true === file_exists($library_path . '/mail_conf_' . $career . '.php'))
    {
      // キャリアに応じた設定ファイルを読み込む
      require_once('mail_conf_' . $career . '.php');
    }
    else
    {
      $this->set_smtp_response_message('キャリアに応じた設定ファイルが存在しない');
      return $ret;
    }

    try
    {
      // SMTPサーバのソケットを開く
      $this->set_smtp_connect(fsockopen($SMTP_SERVER_NAME, $PORT_NUMBER, $errno, $errstr, $TIMEOUT));
    }
    catch(Exception $e)
    {
      $this->set_smtp_response_message('fsockopen内でエラー発生: errno=' . $errno . ' errstr=' . $errstr);
      return $ret;
    }

    // 結果判定
    if (false !== $this->get_smtp_connect())
    {
      // ソケットオープン成功
      $this->get_smtp_message('CONNECTED: ');

      // SMTPセッションの開始を送信
      // EHLOがもしダメだったら、HELOでセッションを確立してみる
      if (true === $this->send_smtp_message('EHLO ' . $SMTP_CLIENT_NAME) ||
          true === $this->send_smtp_message('HELO ' . $SMTP_CLIENT_NAME))
      {
        $this->set_smtp_response_message($this->get_smtp_response_message() . 'EHLO/HELO: ');

        $smtp_response = '';

        // 複数行のレスポンスが返ってくる時があるので、繰り返す
        while (false !== ($smtp_response = fgets($this->get_smtp_connect(), 513)))
        {
          // 1行ずつレスポンスを設定していく
          $this->set_smtp_response_message($this->get_smtp_response_message() . $smtp_response);
          // 「250 」で始まるメッセージが返ってきたら処理を終了する
          if ((false !== ($pos = strpos($smtp_response, '250 '))) && 0 === $pos)
          {
            break;
          }
        }

        // SMTP-AUTHの開始を送信
        if (true === $this->send_smtp_message('AUTH LOGIN'))
        {
          $this->get_smtp_message('AUTH LOGIN: ');

          // SMTP-AUTHユーザー名を送信
          if (true === $this->send_smtp_message(base64_encode($SMTP_USER_NAME)))
          {
            $this->get_smtp_message('USERNAME: ');

            // SMTP-AUTHパスワードを送信
            if (true === $this->send_smtp_message(base64_encode($SMTP_PASSWORD)))
            {
              $this->get_smtp_message('PASSWORD: ');

              // 送信元を送信
              if (true === $this->send_smtp_message('MAIL FROM: ' . '<' . $this->get_from() . '>'))
              {
                $this->get_smtp_message('MAIL FROM: ');

                // 宛先を送信
                if (true === $this->send_smtp_message('RCPT TO: ' . '<' . $this->get_to() . '>'))
                {
                  $this->get_smtp_message('RCPT TO: ');

                  // データの開始を送信
                  if (true === $this->send_smtp_message('DATA'))
                  {
                    $this->get_smtp_message('DATA: ');

                    // 言語設定と内部エンコーディングを設定
                    mb_language('Japanese');
                    mb_internal_encoding('UTF-8');

                    // 宛先の表示名
                    $name_to = mb_encode_mimeheader($this->get_name_to());
                    // CC先の表示名
                    $name_cc = mb_encode_mimeheader($this->get_name_cc());
                    // BCC先の表示名
                    $name_bcc = mb_encode_mimeheader($this->get_name_bcc());
                    // 送信元の表示名
                    $name_from = mb_encode_mimeheader($this->get_name_from());
                    // 件名
                    $subject = mb_encode_mimeheader($this->get_subject());
                    // 本文
                    $body = mb_convert_encoding($this->get_body(), 'JIS');
                    // メール内容を送信
                    if (true === $this->send_smtp_message(
                          'From: ' . $name_from . ' <' . $this->get_from() . '>' . self::NEW_LINE .
                          'To: ' . $name_to . ' <' . $this->get_to() . '>' . self::NEW_LINE .
                          'Cc: ' . $name_cc . ' <' . $this->get_cc() . '>' . self::NEW_LINE .
                          'Bcc: ' . $name_bcc . ' <' . $this->get_bcc() . '>' . self::NEW_LINE .
                          'Subject: ' . $subject . self::NEW_LINE .
                          self::NEW_LINE .
                          $body . self::NEW_LINE .
                          '.'))
                    {
                      $smtp_response = fgets($this->get_smtp_connect(), 513);
                      $this->set_smtp_response_message($this->get_smtp_response_message() . 'CONTENTS: ' . $smtp_response);
                      // レスポンス内容が「250 」から始まっていれば成功
                      if (0 === strpos($smtp_response, '250 '))
                      {
                        // SMTPセッションの終了を送信
                        if (true === $this->send_smtp_message('QUIT'))
                        {
                          $this->get_smtp_message('QUIT: ');
                        }
                        else
                        {
                          $this->set_smtp_response_message($this->get_smtp_response_message() . 'SMTPセッションの終了の送信失敗');
                        }
                      }
                      else
                      {
                        // 送信失敗
                        $this->set_smtp_response_message($this->get_smtp_response_message() . 'コンテンツへのメッセージが250以外から始まった');
                      }
                    }
                    else
                    {
                      $this->set_smtp_response_message($this->get_smtp_response_message() . 'メール内容の送信失敗');
                    }
                  }
                  else
                  {
                    $this->set_smtp_response_message($this->get_smtp_response_message() . 'データの開始の送信失敗');
                  }
                }
                else
                {
                  $this->set_smtp_response_message($this->get_smtp_response_message() . '宛先の送信失敗');
                }
              }
              else
              {
                $this->set_smtp_response_message($this->get_smtp_response_message() . '送信元の送信失敗');
              }
            }
            else
            {
              $this->set_smtp_response_message($this->get_smtp_response_message() . 'SMTP-AUTHパスワードの送信失敗');
            }
          }
          else
          {
            $this->set_smtp_response_message($this->get_smtp_response_message() . 'SMTP-AUTHユーザー名の送信失敗');
          }
        }
        else
        {
          $this->set_smtp_response_message($this->get_smtp_response_message() . 'SMTP-AUTHの開始の送信失敗');
        }
      }
      else
      {
        $this->set_smtp_response_message($this->get_smtp_response_message() . 'SMTPセッションの開始の送信失敗');
      }
      // SMTPサーバのソケットを閉じる
      if (true === $this->disconnect_smtp_server())
      {
        $ret = true;
      }
      else
      {
        $this->set_smtp_response_message($this->get_smtp_response_message() . 'SMTPサーバとのソケット切断失敗');
      }
    }
    else
    {
      // ソケットオープン失敗
      $this->set_smtp_response_message('ソケットオープン失敗: errno=' . $errno . ' errstr=' . $errstr);
    }

    return $ret;
  }

  /**
   * メールヘッダに不正な入力値が無いかどうかチェックする
   *
   * @access private
   * @return string チェックが問題無ければ空文字、問題有ればエラーメッセージ
   */
  private function check_mail_header()
  {
    $method_names = array('name_from', 'from', 'name_to', 'to', 'name_cc', 'cc', 'name_bcc', 'bcc', 'subject');
    // メールヘッダの各項目を一つずつチェックしていく
    foreach ($method_names as $method_name)
    {
      $full_method_name = 'get_' . $method_name;
      $val = $this->$full_method_name();
      if (false !== strpos($val, "\r\n") || false !== strpos($val, "\r") || false !== strpos($val, "\n"))
      {
        // 改行コードが含まれているのでエラー
        return $method_name . 'の中に改行コードが含まれています。値=' . $val;
      }
      $full_method_name = 'set_' . $method_name;
      // ヌルバイトは置換
      $this->$full_method_name(str_replace("\0", "", $val));
    }

    return '';
  }

  /**
   * メールアドレスからキャリア名を取得する
   *
   * @access private
   * @return string キャリア名
   */
  private function get_career_name()
  {
    $mail_address = explode('@', $this->get_from());
    $domain_part = explode('.', $mail_address[1]);

    return $domain_part[0];
  }

  /**
   * SMTPサーバーへメッセージを送信する
   *
   * @access private
   * @param string $smtp_message 送信するメッセージ
   * @return mixed 成功した場合は送信したバイト数、失敗した場合はfalse
   */
  private function send_smtp_message($smtp_message)
  {
    return is_int(fputs($this->get_smtp_connect(), $smtp_message . self::NEW_LINE));
  }

  /**
   * SMTPサーバーから応答メッセージを取得する
   *
   * @access private
   * @param string $smtp_message 応答メッセージ
   */
  private function get_smtp_message($smtp_message)
  {
    $smtp_response = fgets($this->get_smtp_connect(), 513);
    $this->set_smtp_response_message($this->get_smtp_response_message() . $smtp_message . $smtp_response);
  }

  /**
   * SMTPサーバーとのソケット接続を切断する
   *
   * @access private
   * @return boolean 切断に成功したらtrue、失敗したらfalse
   */
  private function disconnect_smtp_server()
  {
    // SMTPサーバへのソケットを閉じる
    return fclose($this->get_smtp_connect());
  }

  // 改行文字定数(CRLF)
  const NEW_LINE = "\r\n";

  // SMTP接続ソケットハンドル
  private $smtp_connect;

  // SMTPサーバからのレスポンスメッセージ
  private $smtp_response_message;

  // 送信元メールアドレス
  private $from;

  // 送信元の表示名
  private $name_from;

  // 送信先メールアドレス
  private $to;

  // 送信先の表示名
  private $name_to;

  // CC先メールアドレス
  private $cc;

  // CC先の表示名
  private $name_cc;

  // BCC先メールアドレス
  private $bcc;

  // BCC先の表示名
  private $name_bcc;

  // メールの件名
  private $subject;

  // メール本文
  private $body;
}
