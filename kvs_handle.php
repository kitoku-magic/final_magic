<?php

/**
 * KVSハンドル基底クラス
 *
 * KVSハンドル間で共通の処理を定義
 *
 * @access  public
 * @create  2015/10/10
 * @version 0.1
 */
abstract class kvs_handle
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
   * KVS接続
   *
   * @access public
   */
  abstract public function connect();

  /**
   * KVS切断
   *
   * @access public
   */
  abstract public function disconnect();

  /**
   * データ設定
   *
   * @access public
   * @param string $key キー名
   * @param string $val 設定したい値
   */
  abstract public function set($key, $val);

  /**
   * データ取得
   *
   * @access public
   * @param string $key キー名
	 * @return string キー名に対応した値
   */
  abstract public function get($key);

  /**
   * データ削除
   *
   * @access public
   * @param string $key キー名
   */
  abstract public function delete($key);

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
   * memcache接続オブジェクト設定
   *
   * @access public
   * @param Memcached $conn memcache接続オブジェクト
   */
  public function set_conn($conn)
  {
    $this->conn = $conn;
  }

  /**
   * memcache接続オブジェクト取得
   *
   * @access public
   * @return Memcached memcache接続オブジェクト
   */
  public function get_conn()
  {
    return $this->conn;
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
   * KVS接続オブジェクト
   *
   * @access private
   */
  private $conn;

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
