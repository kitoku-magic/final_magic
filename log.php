<?php

/**
 * Logクラス
 *
 * シングルトンなクラス
 *
 * @access  public
 * @create  2019/03/31
 * @version 0.1
 */
class log
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
   * シングルトンなクラスインスタンスを取得
   *
   * @access public
   * @return this 自クラスのインスタンス
   */
  public static function get_instance()
  {
    if (null === self::$log)
    {
      // 初回アクセス時のみインスタンスを生成し以降はずっと保持
      self::$log = new log();
    }

    return self::$log;
  }

  /**
   * インスタンスのコピー
   */
  public function __clone()
  {
    // シングルトンの為、インスタンスのコピーは禁止
    throw new custom_exception(get_class($this) . 'はシングルトンの為、インスタンスのコピーは出来ません', __CLASS__ . ':' . __FUNCTION__);
  }

  /**
   * ログ書き込みを行う
   *
   * @access public
   * @param string $value ログに書き込む内容
   */
  public function write($value)
  {
    // ログに書き込む
    file_put_contents(config::get_instance()->get_base_path() . '/log/' . utility::get_date_time_with_timezone(utility::get_current_time_stamp())->format('Ymd') . '.log', $value . PHP_EOL, FILE_APPEND);
  }

  /**
   * シングルトンな自クラスのインスタンス
   *
   * @access private
   */
  private static $log;
}
