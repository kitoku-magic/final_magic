<?php

/**
 * データベースアクセス専用の基底クラス
 *
 * データベースアクセス処理に関する共通処理を定義
 *
 * @access  public
 * @create  2011/09/30
 * @version 0.1
 */
class dao
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
    $this->set_model(null);
    $this->set_config(null);
  }

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
   * モデルインスタンス設定
   *
   * @access public
   * @param model $model モデルインスタンス
   */
  public function set_model($model)
  {
    $this->model = $model;
  }

  /**
   * モデルインスタンス取得
   *
   * @access protected
   * @return model モデルインスタンス
   */
  protected function get_model()
  {
    return $this->model;
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
   * モデルインスタンス
   *
   * @access private
   */
  private $model;

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config;
}
