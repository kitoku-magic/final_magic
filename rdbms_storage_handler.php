<?php

/**
 * 汎用RDBMSのストレージクラス
 *
 * DBストレージに対する共通操作を定義
 *
 * @access  public
 * @create  2019/03/16
 * @version 0.1
 */
abstract class rdbms_storage_handler implements storage_handler
{
  /**
   * SQLモード（INSERT）
   *
   * @access public
   */
  const SQL_INSERT = 'insert';

  /**
   * SQLモード（UPDATE）
   *
   * @access public
   */
  const SQL_UPDATE = 'update';

  /**
   * SQLモード（DELETE）
   *
   * @access public
   */
  const SQL_DELETE = 'delete';

  /**
   * バインドするパラメーターの型（int）
   *
   * @access public
   */
  const PARAM_INT = 'int';

  /**
   * バインドするパラメーターの型（string）
   *
   * @access public
   */
  const PARAM_STR = 'string';

  /**
   * データ取得時のモード（1件取得）
   *
   * @access public
   */
  const FETCH_ONE = 1;

  /**
   * データ取得時のモード（全件取得）
   *
   * @access public
   */
  const FETCH_ALL = 2;

  /**
   * データを取得する（関連テーブルのデータは取得しない）
   *
   * @param int $mode 取得するデータの対象（1件なのか全件なのか等）
   */
  abstract public function fetch($mode);

  /**
   * データを取得する（関連テーブルのデータも取得する）
   *
   * @param bool $unique_primary_key_data 主キーが同じ行でも別の配列の要素にするか否か（デフォルトは同じ配列内の要素にまとめる）
   */
  abstract public function fetch_all_associated_entity($unique_primary_key_data);

  /**
   * 最後に追加したレコードの一意なIDを取得する
   */
  abstract public function get_last_insert_id();

  /**
   * トランザクションを開始する
   */
  abstract public function begin();

  /**
   * トランザクションをコミットする
   */
  abstract public function commit();

  /**
   * トランザクションをロールバックする
   */
  abstract public function rollback();

  /**
   * 指定されたテーブルのメタデータを取得する
   *
   * @param string $table_name テーブル名
   */
  abstract protected function get_table_meta_data($table_name);

  /**
   * エンティティクラスのフィールドに対して、適切な型のデータを設定する為にキャストする
   *
   * @param array $table_meta_data テーブルのメタデータ
   * @param string $column_name 項目名
   * @param mixed $column_value 値
   */
  abstract protected function get_cast_value(array $table_meta_data, $column_name, $column_value);

  /**
   * 変更された行数を取得する
   */
  abstract protected function get_affected_rows();

  /**
   * コンストラクタ
   */
  public function __construct()
  {
    $this->where = array();
    $this->entities = array();
  }

  public function set_config($config)
  {
    $this->config = $config;
  }

  public function get_config()
  {
    return $this->config;
  }

  public function set_user_name($user_name)
  {
    $this->user_name = $user_name;
  }

  public function get_user_name()
  {
    return $this->user_name;
  }

  public function set_password($password)
  {
    $this->password = $password;
  }

  public function get_password()
  {
    return $this->password;
  }

  public function set_database_name($database_name)
  {
    $this->database_name = $database_name;
  }

  public function get_database_name()
  {
    return $this->database_name;
  }

  public function set_host_name($host_name)
  {
    $this->host_name = $host_name;
  }

  public function get_host_name()
  {
    return $this->host_name;
  }

  public function set_port_number($port_number)
  {
    $this->port_number = $port_number;
  }

  public function get_port_number()
  {
    return $this->port_number;
  }

  public function set_error_message($error_message)
  {
    $this->error_message = $error_message;
  }

  public function get_error_message()
  {
    return $this->error_message;
  }

  public function set_primary_keys(array $primary_keys)
  {
    $this->primary_keys = $primary_keys;
  }

  protected function get_primary_keys()
  {
    return $this->primary_keys;
  }

  public function set_table_name($table_name)
  {
    $this->table_name = $table_name;
  }

  protected function get_table_name()
  {
    return $this->table_name;
  }

  protected function set_connection($connection)
  {
    $this->connection = $connection;
  }

  protected function get_connection()
  {
    return $this->connection;
  }

  public function set_is_column_unique($is_column_unique)
  {
    $this->is_column_unique = $is_column_unique;
  }

  protected function get_is_column_unique()
  {
    return $this->is_column_unique;
  }

  /**
   * データ操作を行う対象のカラムを設定する
   *
   * @param mixed $columns 対象のカラム 省略時は全カラム
   */
  public function set_columns($columns = '*')
  {
    $sql_escape_character = $this->get_sql_escape_character();
    if (true === is_array($columns))
    {
      $result = '';
      foreach ($columns as $column)
      {
        // 複数のテーブル間で同じ項目だった時の為に、別名にしている
        $full_column_name = $sql_escape_character . $column[0] . $sql_escape_character . '.' . $sql_escape_character . $column[1] . $sql_escape_character;
        $result .= $full_column_name . ' AS \'' . $column[0] . '.' . $column[1] . '\', ';
      }
      $this->columns = rtrim($result, ', ');
    }
    else
    {
      $this->columns = $columns;
    }
  }

  /**
   * データ操作を行う対象のカラムを取得する
   *
   * @return mixed 対象のカラム
   */
  protected function get_columns()
  {
    return $this->columns;
  }

  public function set_main_table_name($main_table_name)
  {
    $sql_escape_character = $this->get_sql_escape_character();
    $this->main_table_name = $sql_escape_character . $main_table_name . $sql_escape_character;
  }

  protected function get_main_table_name()
  {
    return $this->main_table_name;
  }

  public function set_join(array $join)
  {
    $this->join = $join;
  }

  protected function get_join()
  {
    return $this->join;
  }

  /**
   * SQL文のJOIN句を作る
   *
   * @return string 作られたJOIN句
   */
  protected function make_join()
  {
    $sql_escape_character = $this->get_sql_escape_character();
    $result = '';
    $join = $this->get_join();
    foreach ($join as $value)
    {
      $result .= ' ' . $value['join_type'] . ' JOIN ' . $sql_escape_character . $value['join_table'] . $sql_escape_character . ' ON ';
      foreach ($value['join_where'] as $join_where)
      {
        $result .= $sql_escape_character . $join_where['main_table'] . $sql_escape_character . '.' . $sql_escape_character . $join_where['main_column'] . $sql_escape_character . ' ' . $join_where['bracket'] .
          ' ' . $sql_escape_character . $join_where['relation_table'] . $sql_escape_character . '.' . $sql_escape_character . $join_where['relation_column'] . $sql_escape_character . ' ' . $join_where['conjunction'] . ' ';
      }
    }

    return $result;
  }

  public function set_where(array $where)
  {
    $this->where = $where;
  }

  protected function get_where()
  {
    return $this->where;
  }

  /**
   * SQL文のWHERE句を作る
   *
   * @param array $values WHERE句に設定したい値配列
   * @return string 作られたWHERE句
   */
  protected function make_where(array $values)
  {
    $sql_escape_character = $this->get_sql_escape_character();
    $result = '';

    foreach ($values as $value)
    {
      if (true === isset($value['table']))
      {
        $result .= $sql_escape_character . $value['table'] . $sql_escape_character . '.';
      }
      $result .= $sql_escape_character . $value['name'] . $sql_escape_character . ' ' . $value['bracket'] . ' ' . $value['value'] . ' ' . $value['conjunction'] . ' ';
    }

    return $result;
  }

  public function set_group_by($group_by)
  {
    $this->group_by = $group_by;
  }

  protected function get_group_by()
  {
    return $this->group_by;
  }

  public function set_values(array $values)
  {
    $this->values = $values;
  }

  protected function get_values()
  {
    return $this->values;
  }

  /**
   * SQL文の値の設定部分を作る
   *
   * @return string 作られた値の設定部分
   */
  protected function make_values()
  {
    $result = '';
    $values = $this->get_values();

    if (0 < count($values))
    {
      $result = implode(', ', $values);
    }

    return $result;
  }

  public function set_sql($sql)
  {
    $this->sql = $sql;
  }

  protected function get_sql()
  {
    return $this->sql;
  }

  public function set_bind_types($bind_types)
  {
    $this->bind_types = $bind_types;
  }

  protected function get_bind_types()
  {
    return $this->bind_types;
  }

  public function set_bind_params(array $bind_params)
  {
    $this->bind_params = $bind_params;
  }

  protected function get_bind_params()
  {
    return $this->bind_params;
  }

  public function set_entity_class_name($entity_class_name)
  {
    $this->entity_class_name = $entity_class_name;
  }

  protected function get_entity_class_name()
  {
    return $this->entity_class_name;
  }

  public function set_associated_tables(array $associated_tables)
  {
    $this->associated_tables = $associated_tables;
  }

  protected function get_associated_tables()
  {
    return $this->associated_tables;
  }

  public function set_associated_entities(array $associated_entities)
  {
    $this->associated_entities = $associated_entities;
  }

  protected function get_associated_entities()
  {
    return $this->associated_entities;
  }

  protected function set_main_entity(entity $main_entity)
  {
    $this->main_entity = $main_entity;
  }

  protected function get_main_entity()
  {
    return $this->main_entity;
  }

  protected function set_entities(array $entities)
  {
    $this->entities = $entities;
  }

  protected function get_entities()
  {
    return $this->entities;
  }

  protected function get_entity($entity_index)
  {
    return $this->entities[$entity_index];
  }

  protected function add_entities(entity $entity)
  {
    $this->entities[] = $entity;
  }

  /**
   * RDBMS毎に異なるSQL識別子のエスケープの為の文字を取得する
   *
   * @return string SQL識別子のエスケープの為の文字
   */
  public function get_sql_escape_character()
  {
    // DBハンドラークラスで定義されているSQL識別子のエスケープの為の文字の値を取得する
    return constant(get_class($this) . '::SQL_ESCAPE_CHARACTER');
  }

  /**
   * SELECT文を作ってSQLを実行する
   *
   * @return bool SQL実行結果（成功ならtrue、失敗ならfalse）
   */
  public function get()
  {
    $sql = 'SELECT ' . $this->get_columns();

    // FROM句が無いSQLも有り得るので
    $main_table_name = $this->get_main_table_name();
    if (null !== $main_table_name)
    {
      $sql .= ' FROM ' . $main_table_name;
    }

    $join = $this->make_join();
    if ('' !== $join)
    {
      $sql .= $join;
    }

    $where = $this->make_where($this->get_where());
    if ('' !== $where)
    {
      $sql .= ' WHERE ' . $where;
    }

    // 以下、GROUP BYなどが続く

    return $this->execute_sql($sql);
  }

  /**
   * INSERT・UPDATE・DELETE文を作ってSQLを実行する
   *
   * @param string $mode 実行対象のSQLの種別(INSERT・UPDATE・DELETE等）
   * @return int 更新した行数
   */
  public function set($mode)
  {
    $sql = '';

    // INSERT・UPDATE・DELETEを実行する
    if (self::SQL_INSERT === $mode)
    {
      $sql = 'INSERT INTO ' . $this->get_main_table_name();
      $columns = $this->get_columns();
      if (null !== $columns)
      {
        $sql .= '(' . $columns . ')';
      }
      $values = $this->make_values();
      if ('' !== $values)
      {
        $sql .= ' VALUES(' . $values . ')';
      }
    }
    else if (self::SQL_UPDATE === $mode)
    {
      $sql = 'UPDATE ' . $this->get_main_table_name();
      $values = $this->make_where($this->get_values());
      if ('' !== $values)
      {
        $sql .= ' SET ' . $values;
      }
      $where = $this->make_where($this->get_where());
      if ('' !== $where)
      {
        $sql .= ' WHERE ' . $where;
      }
    }
    else if (self::SQL_DELETE === $mode)
    {
      $sql = 'DELETE FROM ' . $this->get_main_table_name();
      $where = $this->make_where($this->get_where());
      if ('' !== $where)
      {
        $sql .= ' WHERE ' . $where;
      }
    }
    else
    {
      throw new custom_exception('SQLのモードが不明です', __CLASS__ . ':' . __FUNCTION__);
    }

    $result = $this->execute_sql($sql);

    if (true === $result)
    {
      $result = $this->get_affected_rows();
    }
    else
    {
      throw new custom_exception('SQLの実行に失敗しました', __CLASS__ . ':' . __FUNCTION__);
    }

    return $result;
  }

  /**
   * 全てのエンティティにデータをセットする
   *
   * @param bool $unique_primary_key_data 主キーが同じ行でも別の配列の要素にするか否か
   * @param array $data DBからフェッチしたデータ配列
   * @param array $main_table_meta_data メインのテーブルのメタデータ配列
   * @param string $entity_class_name エンティティのクラス名
   * @param array $associated_entities 関連エンティティ配列
   * @param array $associated_tables 関連テーブル配列
   * @param string $main_table_name メインのテーブル名（SELECTした時にテーブル名も書いた時に値が入る）
   */
  protected function set_all_entities(
    $unique_primary_key_data,
    array $data,
    array $main_table_meta_data,
    $entity_class_name,
    array $associated_entities,
    array $associated_tables,
    $main_table_name
  ) {
    $main_entity = $this->get_main_entity();
    $entities = $this->get_entities();

    $entity_created = false;
    if (true === $unique_primary_key_data &&
      null !== $main_entity)
    {
      // 既にエンティティが設定済みで、主キー毎に配列要素をまとめたい時
      $row_values = array();
      $entity_values = array();
      $main_entity_index = array_search($main_entity, $entities, true);
      $primary_keys = $this->get_primary_keys();
      // 主キーにデータが設定され、エンティティが関連付いているかどうか調べる
      foreach ($primary_keys as $primary_key)
      {
        if (true === isset($data[$main_table_name . $primary_key]))
        {
          $value = $data[$main_table_name . $primary_key];
          $row_values[] = $this->get_cast_value($main_table_meta_data, $primary_key, $value);

          if (false !== $main_entity_index)
          {
            $method_name = 'get_' . $primary_key;
            $entity_values[] = call_user_func_array(array($entities[$main_entity_index], $method_name), array());
          }
        }
      }
      // 取得したカラムに、全ての主キーが含まれている &&
      // 既に設定済みのエンティティの全ての主キーの値に、nullが含まれていない &&
      // 取得したカラムの主キーの値と、既に設定済みのエンティティの主キーの値が同じ
      if (count($primary_keys) === count($row_values) &&
        false === in_array(null, $entity_values, true) &&
        $entity_values === $row_values)
      {
        $entity_created = true;
      }
    }
    if (false === $entity_created)
    {
      require_once $this->get_config()->search('app_base_dir') . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'entity' . DIRECTORY_SEPARATOR . $entity_class_name . '.php';
      $main_entity = new $entity_class_name;
    }
    // 関連しているエンティティへの、データの設定
    foreach ($associated_entities as $associated_idx => $associated_entity_class_name)
    {
      require_once $this->get_config()->search('app_base_dir') . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'entity' . DIRECTORY_SEPARATOR . $associated_entity_class_name . '.php';
      $associated_entity_class = new $associated_entity_class_name;
      $associated_table_meta_data = $this->get_table_meta_data($associated_tables[$associated_idx]);
      foreach ($data as $column_name => $column_value)
      {
        $associated_column_name = str_replace($associated_tables[$associated_idx] . '.', '', $column_name);
        if (true === $associated_entity_class->is_property_exists($associated_column_name))
        {
          $column_value = $this->get_cast_value($associated_table_meta_data, $associated_column_name, $column_value);
          $associated_entity_class->set_entity_data($associated_column_name, $column_value);
        }
        $main_column_name = str_replace($main_table_name, '', $column_name);
        if (false === $entity_created &&
            true === $main_entity->is_property_exists($main_column_name))
        {
          $column_value = $this->get_cast_value($main_table_meta_data, $main_column_name, $column_value);
          $main_entity->set_entity_data($main_column_name, $column_value);
        }
      }
      // メインのエンティティに対する、関連しているエンティティの追加
      $method_name = 'add_' . $associated_entity_class_name;
      call_user_func_array(array($main_entity, $method_name), array($associated_entity_class));
    }
    if (false === $entity_created)
    {
      $this->add_entities($main_entity);
    }

    $this->set_main_entity($main_entity);
  }

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config;

  /**
   * ユーザー名
   *
   * @access private
   */
  private $user_name;

  /**
   * パスワード
   *
   * @access private
   */
  private $password;

  /**
   * データベース名
   *
   * @access private
   */
  private $database_name;

  /**
   * ホスト名
   *
   * @access private
   */
  private $host_name;

  /**
   * ポート番号
   *
   * @access private
   */
  private $port_number;

  /**
   * エラーメッセージ
   *
   * @access private
   */
  private $error_message;

  /**
   * 主キー配列
   *
   * @access private
   */
  private $primary_keys;

  /**
   * テーブル名
   *
   * @access private
   */
  private $table_name;

  /**
   * DBコネクションクラスインスタンス
   *
   * @access private
   */
  private $connection;

  /**
   * ユニークなカラム名か否か
   *
   * @access private
   */
  private $is_column_unique;

  /**
   * カラム名配列
   *
   * @access private
   */
  private $columns;

  /**
   * メインのテーブル名（識別子エスケープ済み）
   *
   * @access private
   */
  private $main_table_name;

  /**
   * JOIN句
   *
   * @access private
   */
  private $join;

  /**
   * WHERE句
   *
   * @access private
   */
  private $where;

  /**
   * GROUP BY句
   *
   * @access private
   */
  private $group_by;

  // 以下、having・order_byなどが続く

  /**
   * 値の配列
   *
   * @access private
   */
  private $values;

  /**
   * SQL文
   *
   * @access private
   */
  private $sql;

  /**
   * バインドするデータ型配列
   *
   * @access private
   */
  private $bind_types;

  /**
   * バインドするパラメータ名配列
   *
   * @access private
   */
  private $bind_params;

  /**
   * エンティティのクラス名
   *
   * @access private
   */
  private $entity_class_name;

  /**
   * 関連するテーブル名配列
   *
   * @access private
   */
  private $associated_tables;

  /**
   * 関連するエンティティクラス名配列
   *
   * @access private
   */
  private $associated_entities;

  /**
   * メインのエンティティクラスインスタンス
   *
   * @access private
   */
  private $main_entity;

  /**
   * エンティティクラスインスタンス配列
   *
   * @access private
   */
  private $entities;
}
