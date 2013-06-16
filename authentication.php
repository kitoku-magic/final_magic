<?php

/**
 * 認証管理クラス
 *
 * ユーザー認証に関連する処理を定義した基底クラス
 *
 * @access  public
 * @create  2011/09/30
 * @version 0.1
 */
abstract class authentication
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
   * @access protected
   */
  protected function init()
  {
    $this->set_db_handle(null);
    $this->set_config(null);
  }

  /**
   * ユーザー認証判定
   *
   * @access public
   */
  abstract public function is_authentication();

  /**
   * DBハンドルインスタンス設定
   *
   * @access public
   * @param db_handle $db_handle DBハンドルインスタンス
   */
  public function set_db_handle($db_handle)
  {
    $this->db_handle = $db_handle;
  }

  /**
   * DBハンドルインスタンス取得
   *
   * @access protected
   * @return db_handle DBハンドルインスタンス
   */
  protected function get_db_handle()
  {
    return $this->db_handle;
  }

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
   * @access protected
   * @return config 設定ファイルクラスインスタンス
   */
  protected function get_config()
  {
    return $this->config;
  }

  /**
   * DBハンドルインスタンス
   *
   * @access private
   */
  private $db_handle;

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config;
}
