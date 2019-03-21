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
   * @param string $message メッセージ
   * @param mixed $original_code コード
   * @param int $severity 深刻度（エラーレベル）
   * @param string $file_name ファイル名
   * @param int $line_no 行数
   * @param Exception $previous 前に発生した例外
   */
  public function __construct(
    $message,
    $original_code,
    $severity = E_ERROR,
    $file_name = __FILE__,
    $line_no = __LINE__,
    $previous = null
  ) {
    parent::__construct($message, 0);
    $this->set_message($message);
    $this->set_original_code($original_code);
    $this->set_severity($severity);
    $this->set_file_name($file_name);
    $this->set_line_no($line_no);
    $this->set_previous($previous);
  }

  /**
   * メッセージ設定
   *
   * @access protected
   * @param string $message メッセージ
   */
  protected function set_message($message)
  {
    $this->message = $message;
  }

  /**
   * メッセージ取得
   *
   * @access protected
   * @return string メッセージ
   */
  public function get_message()
  {
    return $this->message;
  }

  /**
   * コード設定
   *
   * @access protected
   * @param mixed $original_code コード
   */
  protected function set_original_code($original_code)
  {
    $this->original_code = $original_code;
  }

  /**
   * コード取得
   *
   * @access protected
   * @return mixed コード
   */
  protected function get_original_code()
  {
    return $this->original_code;
  }

  /**
   * 深刻度（エラーレベル）設定
   *
   * @access protected
   * @param int $severity 深刻度（エラーレベル）
   */
  protected function set_severity($severity)
  {
    $this->severity = $severity;
  }

  /**
   * 深刻度（エラーレベル）取得
   *
   * @access protected
   * @return int 深刻度（エラーレベル）
   */
  protected function get_severity()
  {
    return $this->severity;
  }

  /**
   * ファイル名設定
   *
   * @access protected
   * @param string $file_name ファイル名
   */
  protected function set_file_name($file_name)
  {
    $this->file_name = $file_name;
  }

  /**
   * ファイル名取得
   *
   * @access protected
   * @return string ファイル名
   */
  protected function get_file_name()
  {
    return $this->file_name;
  }

  /**
   * 行数設定
   *
   * @access protected
   * @param int $line_no 行数
   */
  protected function set_line_no($line_no)
  {
    $this->line_no = $line_no;
  }

  /**
   * 行数取得
   *
   * @access protected
   * @return int 行数
   */
  protected function get_line_no()
  {
    return $this->line_no;
  }

  /**
   * 前に発生した例外設定
   *
   * @access protected
   * @param Exception $previous 前に発生した例外
   */
  protected function set_previous($previous)
  {
    $this->previous = $previous;
  }

  /**
   * 前に発生した例外取得
   *
   * @access protected
   * @return Exception 前に発生した例外
   */
  protected function get_previous()
  {
    return $this->previous;
  }

  /**
   * ログ書き込みを行う
   *
   * @access public
   */
  public function write_log()
  {
    // リファラーがセットされていれば取得
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $date_time = new DateTime();

    // ログに書き込む文字列を生成
    // 優先度中：getTraceAsString()は文字切れして表示されてしまう？要調査＆改善
    $severity = $this->get_severity();
    $error_string = $date_time->format('Y-m-d H:i:s') . ' ' . (isset(self::$error_levels[$severity]) ? self::$error_levels[$severity] : $severity) . ' '
      . $this->get_original_code() . ' ' . $this->get_message() . ' ' . $this->get_file_name() . '(' . $this->get_line_no() . ')' . PHP_EOL
      . 'Referer = ' . $referer . PHP_EOL . $this->getTraceAsString() . PHP_EOL;

    // ログに書き込む
    file_put_contents(config::get_instance()->get_base_path() . '/log/' . $date_time->format('Ymd') . '.log', $error_string, FILE_APPEND);
  }

  private static $error_levels = array(
    E_ERROR => 'E_ERROR',
    E_WARNING => 'E_WARNING',
    E_PARSE => 'E_PARSE',
    E_NOTICE => 'E_NOTICE',
    E_CORE_ERROR => 'E_CORE_ERROR',
    E_CORE_WARNING => 'E_CORE_WARNING',
    E_COMPILE_ERROR => 'E_COMPILE_ERROR',
    E_COMPILE_WARNING => 'E_COMPILE_WARNING',
    E_USER_ERROR => 'E_USER_ERROR',
    E_USER_WARNING => 'E_USER_WARNING',
    E_USER_NOTICE => 'E_USER_NOTICE',
    E_STRICT => 'E_STRICT',
    E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
  );

  /**
   * メッセージ
   *
   * @access protected
   */
  protected $message;

  /**
   * コード
   *
   * @access protected
   */
  protected $original_code;

  /**
   * ファイル名
   *
   * @access protected
   */
  protected $file_name;

  /**
   * 行数
   *
   * @access protected
   */
  protected $line_no;

  /**
   * 例外の深刻度（エラーレベル）
   *
   * @access private
   */
  private $severity;

  /**
   * 前に発生した例外
   *
   * @access private
   */
  private $previous;
}
