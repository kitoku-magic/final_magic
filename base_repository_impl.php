<?php

/**
 * データアクセスの基底リポジトリの具象クラス
 *
 * @access  public
 * @create  2019/03/16
 * @version 0.1
 */
class base_repository_impl implements base_repository
{
  /**
   * エンティティクラスの接尾語
   *
   * @access public
   */
  const ENTITY_CLASS_SUFFIX = '_entity';

  /**
   * コンストラクタ
   *
   * @param array $storage_handlers ストレージハンドラークラスインスタンス配列
   * @param string $table_name テーブル名
   * @param array $primary_keys 主キー配列
   * @param array $associated_tables 関連しているテーブル名配列
   */
  public function __construct(array $storage_handlers, $table_name, array $primary_keys, array $associated_tables)
  {
    $this->storage_handlers = $storage_handlers;
    $this->table_name = $table_name;
    $this->primary_keys = $primary_keys;
    $this->associated_tables = $associated_tables;
    $this->associated_entities = array();
    foreach ($associated_tables as $associated_table)
    {
      $this->associated_entities[] = $associated_table . self::ENTITY_CLASS_SUFFIX;
    }
  }

  public function get_primary_keys()
  {
    return $this->primary_keys;
  }

  public function begin()
  {
    $this->set_server_type('master');

    return $this->get_storage_handler()->begin();
  }

  public function commit()
  {
    return $this->get_storage_handler()->commit();
  }

  public function rollback()
  {
    return $this->get_storage_handler()->rollback();
  }

  protected function get_storage_handler()
  {
    return $this->storage_handlers[$this->get_server_type()];
  }

  protected function get_table_name()
  {
    return $this->table_name;
  }

  protected function get_associated_tables()
  {
    return $this->associated_tables;
  }

  protected function get_associated_entities()
  {
    return $this->associated_entities;
  }

  protected function set_server_type($server_type)
  {
    if ('master' === $server_type || 'slave' === $server_type)
    {
      return $this->server_type = $server_type;
    }
    else
    {
      throw new custom_exception('データアクセスサーバーの種別が誤っています', __CLASS__ . ':' . __FUNCTION__);
    }
  }

  protected function get_server_type()
  {
    return $this->server_type;
  }

  protected function select()
  {
    return $this->get_storage_handler()->get();
  }

  protected function insert()
  {
    return $this->get_storage_handler()->set(rdbms_storage_handler::SQL_INSERT);
  }

  protected function update()
  {
    return $this->get_storage_handler()->set(rdbms_storage_handler::SQL_UPDATE);
  }

  protected function delete()
  {
    return $this->get_storage_handler()->set(rdbms_storage_handler::SQL_DELETE);
  }

  /**
   * データを取得する（関連テーブルのデータは取得しない）
   *
   * @param int $mode 取得するデータの対象（1件なのか全件なのか等）
   * @return Entity エンティティクラスインスタンス
   */
  protected function fetch($mode)
  {
    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_table_name($this->get_table_name());
    $storage_handler->set_entity_class_name($this->get_table_name() . self::ENTITY_CLASS_SUFFIX);

    return $storage_handler->fetch($mode);
  }

  /**
   * データを取得する（関連テーブルのデータも取得する）
   *
   * @param bool $unique_primary_key_data 主キーが同じ行でも別の配列の要素にするか否か（デフォルトは同じ配列内の要素にまとめる）
   * @return array エンティティクラスインスタンス配列
   */
  protected function fetch_all_associated_entity($unique_primary_key_data = true)
  {
    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_primary_keys($this->get_primary_keys());
    $storage_handler->set_table_name($this->get_table_name());
    $storage_handler->set_entity_class_name($this->get_table_name() . self::ENTITY_CLASS_SUFFIX);
    $storage_handler->set_associated_tables($this->get_associated_tables());
    $storage_handler->set_associated_entities($this->get_associated_entities());

    return $storage_handler->fetch_all_associated_entity($unique_primary_key_data);
  }

  protected function get_last_insert_id()
  {
    return $this->get_storage_handler()->get_last_insert_id();
  }

  /**
   * ストレージハンドラー配列
   *
   * @access private
   */
  private $storage_handlers;

  /**
   * テーブル名
   *
   * @access private
   */
  private $table_name;

  /**
   * 主キー配列
   *
   * @access private
   */
  private $primary_keys;

  /**
   * 関連テーブル配列
   *
   * @access private
   */
  private $associated_tables;

  /**
   * 関連エンティティクラス配列
   *
   * @access private
   */
  private $associated_entities;

  /**
   * データアクセスサーバーの種別（master・slave等）
   *
   * @access private
   */
  private $server_type;
}
