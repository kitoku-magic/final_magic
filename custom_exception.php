<?php

/**
 * 拡張例外クラス
 *
 * 独自機能を実装
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class custom_exception extends Exception
{

  /**
   * コンストラクタ
   *
   * @access public
   * @param string $message 例外メッセージ
   * @param int $code 例外コード
   */
  public function __construct($message, $code)
  {
    parent::__construct($message, $code);
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
   * ログ書き込みを行う
   *
   * @access public
   */
  public function write_log()
  {
    // リファラーがセットされていれば取得
    $referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : '';
    // ログに書き込む文字列を生成
    // 優先度中：getTraceAsString()は文字切れして表示されてしまう？要調査＆改善
    $error_string = date('Y/m/d H:i:s') . ' ' . $this->getFile() . '(' . $this->getLine() . ') ' . $this->getCode() . ' ' . $this->getMessage() . "\n" . $this->getTraceAsString() . "\n" . 'Referer = ' . $referer . "\n";
    // ログに書き込む
    file_put_contents($this->get_config()->get_base_path() . 'log/' . date('Ymd') . '.log', $error_string, FILE_APPEND);
  }

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config;
}
