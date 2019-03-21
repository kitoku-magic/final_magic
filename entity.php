<?php

/**
 * エンティティの基底クラス
 *
 * @access  public
 * @create  2019/03/16
 * @version 0.1
 */
abstract class entity
{
  /**
   * エンティティに定義されている全ての項目名の配列を取得する
   */
  abstract public function get_table_columns();

  /**
   * 各エンティティクラスに定義されているプロパティに値を設定する
   *
   * @param string $name プロパティ名
   * @param mixed $value 値
   */
  public function __set($name, $value)
  {
    if (true === $this->is_property_exists($name))
    {
      $this->properties[$name] = $value;
    }
    else
    {
      throw new custom_exception('対象のプロパティが存在しません', __CLASS__ . ':' . __FUNCTION__);
    }
  }

  /**
   * 各エンティティクラスに定義されているプロパティの値を取得する
   *
   * @param string $name プロパティ名
   * @return mixed プロパティの値
   */
  public function __get($name)
  {
    // nullを設定したいケースに備え、array_key_existsにしている
    if (true === array_key_exists($name, $this->properties))
    {
      return $this->properties[$name];
    }
    else
    {
      throw new custom_exception('対象のプロパティに値が設定されていません', __CLASS__ . ':' . __FUNCTION__);
    }
  }

  /**
   * エンティティの任意の項目にデータをセットする
   *
   * @param string $column データを設定したい任意の項目名
   * @param mixed $value 設定したい値
   */
  public function set_entity_data($column, $value)
  {
    $method_name = 'set_' . $column;
    if (true === method_exists($this, $method_name))
    {
      call_user_func_array(array($this, $method_name), array($value));
    }
  }

  /**
   * エンティティに対象の項目が存在するか調べる
   *
   * @param string $field_name 対象の項目名
   * @return bool 項目が存在すればtrue
   */
  public function is_property_exists($field_name)
  {
    // 5.3以上なら、以下の書き方も可能
    // $thisには、{テーブル名}_entity_baseクラスのサブクラスが入っている前提
    //return property_exists(get_parent_class($this), $field_name);
    return array_key_exists($field_name, $this->get_table_columns());
  }

  /**
   * エンティティクラスのプロパティ情報を格納する配列
   *
   * @access private
   */
  private $properties;
}
