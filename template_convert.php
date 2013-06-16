<?php

require_once('template_convert_single.php');
require_once('template_convert_multi.php');
require_once('template_convert_bool.php');

/**
 * テンプレート置換処理基底クラス
 *
 * HTMLテンプレートの置換を行う基底クラス
 *
 * @access  public
 * @create  2011/01/30
 * @version 0.1
 */
class template_convert
{

  /**
   * 単一値格納用の配列設定
   *
   * @access protected
   * @param array $single_array 単一値格納用の配列
   */
  protected function set_single_array($single_array)
  {
    $this->single_array = $single_array;
  }

  /**
   * 単一値格納用の配列取得
   *
   * @access protected
   * @return array 単一値格納用の配列
   */
  protected function get_single_array()
  {
    return $this->single_array;
  }

  /**
   * 単一値格納用の配列の値を取得
   *
   * @access public
   * @param string $single_name 配列に設定したキーの名前
   * @return mixed 単一値格納用の配列の値
   */
  public function get_single_array_val($single_name)
  {
    return $this->single_array[$single_name];
  }

  /**
   * 複数値格納用の配列設定
   *
   * @access protected
   * @param array $multi_array 複数値格納用の配列
   */
  protected function set_multi_array($multi_array)
  {
    $this->multi_array = $multi_array;
  }

  /**
   * 複数値格納用の配列取得
   *
   * @access protected
   * @return array 複数値格納用の配列
   */
  protected function get_multi_array()
  {
    return $this->multi_array;
  }

  /**
   * 複数値格納用の配列の値を取得
   *
   * @access public
   * @param string $multi_name 配列に設定したキーの名前
   * @return mixed 複数値格納用の配列の値
   */
  public function get_multi_array_val($multi_name)
  {
    return $this->multi_array[$multi_name];
  }

  /**
   * 複数値で値が一致時のデータ配列設定
   *
   * @access protected
   * @param array $multi_array_yes 複数値で値が一致時のデータ配列
   */
  protected function set_multi_array_yes($multi_array_yes)
  {
    $this->multi_array_yes = $multi_array_yes;
  }

  /**
   * 複数値で値が一致時のデータ配列取得
   *
   * @access protected
   * @return array 複数値で値が一致時のデータ配列
   */
  protected function get_multi_array_yes()
  {
    return $this->multi_array_yes;
  }

  /**
   * 複数値で値が一致時のデータ配列の値を取得
   *
   * @access public
   * @param string $multi_name 配列に設定したキーの名前
   * @return mixed 複数値で値が一致時のデータ配列の値
   */
  public function get_multi_array_yes_val($multi_name)
  {
    return $this->multi_array_yes[$multi_name];
  }

  /**
   * 複数値で値が不一致時のデータ配列設定
   *
   * @access protected
   * @param array $multi_array_no 複数値で値が不一致時のデータ配列
   */
  protected function set_multi_array_no($multi_array_no)
  {
    $this->multi_array_no = $multi_array_no;
  }

  /**
   * 複数値で値が不一致時のデータ配列取得
   *
   * @access protected
   * @return array 複数値で値が不一致時のデータ配列
   */
  protected function get_multi_array_no()
  {
    return $this->multi_array_no;
  }

  /**
   * 複数値で値が不一致時のデータ配列の値を取得
   *
   * @access public
   * @param string $multi_name 配列に設定したキーの名前
   * @return mixed 複数値で値が不一致時のデータ配列の値
   */
  public function get_multi_array_no_val($multi_name)
  {
    return $this->multi_array_no[$multi_name];
  }

  /**
   * 論理値格納用の配列設定
   *
   * @access protected
   * @param array $bool_array 論理値格納用の配列
   */
  protected function set_bool_array($bool_array)
  {
    $this->bool_array = $bool_array;
  }

  /**
   * 論理値格納用の配列取得
   *
   * @access protected
   * @return array 論理値格納用の配列
   */
  protected function get_bool_array()
  {
    return $this->bool_array;
  }

  /**
   * 論理値格納用の配列の値を取得
   *
   * @access public
   * @param string $bool_name 配列に設定したキーの名前
   * @return mixed 論理値格納用の配列の値
   */
  public function get_bool_array_val($bool_name)
  {
    return $this->bool_array[$bool_name];
  }

  /**
   * 単一値専用のテンプレート置換処理インスタンス設定
   *
   * @access public
   * @param template_convert_single $template_convert_single 単一値専用のテンプレート置換処理インスタンス
   */
  public function set_template_convert_single($template_convert_single)
  {
    $this->template_convert_single = $template_convert_single;
  }

  /**
   * 単一値専用のテンプレート置換処理インスタンス取得
   *
   * @access public
   * @return template_convert_single 単一値専用のテンプレート置換処理インスタンス
   */
  public function get_template_convert_single()
  {
    return $this->template_convert_single;
  }

  /**
   * 複数値専用のテンプレート置換処理インスタンス設定
   *
   * @access public
   * @param template_convert_multi $template_convert_multi 複数値専用のテンプレート置換処理インスタンス
   */
  public function set_template_convert_multi($template_convert_multi)
  {
    $this->template_convert_multi = $template_convert_multi;
  }

  /**
   * 複数値専用のテンプレート置換処理インスタンス取得
   *
   * @access public
   * @return template_convert_multi 複数値専用のテンプレート置換処理インスタンス
   */
  public function get_template_convert_multi()
  {
    return $this->template_convert_multi;
  }

  /**
   * 論理値専用のテンプレート置換処理インスタンス設定
   *
   * @access public
   * @param template_convert_bool $template_convert_bool 論理値専用のテンプレート置換処理インスタンス
   */
  public function set_template_convert_bool($template_convert_bool)
  {
    $this->template_convert_bool = $template_convert_bool;
  }

  /**
   * 論理値専用のテンプレート置換処理インスタンス取得
   *
   * @access public
   * @return template_convert_bool 論理値専用のテンプレート置換処理インスタンス
   */
  public function get_template_convert_bool()
  {
    return $this->template_convert_bool;
  }

  /**
   * 単一値の割り当て(HTMLエスケープ有)
   *
   * @access public
   * @param string $key 単一値のキー名
   * @param mixed $val 単一値
   */
  public function assign_single_array($key, $val)
  {
    // テンプレートの置換用に区切り文字のエスケープも同時に行う
    $this->single_array[$key] = $this->escape_delimiter_in_html_template(security::html_escape($val));
  }

  /**
   * 単一値の割り当て(HTMLエスケープ無しなので、取扱いは慎重に！)
   *
   * @access public
   * @param string $key 単一値のキー名
   * @param mixed $val 単一値
   */
  public function assign_single_array_no_escape($key, $val)
  {
    // テンプレートの置換用に区切り文字のエスケープも同時に行う
    $this->single_array[$key] = $this->escape_delimiter_in_html_template($val);
  }

  /**
   * 複数値の割り当て(セレクトボックス・チェックボックス・ラジオボタンで使用)
   *
   * @access public
   * @param string $key 複数値のキー名
   * @param mixed $val 複数値
   * @param string $yes 選択する項目のタグに設定する文字列(selected="selected"など)
   * @param string $no 選択しない項目のタグに設定する文字列(基本空文字'')
   */
  public function assign_multi_array($key, $val, $yes, $no)
  {
    // テンプレートの置換用に区切り文字のエスケープも同時に行う
    $this->multi_array[$key] = $this->escape_delimiter_in_html_template(security::html_escape($val));
    $this->multi_array_yes[$key] = $yes;
    $this->multi_array_no[$key] = $no;
  }

  /**
   * 論理値の割り当て
   *
   * @access public
   * @param string $key 論理値のキー名
   * @param mixed $obj 単一の論理値の場合はboolean、複数の論理値(ループ)の場合はarray
   */
  public function assign_bool_array($key, $obj = true)
  {
    if (true === is_array($obj))
    {
      // ループ
      $tmp = array();
      $obj_length = count($obj);
      // 表形式で$iは行数、$array_keyは列名を意味する
      for ($i = 0; $i < $obj_length; $i++)
      {
        foreach ($obj[$i] as $array_key => $array_val)
        {
          // テンプレートの置換用に区切り文字のエスケープも同時に行う
          $array_val = $this->escape_delimiter_in_html_template(security::html_escape($array_val));
          $obj[$i][$array_key] = $array_val;
        }
        $tmp[] = $obj[$i];
      }
      $this->bool_array[$key] = $tmp;
    }
    else
    {
      // 単一
      $this->bool_array[$key] = $obj;
    }
  }

  /**
   * HTMLテンプレートで使用されている区切り文字をエスケープする(優先度中：メソッド名が微妙。エスケープするのはHTMLテンプレート内の区切り文字ではなく、データの中の文字では？)
   *
   * @access protected
   * @param string $val エスケープする対象の文字列
   * @return string エスケープされた文字列
   */
  protected function escape_delimiter_in_html_template($val)
  {
    $val = str_replace('|||', '\|\|\|', $val);
    $val = str_replace(';;;', '\;\;\;', $val);
    $val = str_replace(':::', '\:\:\:', $val);

    return $val;
  }

  /**
   * 単一値格納用の配列
   *
   * @access private
   */
  private $single_array = array();

  /**
   * 複数値格納用の配列
   *
   * @access private
   */
  private $multi_array = array();

  /**
   * 複数値で値が一致時のデータ配列
   *
   * @access private
   */
  private $multi_array_yes = array();

  /**
   * 複数値で値が不一致時のデータ配列
   *
   * @access private
   */
  private $multi_array_no = array();

  /**
   * 論理値格納用の配列
   *
   * @access private
   */
  private $bool_array = array();

  /**
   * 単一値専用のテンプレート置換処理インスタンス
   *
   * @access private
   */
  private $template_convert_single = null;

  /**
   * 複数値専用のテンプレート置換処理インスタンス
   *
   * @access private
   */
  private $template_convert_multi = null;

  /**
   * 論理値専用のテンプレート置換処理インスタンス
   *
   * @access private
   */
  private $template_convert_bool = null;
}
