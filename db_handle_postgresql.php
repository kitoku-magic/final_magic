<?php

require_once('db_handle.php');

/**
 * DBハンドルクラス PostgreSQL
 *
 * PostgreSQL用のDBハンドルクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class db_handle_postgresql extends db_handle
{

  /**
   * DB接続オブジェクト設定
   *
   * @access public
   * @param resource $conn DB接続オブジェクト
   */
  public function set_conn($conn)
  {
    $this->conn = $conn;
  }

  /**
   * DB接続オブジェクト取得
   *
   * @access public
   * @return resource DB接続オブジェクト
   */
  public function get_conn()
  {
    return $this->conn;
  }

  /**
   * ステートメントオブジェクト設定
   *
   * @access public
   * @param resource $stmt ステートメントオブジェクト
   */
  public function set_stmt($stmt)
  {
    $this->stmt = $stmt;
  }

  /**
   * ステートメントオブジェクト取得
   *
   * @access public
   * @return resource ステートメントオブジェクト
   */
  public function get_stmt()
  {
    return $this->stmt;
  }

  /**
   * 結果セットオブジェクト設定
   *
   * @access public
   * @param resource $res 結果セットオブジェクト
   */
  public function set_res($res)
  {
    $this->res = $res;
  }

  /**
   * 結果セットオブジェクト取得
   *
   * @access public
   * @return resource 結果セットオブジェクト
   */
  public function get_res()
  {
    return $this->res;
  }

  /**
   * パラメータにバインドする配列設定
   *
   * @access public
   * @param array $param_array パラメータにバインドする配列
   */
  public function set_param_array($param_array)
  {
    $this->param_array = $param_array;
  }

  /**
   * パラメータにバインドする配列取得
   *
   * @access public
   * @return resource パラメータにバインドする配列
   */
  public function get_param_array()
  {
    return $this->param_array;
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
    if (is_null($this->get_conn()))
    {
      // DB接続文字列
      $conn_str = 'host=' . $this->get_host_name() . ' port=' . $this->get_port_number() . ' dbname=' . $this->get_database_name() . ' user=' . $this->get_user_name() .
      ' password=' . $this->get_password();
      $conn = pg_connect($conn_str);
      if (false === $conn)
      {
        // DB接続失敗
        $this->set_error_message($this->get_config()->search('db_connect_error'));
        return false;
      }

      $this->set_conn($conn);

      // 文字コード設定
      pg_set_client_encoding($this->get_conn(), $this->get_config()->search('db_character_set'));
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
      pg_close($this->get_conn());
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
    $result = pg_prepare($this->get_conn(), '', utility::sql_param_question_to_dollar_num($sql));

    if (false === $result)
    {
      return false;
    }

    return true;
  }

  /**
   * プリペアドステートメントのパラメータに変数をバインド
   *
   * @access public
   * @param string $variable_type パラメータ変数のデータ型
   * @param array $param_array パラメータのキーと値が格納されている配列
   */
  public function set_bind_param($variable_type, $param_array)
  {
    // Postgresでは$variable_typeは使わない
    $this->set_param_array($param_array);
  }

  /**
   * プリペアドステートメント実行
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function execute_query()
  {
    $res = pg_execute($this->get_conn(), '', $this->get_param_array());
    if (false === $res)
    {
      return false;
    }
    else
    {
      $this->set_res($res);
      return true;
    }
  }

  /**
   * 結果セットを保存(Postgresでは何もしない)
   *
   * @access public
   */
  public function set_store_result()
  {
  }

  /**
   * バインド変数に結果を保存
   *
   * @access public
   * @param array $res_array SQL結果を保存する配列
   */
  public function set_bind_result($res_array)
  {
    $this->set_res_array($res_array);
  }

  /**
   * 連想配列形式のバインド変数に結果を保存(Postgresでは何もしない)
   *
   * @access public
   */
  public function set_bind_result_assoc()
  {
  }

  /**
   * データ件数を取得
   *
   * @access public
   * @return int データ件数
   */
  public function get_num_rows()
  {
    return pg_num_rows($this->get_res());
  }

  /**
   * 結果セットから1行取得(数値添字配列形式)
   *
   * @access public
   * @return array 数値添字配列形式の1行の配列
   */
  public function fetch_row()
  {
    $this->set_res_array(pg_fetch_row($this->get_res()));
    return $this->get_res_array();
  }

  /**
   * 結果セットから1行取得(連想配列形式)
   *
   * @access public
   * @return array 連想配列形式の1行の配列
   */
  public function fetch_assoc()
  {
    $this->set_res_array(pg_fetch_assoc($this->get_res()));
    return $this->get_res_array();
  }

  /**
   * プリペアドステートメントをクローズ(Postgresでは何もしない)
   *
   * @access public
   */
  public function stmt_close()
  {
  }

  /**
   * トランザクションの開始
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function begin_transaction()
  {
    $ret = pg_query('begin');

    if (false === $ret)
    {
      return false;
    }

    return true;
  }

  /**
   * 変更された行数を取得
   *
   * @access public
   * @return int 変更された行数
   */
  public function get_affected_rows()
  {
    return pg_affected_rows($this->get_res());
  }

  /**
   * コミットする
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function commit()
  {
    $ret = pg_query('commit');

    if (false === $ret)
    {
      return false;
    }

    return true;
  }

  /**
   * ロールバックする
   *
   * @access public
   * @return boolean 成功したらtrue、失敗したらfalse
   */
  public function rollback()
  {
    $ret = pg_query('rollback');

    if (false === $ret)
    {
      return false;
    }

    return true;
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
   * 結果セットオブジェクト
   *
   * @access private
   */
  private $res = null;

  /**
   * パラメータにバインドする配列
   *
   * @access private
   */
  private $param_array = array();

  /**
   * 結果セット配列
   *
   * @access private
   */
  private $res_array = null;
}
