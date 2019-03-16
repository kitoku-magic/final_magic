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
}
