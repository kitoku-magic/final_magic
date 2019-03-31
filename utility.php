<?php

/**
 * ユーティリティクラス
 *
 * 便利なメソッドを提供
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class utility
{

  /**
   * コンストラクタ
   *
   * @access private
   */
  private function __construct()
  {
    // 外部からのインスタンス生成を禁止
  }

  /**
   * 配列をチェックして値を取得する
   *
   * @access public
   * @param array $array チェック対象の配列
   * @param string $key 配列のキー名
   * @param null $default_val 配列に値が存在しない場合に使用する値
   * @return mixed 配列に値が存在すればその値、存在しなければnullを返す
   */
  static public function get_array_val($array, $key, $default_val = null)
  {
    return isset($array[$key]) ? $array[$key] : $default_val;
  }

  /**
   * SQLパラメータの？を＄に順序付けて置き換える
   *
   * @access public
   * @param string $sql 置換対象のSQL文
   * @return string 置換されたSQL文
   */
  static public function sql_param_question_to_dollar_num($sql)
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
   * オブジェクトを連想配列に変換する
   *
   * @access public
   * @param object $obj 任意のオブジェクト
   * @return array 変換された連想配列
   */
  static public function convert_object_to_array($obj)
  {
    // オブジェクトは配列にキャスト
    if (true === is_object($obj))
    {
      $obj = (array) $obj;
    }

    if (true === is_array($obj))
    {
      $new = array();
      foreach ($obj as $key => $val)
      {
        // プロパティ名以外の不要な文字を削除
        $key = mb_substr(mb_strrchr($key, "\0"), 1);
        $new[$key] = self::convert_object_to_array($val);
      }
    }
    else
    {
      $new = $obj;
    }

    return $new;
  }

  /**
   * 全角スペース対応trim
   *
   * 標準のtrim関数の文字に加え全角スペースもtrimする
   *
   * @access public
   * @param string $value trimしたい値
   * @param string $character_mask_pattern trimする対象の文字の正規表現パターン
   * @return string trim後の値
   */
  static public function mb_trim($value, $character_mask_pattern = '(\x20|\x09|\x0a|\x0d|\x00|\x0b|\x{3000})')
  {
    return preg_replace('/\A' . $character_mask_pattern . '++|' . $character_mask_pattern . '++\z/u', '', $value);
  }

  /**
   * ハイフンを置換する
   *
   * @access public
   * @param string $value 置換元の値
   * @param string $replace 置換したいハイフン文字
   * @return string ハイフン置換後の値
   */
  static public function replace_hyphen($value, $replace)
  {
    // 色々なハイフン（主要なもの？）を変換
    $hyphen_list = array(
      "\x2d",
      "\xef\xb9\xa3",
      "\xef\xbc\x8d",
      "\xe2\x80\x90",
      "\xe2\x80\x91",
      "\xe2\x81\x83",
      "\xcb\x97",
      "\xe2\x88\x92",
      "\xe2\x80\x92",
      "\xe2\x80\x93",
      "\xe2\x80\x94",
      "\xe2\x80\x95",
      "\xef\xb9\x98",
      "\xe2\x8e\xaf",
      "\xe2\x8f\xa4",
      "\xe2\x9a\x8a",
      "\xe2\x94\x80",
      "\xe1\x85\xb3",
      "\xe2\xbc\x80",
      "\xe3\x83\xbc",
      "\xe3\x85\xa1",
      "\xe3\x87\x90",
      "\xe4\xb8\x80",
      "\xef\xbd\xb0",
      "\xef\xbf\x9a",
    );
    return str_replace($hyphen_list, $replace, $value);
  }

  /**
   * マルチバイト対応ord
   *
   * 標準のord関数のマルチバイト対応版
   *
   * @access public
   * @param string $char 対象の文字
   * @return number 対象の文字のコードポイントを１０進数にした値
   */
  static public function mb_ord($char)
  {
    return hexdec(bin2hex(mb_convert_encoding($char, 'UTF-32BE')));
  }

  /**
   * 最小桁数のチェック
   *
   * @access public
   * @param string $value チェックしたい値
   * @param string $length チェックしたい最小桁数
   * @return bool 最小桁数以上ならtrue
   */
  static public function check_min_length($value, $length)
  {
    return mb_strlen($value) >= $length;
  }

  /**
   * 最大桁数のチェック
   *
   * @access public
   * @param string $value チェックしたい値
   * @param string $length チェックしたい最大桁数
   * @return bool 最大桁数以下ならtrue
   */
  static public function check_max_length($value, $length)
  {
    return mb_strlen($value) <= $length;
  }

  /**
   * 空かどうかのチェック
   *
   * @access public
   * @param string $value チェックしたい値
   * @return bool 値が空ならtrue
   */
  static public function is_empty($value)
  {
    return null === $value || '' === $value;
  }

  /**
   * 日付が妥当かどうかのチェック
   *
   * @access public
   * @param string $date チェックしたい日付
   * @param string $format 日付フォーマット
   * @return bool 日付が妥当ならtrue
   */
  static public function check_date($date, $format = 'Y-m-d H:i:s')
  {
    $d = date_create($date);
    if (false === $d)
    {
      return $d;
    }
    return $d->format($format) === $date;
  }

  /**
   * 郵便番号が妥当かどうかのチェック
   *
   * @access public
   * @param string $value チェックしたい郵便番号
   * @param bool $is_include_hyphen ハイフンを含んでいるか否か
   * @return bool 郵便番号が妥当ならtrue
   */
  static public function check_zip_code($value, $is_include_hyphen = false)
  {
    $pattern = '/\A[0-9]{3}';
    if (true === $is_include_hyphen)
    {
      $pattern .= '-';
    }
    $pattern .= '[0-9]{4}\z/u';

    return 1 === preg_match($pattern, $value);
  }

  /**
   * 電話番号が妥当かどうかのチェック
   *
   * @access public
   * @param string $value チェックしたい番号
   * @param bool $is_include_hyphen ハイフンを含んでいるか否か
   * @return bool 番号が妥当ならtrue
   */
  static public function check_telephone($value, $is_include_hyphen = false)
  {
    $pattern = '/\A0[1-9][0-9]{0,3}';
    if (true === $is_include_hyphen)
    {
      $pattern .= '-';
    }
    $pattern .= '[0-9]{1,4}';
    if (true === $is_include_hyphen)
    {
      $pattern .= '-';
    }
    $pattern .= '[0-9]{4}\z/u';

    return 1 === preg_match($pattern, $value);
  }

  /**
   * ユニークなIDを取得する
   *
   * @access public
   * @return string ユニークID
   */
  static public function get_unique_id()
  {
    return uniqid(mt_rand() . '', true);
  }

  /**
   * バブルソートを行う
   *
   * @access public
   * @param array $arr ソート対象の配列
   * @param boolean $asc 昇順か降順かを表すフラグ
   */
  static public function bubble_sort(&$arr, $asc = true)
  {
    $flg = true;
    $arr_size = count($arr);
    $k = 0;

    // 昇順・降順の判定処理は繰り返す必要がない為、ループ外に書いた
    if (true === $asc)
    {
      // 昇順
      // ソートする要素がある間繰り返す
      while (true === $flg)
      {
        $flg = false;
        // 配列後方のソート済みの要素を除外して繰り返す回数を減らす
        for ($i = 0; $i < $arr_size - 1 - $k; $i++)
        {
          if ($arr[$i] > $arr[$i + 1])
          {
            $flg = true;
            self::exchange($arr, $i, $i + 1);
          }
        }
        $k++;
      }
    }
    else
    {
      // 降順
      // ソートする要素がある間繰り返す
      while (true === $flg)
      {
        $flg = false;
        // 配列後方のソート済みの要素を除外して繰り返す回数を減らす
        for ($i = 0; $i < $arr_size - 1 - $k; $i++)
        {
          if ($arr[$i] < $arr[$i + 1])
          {
            $flg = true;
            self::exchange($arr, $i, $i + 1);
          }
        }
        $k++;
      }
    }
  }

  /**
   * マージソートを行う(優先度高：降順処理が未完成)
   *
   * @access public
   * @param array $arr ソート対象の配列
   * @param int $size 分割していくブロックの数
   * @param int $offset 配列内のソート開始位置
   * @param boolean $asc 昇順か降順かを表すフラグ
   */
  static public function merge_sort(&$arr, $size, $offset, $asc = true)
  {
    // ブロックが1つになったら分割終了
    if (1 >= $size)
    {
      return;
    }
    // ブロックを2分割
    $block = (int) ($size / 2);

    //-----------------------------
    // ブロックを前半と後半に分ける
    //-----------------------------
    // 前半
    self::merge_sort($arr, $block, $offset, $asc);
    // 後半
    self::merge_sort($arr, $size - $block, $offset + $block, $asc);

    $buf = array();
    // 併合(マージ)操作
    for ($i = 0; $i < $block; $i++)
    {
      $buf[$i] = $arr[$offset + $i];
    }

    $j = $block;
    $i = 0;
    $k = 0;
    while ($i < $block && $j < $size)
    {
      if ($buf[$i] <= $arr[$offset + $j])
      {
        $arr[$offset + $k++] = $buf[$i++];
      }
      else
      {
        $arr[$offset + $k++] = $arr[$offset + $j++];
      }
    }

    while ($i < $block)
    {
      $arr[$offset + $k++] = $buf[$i++];
    }
  }

  /**
   * クイックソートを行う
   *
   * @access public
   * @param array $arr ソート対象の配列
   * @param int $begin ソートする開始位置のインデックス
   * @param int $end ソートする終了位置のインデックス
   * @param boolean $asc 昇順か降順かを表すフラグ
   */
  static public function quick_sort(&$arr, $begin, $end, $asc = true)
  {
    // 開始位置と終了位置が重なるか交わったら処理終了
    if ($begin >= $end)
    {
      return;
    }

    // 開始位置の値を基準値とする
    $standard = $arr[$begin];
    // 昇順・降順の判定処理は繰り返す必要がない為、ループ外に書いた
    if (true === $asc)
    {
      // 昇順
      // 開始位置と終了位置が重なるか交わるまで繰り返す
      for ($lower = $begin, $upper = $end; $lower < $upper;)
      {
        // 開始位置と終了位置が交わるか、開始位置の値が基準値より大きくなるまで開始位置を増やす
        while ($lower <= $upper && $arr[$lower] <= $standard)
        {
          $lower++;
        }
        // 開始位置と終了位置が交わるか、終了位置の値が基準値以下になるまで終了位置を減らす
        while ($lower <= $upper && $arr[$upper] > $standard)
        {
          $upper--;
        }
        // 開始位置と終了位置が重なるか交わってなければ開始位置の値と終了位置の値を入れ替える
        if ($lower < $upper)
        {
          self::exchange($arr, $lower, $upper);
        }
      }
    }
    else
    {
      // 降順
      // 開始位置と終了位置が重なるか交わるまで繰り返す
      for ($lower = $begin, $upper = $end; $lower < $upper;)
      {
        // 開始位置と終了位置が交わるか、開始位置の値が基準値より小さくなるまで開始位置を増やす
        while ($lower <= $upper && $arr[$lower] >= $standard)
        {
          $lower++;
        }
        // 開始位置と終了位置が交わるか、終了位置の値が基準値以上になるまで終了位置を減らす
        while ($lower <= $upper && $arr[$upper] < $standard)
        {
          $upper--;
        }
        // 開始位置と終了位置が重なるか交わってなければ開始位置の値と終了位置の値を入れ替える
        if ($lower < $upper)
        {
          self::exchange($arr, $lower, $upper);
        }
      }
    }

    // 基準値を中央に移動し、
    // 基準値より小さい(大きい)値は左側に、大きい(小さい)値は右側になる様にする
    self::exchange($arr, $begin, $upper);
    // 基準値より左側の値の中でのクイックソート
    self::quick_sort($arr, $begin, $upper - 1, $asc);
    // 基準値より右側の値の中でのクイックソート
    self::quick_sort($arr, $upper + 1, $end, $asc);
  }

  /**
   * 配列の要素を交換する
   *
   * @access protected
   * @param array $arr ソート対象の配列
   * @param int $begin ソートする開始位置のインデックス
   * @param int $end ソートする終了位置のインデックス
   */
  static protected function exchange(&$arr, $begin, $end)
  {
    $tmp = $arr[$begin];
    $arr[$begin] = $arr[$end];
    $arr[$end] = $tmp;
  }
}
