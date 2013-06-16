<?php

/**
 * チェッククラス
 *
 * あらゆるチェックを行うクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class check
{

  /**
   * コンストラクタ
   *
   * @access private
   */
  private function __construct()
  {
    // 外部からのインスタンス生成を禁止
  }

  /**
   * 読み込むファイルの存在チェック
   *
   * @access public
   * @param string $file_name 読み込み対象のファイル名
   * @return boolean ファイルに問題なければtrueを返す
   */
  static public function check_exist_file($file_name)
  {
    if (0 !== strcmp('', $file_name) &&
      is_readable($file_name) &&
      is_file($file_name))
    {
      // ファイル名が空文字以外且つ、読み込み可能且つ、通常ファイルであればOK
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * インスタンスを生成するクラスの存在チェック
   *
   * @access public
   * @param string $class_name クラス名
   * @return boolean クラスが存在すればtrueを返す
   */
  static public function check_exist_class($class_name)
  {
    if (class_exists($class_name))
    {
      // クラスが定義済みであればOK
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * メールアドレス形式チェック
   *
   * @access public
   * @param string $check_val メールアドレス
   * @return boolean 形式が問題無ければtrueを返す
   */
  static public function check_email_format($check_val)
  {
    // 優先度中：pregな関数は使いたくないが、さすがにここは仕方ないか
    $result = preg_match('/\A([a-z0-9_\.\+\-])+@([a-z0-9\-]+\.)+[a-z]{2,6}\z/ui', $check_val);

    // 0かfalseの時は形式エラー
    if (0 === $result || false === $result)
    {
      return false;
    }
    else
    {
      return true;
    }

    // 優先度低：以下はcheckdnsrrの処理がかなり重いのでとりあえずコメント扱い
    /*$result = preg_match('/^([a-z0-9_\.\+\-])+@([a-z0-9\-]+\.)+[a-z]{2,6}$/i', $check_val);

    if (0 === $result || false === $result)
    {
      return false;
    }

    $host = explode('@', $check_val);

    if (2 !== count($host))
    {
      return false;
    }
    else if ( (false === checkdnsrr($host[1], 'MX')) && (false === checkdnsrr($host[1], 'A')) )
    {
      return false;
    }
    else
    {
      return true;
    }*/
  }

  /**
   * 確認用メアドと同一チェック
   *
   * @access public
   * @param string $email 入力されたメールアドレス
   * @param string $email_confirm 確認用メールアドレス
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_confirm_email_equal($email, $email_confirm)
  {
    return 0 === strcmp($email, $email_confirm);
  }

  /**
   * 先頭文字チェック(電話番号の先頭文字が0かどうか等で使う)
   *
   * @access public
   * @param string $check_val チェック対象の文字列
   * @param string $check_head_character チェックする先頭文字
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_head_character($check_val, $check_head_character)
  {
    // $check_valの１文字目と$check_head_characterが一致していたらOK
    return 0 === strcmp($check_head_character, mb_substr($check_val, 0, 1));
  }

  /**
   * 桁数超過チェック(文字数)
   *
   * @access public
   * @param string $check_val 桁数を調べる対象の文字列
   * @param int $length 許容可能な最大桁数
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_mb_max_length($check_val, $length)
  {
    // 文字数(非バイト数)が$length以下ならOK
    return mb_strlen($check_val) <= $length;
  }

  /**
   * 桁数不足チェック(文字数)
   *
   * @access public
   * @param string $check_val 桁数を調べる対象の文字列
   * @param int $length 許容可能な最小桁数
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_mb_min_length($check_val, $length)
  {
    // 文字数(非バイト数)が$length以上ならOK
    return mb_strlen($check_val) >= $length;
  }

  /**
   * 記号チェック(優先度高：全角記号のチェックが無い)
   *
   * @access public
   * @param string $check_val チェック対象の文字列
   * @param boolean $email_check_flag メールアドレスに含む記号のチェックか否か
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_sign($check_val, $email_check_flag)
  {
    // チェックする文字列の長さ
    $character_length = mb_strlen($check_val);

    for ($i = 0; $i < $character_length; $i++)
    {
      // １文字取得
      $character = mb_substr($check_val, $i, 1);
      // ASCIIコード値を取得
      $ascii_code = ord($character);
      // ASCIIコード値で判定
      // メールチェックの判断
      if (true === $email_check_flag)
      {
        // メールの場合以下の文字の時は有効とし再判定
        if (true === in_array($ascii_code, array(43, 45, 46, 64, 95)))
        {
          // + - . @ _
          continue;
        }
      }
      // 以下の文字の時はエラー
      // 読み方例：33は!で、右にずれる毎に1ずつ増えていき47は/となる
      if ((33 <= $ascii_code) && ($ascii_code <= 47))
      {
        // ! " # $ % & ' ( ) * + , - . /
        return false;
      }
      else if ((58 <= $ascii_code) && ($ascii_code <= 64))
      {
        // : ; < = > ? @
        return false;
      }
      else if ((91 <= $ascii_code) && ($ascii_code <= 96))
      {
        // [ \ ] ^ _ `
        return false;
      }
      else if ((123 <= $ascii_code) && ($ascii_code <= 126))
      {
        // { | } ~
        return false;
      }
    }

    return true;
  }

  /**
   * 半角文字チェック
   *
   * @access public
   * @param string $check_val チェック対象の文字列
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_single_character($check_val)
  {
    // バイト数と文字数が一致していなければ半角文字以外が含まれている
    return strlen($check_val) === mb_strlen($check_val);
  }

  /**
   * 半角英数チェック
   *
   * @access public
   * @param string $check_val チェック対象の文字列
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_single_alpha_numeric($check_val)
  {
    return ctype_alnum($check_val);
  }

  /**
   * 半角数字チェック
   *
   * @access public
   * @param string $check_val チェック対象の文字列
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_single_numeric($check_val)
  {
    return ctype_digit($check_val);
  }

  /**
   * 未入力チェック（trim有）
   *
   * @access public
   * @param string $check_val チェック対象の文字列
   * @param string $trim_character 削除したい文字のリスト
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_un_input_trim($check_val, $trim_character)
  {
    // 優先度低：trimは第２引数未指定でも半角スペースとタブ文字は削除される模様
    // よって$trim_characterは全角スペースのみでOK
    return mb_strlen(trim($check_val, $trim_character)) > 0;
  }

  /**
   * 未入力チェック（trim無）
   *
   * @access public
   * @param string $check_val チェック対象の文字列
   * @return boolean 問題無ければtrueを返す
   */
  static public function check_un_input_no_trim($check_val)
  {
    return mb_strlen($check_val) > 0;
  }

  /**
   * セレクトボックスなどの配列内の値の存在チェック
   *
   * @access public
   * @param string $search_val 探す対象の文字列
   * @param array $search_array 探される対象の配列
   * @return boolean 値が存在すればtrueを返す
   */
  static public function check_box_out_side_range($search_val, $search_array)
  {
    return in_array($search_val, $search_array);
  }
}
