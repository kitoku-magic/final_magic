<?php

/**
 * DBハンドル基底クラス
 *
 * DBハンドル間で共通の処理を定義
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
abstract class db_handle
{
  /**
   * デストラクタでコネクションを切る
   *
   * @access public
   */
  public function __destruct()
  {
    $this->disconnect();
  }

  // 抽象関数各種

  /**
   * DB接続
   *
   * @access public
   */
  abstract public function connect();

  /**
   * DB切断
   *
   * @access public
   */
  abstract public function disconnect();

  /**
   * プリペアドステートメントを設定
   *
   * @access public
   * @param string $sql 実行するSQL文
   */
  abstract public function set_prepare_query($sql);

  /**
   * プリペアドステートメントのパラメータに変数をバインド
   *
   * @access public
   * @param string $variable_type パラメータ変数のデータ型
   * @param array $param_array パラメータのキーと値が格納されている配列
   */
  abstract public function set_bind_param($variable_type, $param_array);

  /**
   * プリペアドステートメントを実行
   *
   * @access public
   */
  abstract public function execute_query();

  /**
   * プリペアドステートメントの結果を保存
   *
   * @access public
   */
  abstract public function set_store_result();

  /**
   * プリペアドステートメントの結果を変数にバインド
   *
   * @access public
   * @param array $res_array SQL結果を保存する配列
   */
  abstract public function set_bind_result($res_array);

  /**
   * プリペアドステートメントの結果を連想配列形式の変数にバインド
   *
   * @access public
   */
  abstract public function set_bind_result_assoc();

  /**
   * 結果セットの行数を取得
   *
   * @access public
   */
  abstract public function get_num_rows();

  /**
   * 結果セットから１行取得(数値添字配列形式)
   *
   * @access public
   */
  abstract public function fetch_row();

  /**
   * 結果セットから１行取得(連想配列形式)
   *
   * @access public
   */
  abstract public function fetch_assoc();

  /**
   * トランザクション開始
   *
   * @access public
   */
  abstract public function begin_transaction();

  /**
   * 変更された行数を取得
   *
   * @access public
   */
  abstract public function get_affected_rows();

  /**
   * コミットする
   *
   * @access public
   */
  abstract public function commit();

  /**
   * ロールバックする
   *
   * @access public
   */
  abstract public function rollback();

  /**
   * プリペアドステートメントをクローズ
   *
   * @access public
   */
  abstract public function stmt_close();

  /**
   * 設定ファイルクラスインスタンス設定
   *
   * @access public
   * @param config $config 設定ファイルクラスインスタンス
   */
  public function set_config($config)
  {
    $this->config = $config;
  }

  /**
   * 設定ファイルクラスインスタンス取得
   *
   * @access public
   * @return config 設定ファイルクラスインスタンス
   */
  public function get_config()
  {
    return $this->config;
  }

  /**
   * ユーザー名設定
   *
   * @access public
   * @param string $user_name ユーザー名
   */
  public function set_user_name($user_name)
  {
    $this->user_name = $user_name;
  }

  /**
   * ユーザー名取得
   *
   * @access public
   * @return string ユーザー名
   */
  public function get_user_name()
  {
    return $this->user_name;
  }

  /**
   * パスワード設定
   *
   * @access public
   * @param string $password パスワード
   */
  public function set_password($password)
  {
    $this->password = $password;
  }

  /**
   * パスワード取得
   *
   * @access public
   * @return string パスワード
   */
  public function get_password()
  {
    return $this->password;
  }

  /**
   * データベース名設定
   *
   * @access public
   * @param string $database_name データベース名
   */
  public function set_database_name($database_name)
  {
    $this->database_name = $database_name;
  }

  /**
   * データベース名取得
   *
   * @access public
   * @return string データベース名
   */
  public function get_database_name()
  {
    return $this->database_name;
  }

  /**
   * ホスト名設定
   *
   * @access public
   * @param string $host_name ホスト名
   */
  public function set_host_name($host_name)
  {
    $this->host_name = $host_name;
  }

  /**
   * ホスト名取得
   *
   * @access public
   * @return string ホスト名
   */
  public function get_host_name()
  {
    return $this->host_name;
  }

  /**
   * ポート番号設定
   *
   * @access public
   * @param int $port_number ポート番号
   */
  public function set_port_number($port_number)
  {
    $this->port_number = $port_number;
  }

  /**
   * ポート番号取得
   *
   * @access public
   * @return int ポート番号
   */
  public function get_port_number()
  {
    return $this->port_number;
  }

  /**
   * エラーメッセージ設定
   *
   * @access public
   * @param string $error_message エラーメッセージ
   */
  public function set_error_message($error_message)
  {
    $this->error_message = $error_message;
  }

  /**
   * エラーメッセージ取得
   *
   * @access public
   * @return string エラーメッセージ
   */
  public function get_error_message()
  {
    return $this->error_message;
  }

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config;

  /**
   * ユーザー名
   *
   * @access private
   */
  private $user_name;

  /**
   * パスワード
   *
   * @access private
   */
  private $password;

  /**
   * データベース名
   *
   * @access private
   */
  private $database_name;

  /**
   * ホスト名
   *
   * @access private
   */
  private $host_name;

  /**
   * ポート番号
   *
   * @access private
   */
  private $port_number;

  /**
   * エラーメッセージ
   *
   * @access private
   */
  private $error_message;
}
