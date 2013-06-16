<?php

require_once('authentication.php');

/**
 * 認証管理クラス
 *
 * IDとパスワードによる認証を管理するクラス
 *
 * @access public
 * @create 2011/09/30
 * @version 0.1
 */
class authentication_id_pass extends authentication
{

  /**
   * コンストラクタ
   *
   * @access public
   */
  public function __construct()
  {
    parent::__construct();
    $this->init();
  }

  /**
   * 初期化処理
   *
   * @access protected
   */
  protected function init()
  {
    $this->set_id(null);
    $this->set_pass(null);
    $this->set_sql(null);
    $this->set_auth_data_type(null);
  }

  /**
   * 認証用ID設定
   *
   * @access public
   * @param string $id 認証用ID
   */
  public function set_id($id)
  {
    $this->id = $id;
  }

  /**
   * 認証用ID取得
   *
   * @access protected
   * @return string 認証用ID
   */
  protected function get_id()
  {
    return $this->id;
  }

  /**
   * パスワード設定
   *
   * @access public
   * @param string $pass パスワード
   */
  public function set_pass($pass)
  {
    $this->pass = $pass;
  }

  /**
   * パスワード取得
   *
   * @access protected
  * @return string パスワード
   */
  protected function get_pass()
  {
    return $this->pass;
  }

  /**
   * 認証用のSQL設定
   *
   * @access public
   * @param string $sql 認証用のSQL
   */
  public function set_sql($sql)
  {
    $this->sql = $sql;
  }

  /**
   * 認証用のSQL取得
   *
   * @access protected
  * @return string 認証用のSQL
   */
  protected function get_sql()
  {
    return $this->sql;
  }

  /**
   * 認証に使うカラムのデータ型設定
   *
   * @access public
   * @param string $auth_data_type 認証に使うカラムのデータ型
   */
  public function set_auth_data_type($auth_data_type)
  {
    $this->auth_data_type = $auth_data_type;
  }

  /**
   * 認証に使うカラムのデータ型取得
   *
   * @access protected
  * @return string 認証に使うカラムのデータ型
   */
  protected function get_auth_data_type()
  {
    return $this->auth_data_type;
  }

  /**
   * ジョーアカウント判定
   *
   * @access public
   * @return boolean ジョーアカウントだったらtrueを返す
   */
  public function is_joe_account()
  {
    return 0 === strcmp($this->get_id(), $this->get_pass());
  }

  /**
   * 単純なパスワードかどうかの判定
   *
   * @access public
   * @return boolean 単純なパスワードだったらtrueを返す
   */
  public function is_simple_password()
  {
    return 0 === strcasecmp('password', $this->get_pass());
  }

  /**
   * ユーザー認証判定
   *
   * @access public
   * @return boolean 認証OKならtrueを返す
   */
  public function is_authentication()
  {
    $ret = false;
    // SQL文セット
    $this->get_db_handle()->set_prepare_query($this->get_sql());
    // パラメータをバインドする為に配列化させる
    $param_array = $this->set_param_array_for_is_authentication();
    // プリペアドステートメントのパラメータに変数をバインド
    $this->get_db_handle()->set_bind_param($this->get_auth_data_type(), $param_array);
    // SQL実行
    $this->get_db_handle()->execute_query();
    // 結果を保存
    $this->get_db_handle()->set_store_result();
    // 結果を判断
    if (0 < $this->get_db_handle()->get_num_rows())
    {
      // 結果有
      $ret = true;
    }
    // プリペアドステートメントをクローズ
    $this->get_db_handle()->stmt_close();

    return $ret;
  }

  /**
   * パラメータをセット(ユーザー認証用)
   *
   * @access protected
   * @return array プリペアドステートメントのパラメータ格納配列
   */
  protected function set_param_array_for_is_authentication()
  {
    $param_array = array();
    // ログインID
    $param_array[0] = $this->get_id();
    // パスワード
    $param_array[1] = $this->get_password_to_hash();

    return $param_array;
  }

  /**
   * パスワードのハッシュ値を取得する
   *
   * @access protected
   * @return string ハッシュ化されたパスワード
   */
  protected function get_password_to_hash()
  {
    // ソルトを生成
    $salt = $this->create_salt();
    $hash = '';
    $stretching_count = $this->get_config()->search('stretching_count');
    for ($i = 0; $i < $stretching_count; $i++)
    {
      // ストレッチングを行う
      $hash = hash('sha512', $hash . $this->get_pass() . $salt);
    }

    return $hash;
  }

  /**
   * ソルトを生成する
   *
   * @access protected
   * @return string 生成されたソルト
   */
  protected function create_salt()
  {
    return $this->get_id() . pack('H*', $this->get_config()->search('salt'));
  }

  /**
   * 認証用ID
   *
   * @access private
   */
  private $id;

  /**
   * パスワード
   *
   * @access private
   */
  private $pass;

  /**
   * 認証用のSQL
   *
   * @access private
   */
  private $sql;

  /**
   * 認証に使うカラムのデータ型
   *
   * @access private
   */
  private $auth_data_type;
}
