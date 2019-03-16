<?php

/**
 * ストレージハンドラーの基底インタフェース
 *
 * ストレージに対する共通操作を定義
 *
 * @access  public
 * @create  2019/03/16
 * @version 0.1
 */
interface storage_handler
{
  /**
   * 該当のストレージに接続する
   */
  public function connect();

  /**
   * 該当のストレージからデータを取得する
   */
  public function get();

  /**
   * 該当のストレージにデータを保存する
   *
   * @param string $mode データを保存する種別（INSERT・UPDATE・DELETE等）
   */
  public function set($mode);
}
