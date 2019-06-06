<?php

/**
 * 論理値専用のテンプレート置換処理クラス
 *
 * HTMLテンプレート内の論理値の部分の置換を行うクラス
 *
 * @access  public
 * @create  2011/01/30
 * @version 0.1
 */
class template_convert_bool
{
  /**
   * テンプレート置換ロジック実行
   *
   * @access public
   * @param view $view ビュークラスインスタンス
   */
  public function convert_template(view $view)
  {
    $output_html = $view->get_output_html();

    // 区切り開始文字列の直前までの文字列
    $output_buf = mb_strstr($output_html, '|||', true);
    // 区切り開始文字列以降の文字列
    $after = mb_strstr($output_html, '|||');
    // 区切り開始文字列以降の文字列を、区切り開始文字列で分割した配列
    $after_array = explode('|||', $after);
    // テンプレート側で設定してある名前
    $name = $after_array[1];
    // 表示するHTML部分
    $body = $after_array[2];
    // 置換対象のデータ
    $data = $view->get_action()->get_template_convert()->get_bool_array_val($name);
    if (true === isset($data))
    {
      if (true === is_array($data))
      {
        // ループ
        $row_size = count($data);
        // 表示する行数分繰り返す
        for ($i = 0; $i < $row_size; $i++)
        {
          // 初期化
          $body_buf = $body;
          // 置換対象文字列';;;'が存在するまで繰り返し
          // $data_beforeは、置換対象文字列の直前までの文字列
          while (false !== ($data_before = mb_strstr($body_buf, ';;;', true)))
          {
            // 置換対象文字列で分割した配列
            $data_array = explode(';;;', $body_buf);
            // 置換するデータ
            if ($data[$i] instanceof entity)
            {
              // Entityの場合はgetter実行
              $getter_name = 'get_' . $data_array[1];
              $data_val = $data[$i]->$getter_name();
            }
            else
            {
              $data_val = $data[$i][$data_array[1]];
            }
            // 最初に見つかった置換対象文字列の直後からの文字列
            $data_after = mb_substr(mb_strstr(mb_substr(mb_strstr($body_buf, ';;;'), 3), ';;;'), 3);
            // 1ヶ所の置換が終了したので、文字列をくっつけていく
            $body_buf = $data_before . $data_val . $data_after;
          }
          // 1行分の置換が終了したので、文字列をくっつけていく
          $output_buf .= ltrim($body_buf);
        }
      }
      else
      {
        // 単一
        $output_buf .= ltrim($body);
      }
    }
    // 区切り終了文字列の直後からの文字列をくっつけていく
    $output_buf .= ltrim(mb_substr(mb_strstr(mb_strstr($after, $after_array[3]), '|||'), 3));
    $view->set_output_html($output_buf);
  }
}
