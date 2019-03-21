<?php

/**
 * 基底フォームクラス
 *
 * @access  public
 * @create  2019/03/10
 * @version 0.1
 */
abstract class form
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
    $this->set_error_counter(0);
    $this->set_recursive(false);
    $this->set_auth_error(false);
  }

  abstract public function get_all_properties();

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
   * フォームのアクセサ名を生成し実行する
   *
   * @access public
   * @param string $type setなどのアクセサの種類
   * @param string $name 実行するアクセサの対象となる変数名
   * @param string $param アクセサメソッドに渡す引数
   * @return mixed アクセサメソッドの結果を返す
   */
  public function execute_accessor_method($type, $name, $param = '')
  {
    // アクセサ名を作成
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
   * 全てのフォームデータをtrimする
   *
   * @access public
   */
  public function trim_all_data()
  {
    $properties = $this->get_all_properties();
    foreach ($properties as $field => $value)
    {
      if (true === is_string($value))
      {
        // 改行コードはtrimしない
        $this->execute_accessor_method('set', $field, utility::mb_trim($value, '(\x20|\x09|\x00|\x0b|　)'));
      }
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
