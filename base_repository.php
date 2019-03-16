<?php

/**
 * データアクセスの基底リポジトリインタフェース
 *
 * 共通で定義する必要のあるメソッドを定義
 *
 * @access  public
 * @create  2019/03/16
 * @version 0.1
 */
interface base_repository
{
  public function get_primary_keys();
  public function begin();
  public function commit();
  public function rollback();
}
