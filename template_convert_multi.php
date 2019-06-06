<?php

/**
 * 複数値専用のテンプレート置換処理クラス
 *
 * HTMLテンプレート内の複数値の部分の置換を行うクラス
 *
 * @access  public
 * @create  2011/01/30
 * @version 0.1
 */
class template_convert_multi
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
    $output_buf = mb_strstr($output_html, ':::', true);
    // 区切り開始文字列以降の文字列
    $after = mb_strstr($output_html, ':::');
    // 区切り開始文字列以降の文字列を、区切り開始文字列で分割した配列
    $after_array = explode(':::', $after);
    // テンプレート側で設定してある名前
    $name = explode('@', $after_array[1]);
    // 置換対象のデータ
    $data = $view->get_action()->get_template_convert()->get_multi_array_val($name[0]);
    if (true === isset($data))
    {
      // データ有り
      $multi_flag = false;
      // チェックボックスの場合のみ、$dataは配列になっている
      if (true === is_array($data))
      {
        $data_length = count($data);
        for ($i = 0; $i < $data_length; $i++)
        {
          // チェックボックスでどの値を選択するかを判定
          if (0 === strcmp($data[$i], $name[1]))
          {
            $multi_flag = true;
            break;
          }
        }
      }
      else
      {
        // セレクトボックス・ラジオボックスでどの値を選択するかを判定
        if (0 === strcmp($data, $name[1]))
        {
          $multi_flag = true;
        }
      }

      // 判定をして選択か未選択状態の文字列を足しこむ
      if (true === $multi_flag)
      {
        $output_buf .= $view->get_action()->get_template_convert()->get_multi_array_yes_val($name[0]);
      }
      else
      {
        $output_buf = rtrim($output_buf);
        $output_buf .= $view->get_action()->get_template_convert()->get_multi_array_no_val($name[0]);
      }
    }
    // 区切り終了文字列の直後からの文字列をくっつけていく
    $output_buf .= mb_substr(mb_strstr(mb_substr($after, 3), ':::'), 3);
    $view->set_output_html($output_buf);
  }
}
