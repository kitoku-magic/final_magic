<?php

/**
 * 単一値専用のテンプレート置換処理クラス
 *
 * HTMLテンプレート内の単一値の部分の置換を行うクラス
 *
 * @access  public
 * @create  2011/01/30
 * @version 0.1
 */
class template_convert_single
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
    $output_buf = mb_strstr($output_html, ';;;', true);
    // 区切り開始文字列以降の文字列
    $after = mb_strstr($output_html, ';;;');
    // 区切り終了文字列の直後からの文字列
    $name_last_after = mb_substr(mb_strstr(mb_substr($after, 3), ';;;'), 3);
    // テンプレート側で設定してある名前
    $name = explode(';;;', $after);
    // 置換対象のデータ
    $data = $view->get_action()->get_template_convert()->get_single_array_val($name[1]);

    if (true === isset($data))
    {
      // データ有り
      $output_buf .= $data . $name_last_after;
    }
    else
    {
      // データ無し
      $output_buf .= $name_last_after;
    }

    $view->set_output_html($output_buf);
  }
}
