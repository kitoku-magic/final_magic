<?php

/**
 * セキュリティクラス
 *
 * 各種のセキュリティ対策を行うクラス
 *
 * @access  public
 * @create  2012/01/15
 * @version 0.1
 */
class security
{

  /**
   * HTML出力用のエスケープ処理を行う(XSSで使う)
   *
   * @access public
   * @param mixed $val エスケープしたい値
   * @return mixed エスケープされた値
   */
  static public function html_escape($val)
  {
    if (true === is_array($val))
    {
      // 配列の時
      $arr = array();
      foreach ($val as $key => $val)
      {
        // 再帰呼び出しで値をエスケープしてから値をセット
        $arr[$key] = self::html_escape($val);
      }
      return $arr;
    }
    else
    {
      // 配列以外の時
      if (true === is_null($val))
      {
        // null
        return null;
      }
      else if (true === is_bool($val))
      {
        // 論理型
        return $val;
      }
      else
      {
        // 上記以外の時のみエスケープ
        return htmlspecialchars($val, ENT_QUOTES, mb_internal_encoding());
      }
    }
  }

  /**
   * 第三者が知り得ない秘密情報(CSRFトークン)の値を取得する
   *
   * @access public
   * @return string トークン値
   */
  static public function get_csrf_token()
  {
    return hash('sha512', file_get_contents('/dev/urandom', false, null, 0, 128));
  }

  /**
   * CSRFトークンの値をチェックする
   *
   * @access public
   * @return boolean チェックがOKならtrue、NGならfalse
   */
  static public function check_csrf_token($check_value)
  {
    // トークン確認
    if (true === isset($_POST['csrf_token']))
    {
      // hiddenからPOSTされたトークンとセッションIDが違う場合は不正な遷移
      if ($check_value !== $_POST['csrf_token'])
      {
        return false;
      }
    }
    else
    {
      // hiddenからPOSTされたトークンが未設定時も不正な遷移
      return false;
    }

    return true;
  }
}
