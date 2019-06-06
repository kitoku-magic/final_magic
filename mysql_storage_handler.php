<?php

/**
 * MySQL用のDBハンドラークラス
 *
 * @access  public
 * @create  2019/03/16
 * @version 0.1
 */
class mysql_storage_handler extends rdbms_storage_handler
{
  /**
   * SQL識別子のエスケープ用の文字
   *
   * @access public
   */
  const SQL_ESCAPE_CHARACTER = '`';

  /**
   * ステートメントクラスインスタンスを設定する
   *
   * @access protected
   * @param mysqli_stmt ステートメントクラスインスタンス
   */
  protected function set_statement(mysqli_stmt $statement)
  {
    $this->statement = $statement;
  }

  /**
   * ステートメントクラスインスタンスを取得する
   *
   * @access protected
   * @return mysqli_stmt ステートメントクラスインスタンス
   */
  protected function get_statement()
  {
    return $this->statement;
  }

  /**
   * DBに接続する
   *
   * @return bool DBへの接続結果（成功ならtrue、失敗ならfalse）
   */
  public function connect()
  {
    if (null === $this->get_connection())
    {
      $config = config::get_instance();
      // DB接続
      $connection = new mysqli(
        $this->get_host_name(),
        $this->get_user_name(),
        $this->get_password(),
        $this->get_database_name(),
        $this->get_port_number(),
        $config->search('db_socket_file_path')
      );
      if (null !== $connection->connect_error)
      {
        // DB接続失敗
        $this->set_error_message($config->search('db_connect_error') . $connection->connect_error);

        return false;
      }

      $this->set_connection($connection);

      // 文字コード設定
      $this->get_connection()->set_charset($config->search('db_character_set'));
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

    $result_set = $this->get_result_set();

    if (0 < count($result_set))
    {
      $statement = $this->get_statement();
      $entity_class_name = $this->get_entity_class_name();
    }

    if (parent::FETCH_ONE === $mode)
    {
      $entity = null;

      if (true === isset($statement))
      {
        if ($statement->fetch())
        {
          $this->read_entity_class($entity_class_name);
          $entity = new $entity_class_name;
          foreach ($result_set['data'] as $column_name => $column_value)
          {
            if (true === $entity->is_property_exists($column_name))
            {
              $entity->set_entity_data($column_name, $column_value);
            }
          }
        }
      }

      $result = $entity;
    }
    else if (parent::FETCH_ALL === $mode)
    {
      $entities = array();

      if (true === isset($statement))
      {
        while ($statement->fetch())
        {
          $this->read_entity_class($entity_class_name);
          $entity = new $entity_class_name;
          foreach ($result_set['data'] as $column_name => $column_value)
          {
            if (true === $entity->is_property_exists($column_name))
            {
              $entity->set_entity_data($column_name, $column_value);
            }
          }
          $entities[] = $entity;
        }
      }

      $result = $entities;
    }
    else
    {
      throw new custom_exception('フェッチのモードが不明です', __CLASS__ . ':' . __FUNCTION__);
    }

    if (true === isset($statement))
    {
      $statement->close();
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
    $entities = array();

    $result_set = $this->get_result_set();

    if (0 < count($result_set))
    {
      $statement = $this->get_statement();
      $entity_class_name = $this->get_entity_class_name();
    }

    if (true === isset($statement))
    {
      $associated_entities = $this->get_associated_entities();
      $associated_tables = $this->get_associated_tables();
      $main_table_meta_data = $this->get_table_meta_data($this->get_table_name());
      $is_column_unique = $this->get_is_column_unique();
      if (true === $is_column_unique)
      {
        $main_table_name = $this->get_table_name() . '.';
      }
      else
      {
        $main_table_name = '';
      }
      while ($statement->fetch())
      {
        $this->set_all_entities(
          $unique_primary_key_data,
          $result_set['data'],
          $main_table_meta_data,
          $entity_class_name,
          $associated_entities,
          $associated_tables,
          $main_table_name
        );
      }
      $entities = $this->get_entities();
      // 再度呼ばれた際に備えて、データをクリアしておく
      $this->set_entities(array());
    }

    return $entities;
  }

  /**
   * 最後に追加したレコードの一意なIDを取得する
   *
   * @return int 最後に追加したレコードの一意なID
   */
  public function get_last_insert_id()
  {
    $result = null;

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $result = $statement->insert_id;
    }

    return $result;
  }

  /**
   * トランザクションを開始する
   *
   * @return bool トランザクション開始結果（成功ならtrue、失敗ならfalse）
   */
  public function begin()
  {
    return $this->get_connection()->autocommit(false);
  }

  /**
   * トランザクションをコミットする
   *
   * @return bool トランザクションコミット結果（成功ならtrue、失敗ならfalse）
   */
  public function commit()
  {
    return $this->get_connection()->commit();
  }

  /**
   * トランザクションをロールバックする
   *
   * @return bool トランザクションロールバック結果（成功ならtrue、失敗ならfalse）
   */
  public function rollback()
  {
    return $this->get_connection()->rollback();
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
    $statement = $connection->prepare($sql);

    if (false === $statement)
    {
      throw new custom_exception('SQL文のプリペアに失敗しました', __CLASS__ . ':' . __FUNCTION__);
    }
    $this->set_statement($statement);

    // パラメータのデータ型
    $bind_array = $this->get_converted_bind_types();

    $bind_params = $this->get_bind_params();
    if (null !== $bind_params)
    {
      foreach ($bind_params as $key => $val)
      {
        // パラメータの値
        $bind_array[] = &$bind_params[$key];
      }
    }

    $statement = $this->get_statement();
    if (0 < count($bind_array))
    {
      $result = call_user_func_array(array($statement, 'bind_param'), $bind_array);

      if (false === $result)
      {
        throw new custom_exception('SQL文のパラメータのバインドに失敗しました', __CLASS__ . ':' . __FUNCTION__);
      }
    }

    $result = $statement->execute();

    return $result;
  }

  /**
   * 指定されたテーブルのメタデータを取得する
   *
   * @param string $table_name テーブル名
   * @return array テーブルのメタデータ配列
   */
  protected function get_table_meta_data($table_name)
  {
    // MySQLの場合、結果セットに関するメタデータを取得している
    $result = array();

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $mysqli_result = $statement->result_metadata();
      if (false !== $mysqli_result)
      {
        $result = $mysqli_result->fetch_fields();
      }
    }

    return $result;
  }

  /**
   * エンティティクラスのフィールドに対して、適切な型のデータを設定する為にキャストする
   *
   * @param array $table_meta_data テーブルのメタデータ
   * @param string $column_name 項目名
   * @param mixed $column_value 値
   * @return mixed キャストされた値
   */
  protected function get_cast_value(array $table_meta_data, $column_name, $column_value)
  {
    // MySQLは不要なので何もやらない
    return $column_value;
  }

  /**
   * 変更された行数を取得する
   *
   * @return int 変更された行数
   */
  protected function get_affected_rows()
  {
    $result = null;

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $result = $statement->affected_rows;
    }

    return $result;
  }

  /**
   * バインドするデータ型の変換処理
   *
   * @return array バインドするデータ型配列
   */
  protected function get_converted_bind_types()
  {
    $result = array();

    $bind_types = $this->get_bind_types();
    if (null !== $bind_types)
    {
      foreach ($bind_types as $bind_type)
      {
        $result[] = self::$BIND_TYPE_MAPPINGS[$bind_type];
      }
    }

    if (0 < count($result))
    {
      $result = array(implode('', $result));
    }

    return $result;
  }

  /**
   * 結果セットを取得する
   *
   * @return array 結果セット配列
   */
  protected function get_result_set()
  {
    $result = array();

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $res = $statement->store_result();
      if (false === $res)
      {
        throw new custom_exception('結果セットのバッファへの格納に失敗しました。', __CLASS__ . ':' . __FUNCTION__);
      }

      if (false === ($meta = $statement->result_metadata()))
      {
        throw new custom_exception('結果セットのメタデータの取得に失敗しました。', __CLASS__ . ':' . __FUNCTION__);
      }

      $data = array();
      $columns = array();
      while ($field = $meta->fetch_field())
      {
        $columns[] = &$data[$field->name];
      }

      $res = call_user_func_array(array($statement, 'bind_result'), $columns);
      if (false === $res)
      {
        throw new custom_exception('結果セットのバインドに失敗しました。', __CLASS__ . ':' . __FUNCTION__);
      }

      $result['data'] = $data;
    }

    return $result;
  }

  /**
   * バインドするデータ型のマッピング配列（実質、定数）
   *
   * @access private
   */
  private static $BIND_TYPE_MAPPINGS = array(
    parent::PARAM_INT => 'i',
    parent::PARAM_STR => 's',
  );

  /**
   * ステートメントクラスインスタンス
   *
   * @access private
   */
  private $statement;
}
