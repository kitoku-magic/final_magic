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
   * DBハンドルの取得を行う
   *
   * @access public
   * @param config $config 設定ファイルクラスインスタンス
   * @return db_handle DBハンドルクラスインスタンス
   */
  static public function get_handle($config)
  {
    // DBハンドルクラスインスタンス
    $dbh = null;

    // DBMSの検索
    $dbms = $config->search('db_type');

    if (0 === strcmp('mysql', $dbms))
    {
      // MySQL
      require_once('db_handle_mysql.php');
      $dbh = new db_handle_mysql();
    }
    else if (0 === strcmp('postgresql', $dbms))
    {
      // PostgreSQL
      require_once('db_handle_postgresql.php');
      $dbh = new db_handle_postgresql();
    }
    else
    {
      throw new custom_exception('DBが見つかりませんでした', 1);
    }

    // 設定
    $dbh->set_config($config);
    $dbh->set_user_name($config->search('db_user'));
    $dbh->set_password($config->search('db_password'));
    $dbh->set_database_name($config->search('db_name'));
    $dbh->set_host_name($config->search('db_host_name'));
    $dbh->set_port_number($config->search('db_port_number'));

    // DB接続
    if (false === $dbh->connect())
    {
      // 接続失敗
      throw new custom_exception($dbh->get_error_message(), __CLASS__ . ':' . __FUNCTION__);
    }

    // パスワード情報の消去
    $dbh->set_password('?');

    return $dbh;
  }
}
