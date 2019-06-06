<?php

/**
 * PostgreSQL用のDBハンドラークラス
 *
 * @access  public
 * @create  2019/05/25
 * @version 0.1
 */
class postgresql_storage_handler extends rdbms_storage_handler
{
  /**
   * SQL識別子のエスケープ用の文字
   *
   * @access public
   */
  const SQL_ESCAPE_CHARACTER = '"';

  /**
   * クエリ結果リソースを設定する
   *
   * @access protected
   * @param resource クエリ結果リソース
   */
  protected function set_query_result($query_result)
  {
    $this->query_result = $query_result;
  }

  /**
   * クエリ結果リソースを取得する
   *
   * @access protected
   * @return resource クエリ結果リソース
   */
  protected function get_query_result()
  {
    return $this->query_result;
  }

  /**
   * DBに接続する
   *
   * @return bool DBへの接続結果（成功ならtrue、失敗ならfalse）
   */
  public function connect()
  {
    // DB接続処理
    if (null === $this->get_connection())
    {
      $config = config::get_instance();
      // DB接続文字列
      $conn_str = 'host=' . $this->get_host_name() . ' port=' . $this->get_port_number() . ' dbname=' . $this->get_database_name() . ' user=' . $this->get_user_name() .
      ' password=' . $this->get_password();
      $connection = pg_connect($conn_str);
      if (false === $connection)
      {
        // DB接続失敗
        $this->set_error_message($config->search('db_connect_error'));

        return false;
      }

      $this->set_connection($connection);

      // 文字コード設定
      pg_set_client_encoding($this->get_connection(), $config->search('db_character_set'));
    }

    return true;
  }

  /**
   * データを取得する（関連テーブルのデータは取得しない）
   *
   * @param int $mode 取得するデータの対象（1件なのか全件なのか等）
   * @return array|entity エンティティクラスインスタンス配列かエンティティクラスインスタンス
   */
  public function fetch($mode)
  {
    $result = null;

    $entity_class_name = $this->get_entity_class_name();
    $this->read_entity_class($entity_class_name);
    $table_meta_data = $this->get_table_meta_data($this->get_table_name());

    if (parent::FETCH_ONE === $mode)
    {
      $entity = null;

      if (false !== ($data = pg_fetch_assoc($this->get_query_result())))
      {
        $entity = new $entity_class_name;
        foreach ($data as $column => $value)
        {
          if (true === $entity->is_property_exists($column))
          {
            $value = $this->get_cast_value($table_meta_data, $column, $value);
            $entity->set_entity_data($column, $value);
          }
        }
      }

      $result = $entity;
    }
    else if (parent::FETCH_ALL === $mode)
    {
      $entities = array();

      while (false !== ($data = pg_fetch_assoc($this->get_query_result())))
      {
        $entity = new $entity_class_name;
        foreach ($data as $column => $value)
        {
          if (true === $entity->is_property_exists($column))
          {
            $value = $this->get_cast_value($table_meta_data, $column, $value);
            $entity->set_entity_data($column, $value);
          }
        }
        $entities[] = $entity;
      }

      $result = $entities;
    }
    else
    {
      throw new custom_exception('フェッチのモードが不明です', __CLASS__ . ':' . __FUNCTION__);
    }

    return $result;
  }

  /**
   * データを取得する（関連テーブルのデータも取得する）
   *
   * @param bool $unique_primary_key_data 主キーが同じ行でも別の配列の要素にするか否か（デフォルトは同じ配列内の要素にまとめる）
   * @return array エンティティクラスインスタンス配列
   */
  public function fetch_all_associated_entity($unique_primary_key_data)
  {
    $entity_class_name = $this->get_entity_class_name();
    $this->read_entity_class($entity_class_name);

    $associated_entities = $this->get_associated_entities();
    $associated_tables = $this->get_associated_tables();
    $main_table_meta_data = $this->get_table_meta_data($this->get_table_name());
    while (false !== ($data = pg_fetch_assoc($this->get_query_result())))
    {
      $this->set_all_entities(
        $unique_primary_key_data,
        $data,
        $main_table_meta_data,
        $entity_class_name,
        $associated_entities,
        $associated_tables
      );
    }

    return $this->get_entities();
  }

  /**
   * 最後に追加したレコードの一意なIDを取得する
   *
   * @return int 最後に追加したレコードの一意なID
   */
  public function get_last_insert_id()
  {
    $last_insert_id = null;

    // TODO: INSERT・・・RETURNINGという書き方もあるが、未検証
    $result = pg_query($this->get_connection(), 'SELECT LASTVAL()');

    if (false !== $result)
    {
      if (false !== ($row = pg_fetch_row($result)))
      {
        $last_insert_id = $row[0];
      }
    }

    return $last_insert_id;
  }

  /**
   * トランザクションを開始する
   *
   * @return bool トランザクション開始結果（成功ならtrue、失敗ならfalse）
   */
  public function begin()
  {
    return pg_query($this->get_connection(), 'begin');
  }

  /**
   * トランザクションをコミットする
   *
   * @return bool トランザクションコミット結果（成功ならtrue、失敗ならfalse）
   */
  public function commit()
  {
    return pg_query($this->get_connection(), 'commit');
  }

  /**
   * トランザクションをロールバックする
   *
   * @return bool トランザクションロールバック結果（成功ならtrue、失敗ならfalse）
   */
  public function rollback()
  {
    return pg_query($this->get_connection(), 'rollback');
  }

  /**
   * SQLを実行する
   *
   * @param string $sql SQL文
   * @return bool SQL実行結果（成功ならtrue、失敗ならfalse）
   */
  protected function execute_sql($sql)
  {
    $connection = $this->get_connection();
    $result = pg_prepare($connection, '', $this->replace_place_holder($sql));

    if (false === $result)
    {
      throw new custom_exception('SQL文のプリペアに失敗しました', __CLASS__ . ':' . __FUNCTION__);
    }

    $result = pg_execute($connection, '', $this->get_bind_params());

    if (false === $result)
    {
      return $result;
    }
    else
    {
      $this->set_query_result($result);

      return true;
    }
  }

  /**
   * 指定されたテーブルのメタデータを取得する
   *
   * @param string $table_name テーブル名
   * @return array テーブルのメタデータ配列
   */
  protected function get_table_meta_data($table_name)
  {
    return pg_meta_data($this->get_connection(), $table_name);
  }

  /**
   * エンティティクラスのフィールドに対して、適切な型のデータを設定する為にキャストする
   * PostgreSQLは、これをやらないと全部stringになるので
   *
   * @param array $table_meta_data テーブルのメタデータ
   * @param string $column_name 項目名
   * @param mixed $column_value 値
   * @return mixed キャストされた値
   */
  protected function get_cast_value(array $table_meta_data, $column_name, $column_value)
  {
    $result = null;

    $data_type_name = '';

    if (true === isset($table_meta_data[$column_name]))
    {
      $data_type_name = $table_meta_data[$column_name]['type'];
    }

    if (true === isset(self::$DATA_TYPE_MAPPINGS[$data_type_name]))
    {
      $function_name = self::$DATA_TYPE_MAPPINGS[$data_type_name] . 'val';
      if (true === function_exists($function_name))
      {
        // TODO: boolは、't'や'f'の値になっている？
        $result = $function_name($column_value);
      }
      else
      {
        throw new custom_exception('存在しない型変換の関数です', __CLASS__ . ':' . __FUNCTION__);
      }
    }

    if (null === $result)
    {
      throw new custom_exception('値をキャスト出来ませんでした', __CLASS__ . ':' . __FUNCTION__);
    }

    return $result;
  }

  /**
   * 変更された行数を取得する
   *
   * @return int 変更された行数
   */
  protected function get_affected_rows()
  {
    return pg_affected_rows($this->get_query_result());
  }

  /**
   * SQLパラメータの「?」を、「$1」などの数値に置換する(PostgreSQL専用)
   *
   * @param string $sql SQL文
   * @return string 置換されたSQL文
   */
  protected function replace_place_holder($sql)
  {
    $tmp_arr = explode('?', $sql);
    $sql = $tmp_arr[0];
    $tmp_arr_len = count($tmp_arr);
    for ($i = 1; $i < $tmp_arr_len; $i++)
    {
      $sql .= '$' . $i . $tmp_arr[$i];
    }

    return $sql;
  }

  /**
   * PostgreSQLとPHP間のデータ型のマッピング配列（実質、定数）
   * 他にも、まだデータ型あるし、マッピングも結構適当です
   *
   * @access private
   */
  private static $DATA_TYPE_MAPPINGS = array(
    // bigint、bigserial、
    'int8' => 'float',
    // boolean
    'bool' => 'bool',
    // character varing(varchar)
    'varchar' => 'str',
    // character(char)
    'bpchar' => 'str',
    // date
    'date' => 'str',
    // double_precision
    'float8' => 'float',
    // integer、serial
    'int4' => 'int',
    // numeric
    'numeric' => 'float',
    // real
    'float4' => 'float',
    // smallint
    'int2' => 'int',
    // text
    'text' => 'str',
    // time without time zone
    'time' => 'str',
    // time with time zone
    'timetz' => 'str',
    // timestamp without time zone
    'timestamp' => 'str',
    // timestamp with time zone
    'timestamptz' => 'str',
  );

  /**
   * クエリ結果リソース
   *
   * @access private
   */
  private $query_result;
}
