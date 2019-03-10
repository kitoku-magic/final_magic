<?php

/**
 * デバッグ用のユーティリティクラス
 *
 * デバッグに有用な機能を提供するクラス
 *
 * @access  public
 * @create  2011/01/30
 * @version 0.1
 */
class debug_util
{

  /**
   * コンストラクタ
   *
   * @access private
   */
  private function __construct()
  {
    // 外部からのインスタンス生成を禁止
  }

  /**
   * ファイルに書き込む
   *
   * @access public
   * @param string $write_file_path デバッグログの書き込み先ディレクトリのパス
   * @param string $val デバッグして見たい値
   */
  static public function write_to_file($write_file_path, $val)
  {
    // パスの末尾が'/'じゃない時には、'/'をくっ付ける
    if (0 !== strcmp('/', $write_file_path[mb_strlen($write_file_path) - 1]))
    {
      $write_file_path = $write_file_path . '/';
    }
    $write_file_name = $write_file_path . 'debug.log';

    file_put_contents($write_file_name, $val . PHP_EOL, FILE_APPEND);
  }

  /**
   *  関数の呼び出し元のファイル名や行数を取得する
   *
   * @access public
   * @return string 呼び出し元のファイル名と行数を連結した文字列
   */
  static public function get_function_call_position()
  {
    $tmp = debug_backtrace();
    return $tmp[1]['file'] . ':' . $tmp[1]['line'];
  }

  /**
   *  変数の中身の値を文字列として取得する
   *
   * @access public
   * @param mixed $dump_var 変数のダンプ結果を文字列として取得したい対象の変数
   * @return string 変数のダンプ結果を文字列化した内容
   */
  static public function get_var_dump_as_string($dump_var)
  {
    ob_start();
    var_dump($dump_var);
    $ret = rtrim(ob_get_contents());
    ob_end_clean();

    return $ret;
  }
}
