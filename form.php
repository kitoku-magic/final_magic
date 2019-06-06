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
  }

  abstract public function get_all_properties();

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
        $this->execute_accessor_method('set', $field, utility::mb_trim($value, '(\x20|\x09|\x00|\x0b|\x{3000})'));
      }
    }
  }
}
