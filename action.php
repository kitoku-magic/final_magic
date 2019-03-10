<?php

require_once('template_convert.php');

/**
 * 基底アクションクラス
 *
 * MVCAのAを担当するクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
abstract class action
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
    $this->set_config(null);
    $this->set_form(null);
    $this->set_model(null);
    $this->set_db_handle(null);
    $this->set_template_convert(null);
    $this->set_template_file_path('');
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
   * フォームインスタンス設定
   *
   * @access public
   * @param form $form フォームインスタンス
   */
  public function set_form($form)
  {
    $this->form = $form;
  }

  /**
   * フォームインスタンス取得
   *
   * @access public
   * @return form フォームインスタンス
   */
  public function get_form()
  {
    return $this->form;
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
   * @access public
   * @return model モデルインスタンス
   */
  public function get_model()
  {
    return $this->model;
  }

  /**
   * DBハンドル設定
   *
   * @access public
   * @param db_handle $db_handle DBハンドルクラスインスタンス
   */
  public function set_db_handle($db_handle)
  {
    $this->db_handle = $db_handle;
  }

  /**
   * DBハンドル取得
   *
   * @access public
   * @return db_handle DBハンドルクラスインスタンス
   */
  public function get_db_handle()
  {
    return $this->db_handle;
  }

  /**
   * テンプレート置換処理インスタンス設定
   *
   * @access public
   * @param template_convert $template_convert テンプレート置換処理インスタンス
   */
  public function set_template_convert($template_convert)
  {
    $this->template_convert = $template_convert;
  }

  /**
   * テンプレート置換処理インスタンス取得
   *
   * @access public
   * @return template_convert テンプレート置換処理インスタンス
   */
  public function get_template_convert()
  {
    return $this->template_convert;
  }

  /**
   * テンプレートファイルパス設定
   *
   * @access protected
   * @param string $template_file_path テンプレートファイルパス
   */
  public function set_template_file_path($template_file_path)
  {
    $this->template_file_path = $template_file_path;
  }

  /**
   * テンプレートファイルパス取得
   *
   * @access public
   * @return string テンプレートファイルパス
   */
  public function get_template_file_path()
  {
    return $this->template_file_path;
  }

  /**
   * ビジネスロジックを実行する。
   * 個別actionにexecuteメソッドが無い時(認証時)に実行される
   *
   * @access public
   */
  public function execute()
  {
    // ユーザーの認証セッションの場合、定期的にセッションIDを切り替える
    // セッションIDの更新処理
    if (true === isset($_SESSION['auth_login_id']))
    {
      // 10分以上経過していたらセッションを無効にする
      if (($_SESSION['session_time'] + (10 * 60)) < time())
      {
        // 「ログインしている」というデータを消す
        unset($_SESSION['auth_login_id']);
      }
      else
      {
        // 一定時間(ここでは５分)経過していたらセッションIDを更新する
        if (($_SESSION['session_time'] + (5 * 60)) < time())
        {
          // セッションIDの変更と古いセッションの破棄
          session_regenerate_id(true);
          // セッション基準時間を更新
          $_SESSION['session_time'] = time();
        }
      }
    }

    // 認証状態のチェック
    if (false === isset($_SESSION['auth_login_id']))
    {
      // 認証されていないかタイムアウト
      $this->get_model()->set_auth_error(true);
      return;
    }
    // ビジネスロジックの実行
    $this->execute_auth();
  }

  /**
   * 全てのフォームデータをModelのセッターに設定する
   *
   * @access public
   * @param array $request_parameter_array 設定したいリクエストパラメータの種類が格納されている配列($_GET,$_POST,$_COOKIE)
   */
  public function set_form_to_model($request_parameter_array)
  {
    foreach ($request_parameter_array as $request_parameter_name)
    {
      foreach ($request_parameter_name as $key => $val)
      {
        // モデルのセッターに値を設定していく
        $this->get_model()->create_accessor_name('set', $key, $val);
      }
    }
  }

  /**
   * 全てのフォームデータをフォームクラスに設定する
   *
   * @access public
   * @param array $request_parameter_array 設定したいリクエストパラメータの種類が格納されている配列($_GET,$_POST,$_COOKIE)
   */
  public function set_form_data($request_parameter_array)
  {
    foreach ($request_parameter_array as $request_parameter_name)
    {
      foreach ($request_parameter_name as $key => $val)
      {
        // フォームのセッターに値を設定していく
        $this->get_form()->execute_accessor_method('set', $key, $val);
      }
    }
  }

  /**
   * Modelを連想配列化したデータをセッションデータに設定する
   *
   * @access protected
   * @param array $model_array 連想配列化したモデルクラスインスタンス
   * @param array $no_session_array セッションデータに設定しない項目名がキーになっている配列
   */
  protected function set_model_to_session($model_array, $no_session_array = array())
  {
    foreach ($model_array as $key => $val)
    {
      // キーがセッションに設定しない配列に含まれていなければデータをセット
      if (false === in_array($key, $no_session_array))
      {
        $_SESSION[$key] = $val;
      }
    }
  }

  /**
   * ビジネスロジックを実行する(認証後に下記のexecuteメソッドから呼ぶ)
   *
   * @access protected
   */
  protected function execute_auth()
  {
  }

  /**
   * HTMLに表示するデータのセット
   *
   * @access protected
   */
  protected function set_html()
  {
  }

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config;

  /**
   * フォームインスタンス
   *
   * @access private
   */
  private $form;

  /**
   * モデルインスタンス
   *
   * @access private
   */
  private $model;

  /**
   * DBハンドルインスタンス
   *
   * @access private
   */
  private $db_handle;

  /**
   * テンプレート置換処理インスタンス
   *
   * @access private
   */
  private $template_convert;

  /**
   * テンプレートファイルパス
   *
   * @access private
   */
  private $template_file_path;
}
