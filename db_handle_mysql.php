<?php

require_once('db_handle.php');

/**
 * DBハンドルクラス MySQL
 *
 * MySQL用のDBハンドルクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class db_handle_mysql extends db_handle
{

  /**
   * DB接続オブジェクト設定
   *
   * @access public
   * @param mysqli $conn DB接続オブジェクト
   */
  public function set_conn($conn)
  {
    $this->conn = $conn;
  }

  /**
   * DB接続オブジェクト取得
   *
   * @access public
   * @return mysqli DB接続オブジェクト
   */
  public function get_conn()
  {
    return $this->conn;
  }

  /**
   * ステートメントオブジェクト設定
   *
   * @access public
   * @param mysqli_stmt $stmt ステートメントオブジェクト
   */
  public function set_stmt($stmt)
  {
    $this->stmt = $stmt;
  }

  /**
   * ステートメントオブジェクト取得
   *
   * @access public
   * @return mysqli_stmt ステートメントオブジェクト
   */
  public function get_stmt()
  {
    return $this->stmt;
  }

  /**
   * 結果セット配列設定
   *
   * @access public
   * @param array $res_array 結果セット配列
   */
  public function set_res_array($res_array)
  {
    $this->res_array = $res_array;
  }

  /**
   * 結果セット配列取得
   *
   * @access public
   * @return array 結果セット配列
   */
  public function get_res_array()
  {
    return $this->res_array;
  }

  /**
   * DB接続処理
   *
   * @access public
   * @return boolean DB接続に成功したらtrueを返す
   */
  public function connect()
  {
    // nullならDB接続
    if (true === is_null($this->get_conn()))
    {
      // DB接続
      $conn = new mysqli(
        $this->get_host_name(),
        $this->get_user_name(),
        $this->get_password(),
        $this->get_database_name(),
        $this->get_port_number(),
        $this->get_config()->search('db_socket_file_path')
      );
      if ($conn_error = mysqli_connect_error())
      {
        // DB接続失敗
        $this->set_error_message($this->get_config()->search('db_connect_error') . $conn_error);
        return false;
      }

      $this->set_conn($conn);

      // 文字コード設定
      $this->get_conn()->set_charset($this->get_config()->search('db_character_set'));
    }

    return true;
  }

  /**
   * DB切断処理
   *
   * @access public
   */
  public function disconnect()
  {
    // null以外ならDB切断
    if (false === is_null($this->get_conn()))
    {
      $this->get_conn()->close();
    }
  }

  /**
   * プリペアドステートメント設定
   *
   * @access public
   * @param string $sql 実行するSQL文
   * @return boolean 設定に成功したらtrue、失敗したらfalse
   */
  public function set_prepare_query($sql)
  {
    $stmt = $this->get_conn()->prepare($sql);

    if (false === $stmt)
    {
      return false;
    }
    else
    {
      $this->set_stmt($stmt);
      return true;
    }
  }

  /**
   * プリペアドステートメントのパラメータに変数をバインド
   *
   * @access public
   * @param string $variable_type パラメータ変数のデータ型
   * @param array $param_array パラメータのキーと値が格納されている配列
   * @return boolean バインドに成功したらtrue、失敗したらfalse
   */
  public function set_bind_param($variable_type, $param_array)
  {
    $bind_array = array();
    // パラメータのデータ型
    $bind_array[] = $variable_type;

    foreach ($param_array as $key => $val)
    {
      // パラメータの値
      $bind_array[] = &$param_array[$key];
    }

    $ret = call_user_func_array(array($this->get_stmt(), 'bind_param'), $bind_array);

    if (false === $ret)
    {
      return false;
    }

    return true;
  }

  /**
   * プリペアドステートメント実行
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function execute_query()
  {
    $ret = $this->get_stmt()->execute();

    if (false === $ret)
    {
      return false;
    }

    return true;
  }

  /**
   * 結果セットを保存
   *
   * @access public
   * @return boolean 成功した場合にtrueを返す
   */
  public function set_store_result()
  {
    return $this->get_stmt()->store_result();
  }

  /**
   * バインド変数に結果を保存(優先度中：どうも処理が回りくどい)
   *
   * @access public
   * @param array $res_array SQL結果を保存する配列
   * @return boolean 成功した場合にtrueを返す
   */
  public function set_bind_result($res_array)
  {
    $temp_array = array();

    foreach ($res_array as $key => $val)
    {
      $temp_array[] = &$res_array[$key];
    }

    $this->set_res_array($temp_array);
    return call_user_func_array(array($this->get_stmt(), 'bind_result'), $this->get_res_array());
  }

  /**
   * 連想配列形式のバインド変数に結果を保存
   *
   * @access public
   * @return boolean 成功した場合にtrueを返す
   */
  public function set_bind_result_assoc()
  {
    $tmp = array();

    $meta = $this->get_stmt()->result_metadata();
    while ($column = $meta->fetch_field())
    {
      $tmp[] = &$this->res_array[$column->name];
    }

    return call_user_func_array(array($this->get_stmt(), 'bind_result'), $tmp);
  }

  /**
   * データ件数を取得
   *
   * @access public
   * @return int データ件数
   */
  public function get_num_rows()
  {
    return $this->get_stmt()->num_rows;
  }

  /**
   * 結果セットから1行取得(数値添字配列形式)
   *
   * @access public
   * @return boolean データが有る場合にはtrue、無い場合にはfalseを返す
   */
  public function fetch_row()
  {
    return $this->get_stmt()->fetch();
  }

  /**
   * 結果セットから1行取得(連想配列形式)
   *
   * @access public
   * @return array 連想配列形式の1行の配列、データが無い場合にはfalseを返す
   */
  public function fetch_assoc()
  {
    if (null === $this->get_stmt()->fetch())
    {
      return false;
    }
    else
    {
      // バインド変数に設定されたデータを取得し再設定(参照を外す為に新たな変数に再設定している)
      foreach ($this->get_res_array() as $key => $val)
      {
        $result[$key] = $val;
      }
      return $result;
    }
  }

  /**
   * プリペアドステートメントをクローズ
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function stmt_close()
  {
    $ret = $this->get_stmt()->close();

    if (false === $ret)
    {
      return false;
    }

    return true;
  }

  /**
   * 自動コミットの設定
   *
   * @access public
   * @param boolean $auto_commit_flg 自動コミットにするかどうかを表すフラグ
   * @return boolean 設定に成功したらtrue、失敗したらfalse
   */
  public function set_auto_commit($auto_commit_flg)
  {
    // 自動コミットの設定
    return $this->get_conn()->autocommit($auto_commit_flg);
  }

  /**
   * トランザクションの開始
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function begin_transaction()
  {
    // TODO: mysqliのbegin_transactionメソッドは、PHPとMySQLのバージョンの要求が高いので一旦保留
    // 自動コミットの無効化
    return $this->get_conn()->autocommit(false);
  }

  /**
   * 変更された行数を取得
   *
   * @access public
   * @return int 変更された行数
   */
  public function get_affected_rows()
  {
    return $this->get_stmt()->affected_rows;
  }

  /**
   * コミットする
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function commit()
  {
    return $this->get_conn()->commit();
  }

  /**
   * ロールバックする
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function rollback()
  {
    return $this->get_conn()->rollback();
  }

  /**
   * DB接続オブジェクト
   *
   * @access private
   */
  private $conn = null;

  /**
   * ステートメントオブジェクト
   *
   * @access private
   */
  private $stmt = null;

  /**
   * 結果セット配列
   *
   * @access private
   */
  private $res_array = null;
}
