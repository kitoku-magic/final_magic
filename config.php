<?php

/**
 * 設定ファイル管理クラス
 *
 * 設定ファイル内の値の設定や値の取得を行うクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class config
{

  /**
   * コンストラクタ
   *
   * @access public
   */
  public function __construct()
  {
    $this->list = array();
  }

  /**
   * アプリケーションベースディレクトリのパス設定
   *
   * @access public
   * @param string $base_path アプリケーションベースディレクトリのパス
   */
  public function set_base_path($base_path)
  {
    $this->base_path = $base_path;
  }

  /**
   * アプリケーションベースディレクトリのパス取得
   *
   * @access public
   * @return string アプリケーションベースディレクトリのパス
   */
  public function get_base_path()
  {
    return $this->base_path;
  }

  /**
   * 設定値を検索して取得
   *
   * @access public
   * @param string $name 設定値のキー
   * @return string 設定値
   */
  public function search($name)
  {
    // nameがキーになっている値を返す
    return $this->list[$name];
  }

  /**
   * 設定ファイルをパースして値をセットする
   *
   * @access public
   * @param string $file_name 設定ファイル名
   */
  public function set_config_data($file_name)
  {
    // ファイル内の情報を配列に格納
    $lines = file($file_name);

    foreach ($lines as $line)
    {
      $temp_list = array();

      // 前後の空白削除
      $line = trim($line);

      // 空行は処理省略
      if (0 === strcmp('', $line))
      {
        continue;
      }

      // コメント行も処理省略
      // 優先度低：#の方が良いのでは？
      if (0 === strcmp('/', $line[0]))
      {
        continue;
      }

      // 先頭文字が=(nameが存在しない)の場合も処理省略
      if (0 === strcmp('=', $line[0]))
      {
        continue;
      }

      // "="で分割
      $temp_list = explode('=', $line);

      // データを配列に追加
      $this->add_list(trim($temp_list[0]), trim($temp_list[1]));
    }
  }

  /**
   * 設定値を配列に追加
   *
   * @access private
   * @param string $name 設定値のキー
   * @param string $val 設定値
   */
  protected function add_list($name, $val)
  {
    // 優先度中：データとして「,」が有った場合に大丈夫？
    if (false === mb_strpos($val, ','))
    {
      // 非配列データなのでそのまま設定
      $this->list[$name] = $val;
    }
    else
    {
      // 配列データ
      $val_array = explode(',', $val);
      $val_array_length = count($val_array);
      for ($i = 0; $i < $val_array_length; $i++)
      {
        $this->list[$name][] = $val_array[$i];
      }
    }
  }

  /**
   * 設定値格納配列
   *
   * @access private
   */
  private $list;

  /**
   * アプリケーションベースディレクトリのパス
   *
   * @access private
   */
  private $base_path;
}
