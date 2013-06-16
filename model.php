<?php

/**
 * 基底モデルクラス
 *
 * MVCAのMを担当するクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
abstract class model
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
    $this->set_error_counter(null);
    $this->set_recursive(false);
    $this->set_auth_error(false);
  }

  /**
   * エラーカウンター設定
   *
   * @access public
   * @param int $error_counter エラーカウンター
   */
  public function set_error_counter($error_counter)
  {
    $this->error_counter = $error_counter;
  }

  /**
   * エラーカウンター取得
   *
   * @access public
   * @return int エラーカウンター
   */
  public function get_error_counter()
  {
    return $this->error_counter;
  }

  /**
   * エラーカウンターアップ
   *
   * @access public
   */
  public function increment_error_counter()
  {
    $this->error_counter++;
  }

  /**
   * 再帰処理判定結果設定
   *
   * @access public
   * @param boolean $recursive 再帰処理判定結果
   */
  public function set_recursive($recursive)
  {
    $this->recursive = $recursive;
  }

  /**
   * 再帰処理判定結果取得
   *
   * @access public
   * @return boolean 再帰処理判定結果
   */
  public function is_recursive()
  {
    return $this->recursive;
  }

  /**
   * 認証エラー状態設定
   *
   * @access public
   * @param boolean $auth_error 認証エラー状態
   */
  public function set_auth_error($auth_error)
  {
    $this->auth_error = $auth_error;
  }

  /**
   * 認証エラー状態取得
   *
   * @access public
   * @return boolean 認証エラー状態
   */
  public function is_auth_error()
  {
    return $this->auth_error;
  }

  /**
   * モデルのアクセサ名を生成し実行する
   *
   * @access public
   * @param string $type setなどのアクセサの種類
   * @param string $name 実行するアクセサの対象となる変数名
   * @param string $param アクセサメソッドに渡す引数
   * @return mixed アクセサメソッドの結果を返す
   */
  public function create_accessor_name($type, $name, $param = '')
  {
    // アクセサ名を作成
    // 優先度中：get_nameでもgetNameでも対応出来る様にしておく
    $accessor_name = $type . '_' . $name;
    // アクセサ名の存在チェック
    if (true === method_exists($this, $accessor_name))
    {
      return call_user_func_array(array($this, $accessor_name), array($param));
    }
    else
    {
      return false;
    }
  }

  /**
   * エラーカウンター(優先度低：今後の展開次第だが、このフィールドはこのクラスに書かない方が良いかも)
   *
   * @access private
   */
  private $error_counter;

  /**
   * 再帰処理判定結果
   *
   * @access private
   */
  private $recursive;

  /**
   * 認証エラー状態
   *
   * @access private
   */
  private $auth_error;
}
