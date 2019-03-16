<?php

/**
 * DBハンドル管理クラス
 *
 * 使用するDBによってDBハンドルを切り替える
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class db_manager
{
  /**
   * 対象のDB内のハンドラーを全て取得する
   *
   * @access public
   * @param config $config 設定ファイルクラスインスタンス
   * @return array DBのストレージハンドラークラスインスタンス配列
   */
  public static function get_storage_handlers(config $config)
  {
    $db_handlers = array();
    // マスターへのDBハンドルを取得
    $db_handlers['master'] = self::get_storage_handle($config, $config->search('db_host_master'));
    // スレーブへのDBハンドルを取得（複数あるスレーブサーバーの内、一つをランダムで選択）
    $slave_number = mt_rand(1, $config->search('db_host_slave_count'));
    $db_host_slave = $config->search('db_host_slave' . $slave_number);
    if (null !== $db_host_slave)
    {
      $db_handlers['slave'] = self::get_storage_handle($config, $db_host_slave);
    }
    else
    {
      throw new custom_exception('DBスレーブが見つかりませんでした', __CLASS__ . ':' . __FUNCTION__);
    }

    return $db_handlers;
  }

  /**
   * DB内のハンドラーを一つ取得する
   *
   * @param config $config 設定ファイルクラスインスタンス
   * @param string DBホスト名
   * @return storage_handler DBストレージハンドラークラスインスタンス
   */
  private static function get_storage_handle(config $config, $db_host_name)
  {
    // DBハンドルクラスインスタンス
    $dbh = null;

    // DBMSの検索
    $db_handle_class_name = $config->search('db_type') . '_storage_handler';
    if (true === class_exists($db_handle_class_name))
    {
      $dbh = new $db_handle_class_name();
    }
    else
    {
      throw new custom_exception('DBハンドラークラスが見つかりませんでした', __CLASS__ . ':' . __FUNCTION__);
    }

    // DB接続情報を設定
    $dbh->set_config($config);
    $dbh->set_user_name($config->search('db_user'));
    $dbh->set_password($config->search('db_password'));
    $dbh->set_database_name($config->search('db_name'));
    $dbh->set_host_name($db_host_name);
    $dbh->set_port_number($config->search('db_port_number'));

    // DB接続
    if (false === $dbh->connect())
    {
      // 接続失敗
      throw new custom_exception($dbh->get_error_message(), __CLASS__ . ':' . __FUNCTION__);
    }

    // パスワード情報の消去
    $dbh->set_password('????????????????????');

    return $dbh;
  }
}
