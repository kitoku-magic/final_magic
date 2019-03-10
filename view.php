<?php

/**
 * ビュークラス
 *
 * MVCAのVを担当するクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class view
{

  /**
   * アクションクラスインスタンス設定
   *
   * @access public
   * @param action $action アクションクラスインスタンス
   */
  public function set_action($action)
  {
    $this->action = $action;
  }

  /**
   * アクションクラスインスタンス取得
   *
   * @access public
   * @return action アクションクラスインスタンス
   */
  public function get_action()
  {
    return $this->action;
  }

  /**
   * 設定ファイルクラスインスタンス設定
   *
   * @access public
   * @param config $config 設定ファイルクラスインスタンス
   */
  public function set_config($config)
  {
    $this->config = $config;
  }

  /**
   * 設定ファイルクラスインスタンス取得
   *
   * @access protected
   * @return config 設定ファイルクラスインスタンス
   */
  protected function get_config()
  {
    return $this->config;
  }

  /**
   * モデルインスタンス設定
   *
   * @access public
   * @param model $model モデルインスタンス
   */
  public function set_model($model)
  {
    $this->model = $model;
  }

  /**
   * モデルインスタンス取得
   *
   * @access public
   * @return model モデルインスタンス
   */
  public function get_model()
  {
    return $this->model;
  }

  /**
   * 出力するHTML文字列設定
   *
   * @access public
   * @param string $output_html 出力するHTML文字列
   */
  public function set_output_html($output_html)
  {
    $this->output_html = $output_html;
  }

  /**
   * 出力するHTML文字列取得
   *
   * @access public
   * @return string 出力するHTML文字列
   */
  public function get_output_html()
  {
    return $this->output_html;
  }

  /**
   * テンプレート画面を表示
   *
   * @access public
   */
  public function show_display()
  {
    // 出力バッファリングの開始
    ob_start();
    // 出力バッファの内容を消去(クリア)
    ob_clean();

    // テンプレートファイルを読み込んで出力バッファに保存
    require_once($this->get_config()->search('app_base_dir') . '/template/' . $this->get_action()->get_template_file_path());

    // 出力バッファの内容を取得
    $this->set_output_html(ob_get_contents());

    // 出力バッファの内容を消去(クリア)し、出力バッファリングを終了
    ob_end_clean();

    //-------------------------
    // テンプレートの置換を実施
    //-------------------------
    $this->convert_template();

    // レスポンスヘッダーのコンテントタイプの指定
    header('Content-Type: text/html; charset=' . $this->get_config()->search('html_character_set'));

    // 置換後のテンプレートの中身を表示
    echo $this->get_output_html();
  }

  /**
   * テンプレートの置換を実施する
   *
   * @access protected
   */
  protected function convert_template()
  {
    if ('' !== $this->get_output_html())
    {
      // 各状態モードのインスタンスを生成
      $this->get_action()->get_template_convert()->set_template_convert_bool(new template_convert_bool());
      $this->get_action()->get_template_convert()->set_template_convert_multi(new template_convert_multi());
      $this->get_action()->get_template_convert()->set_template_convert_single(new template_convert_single());

      while (true)
      {
        if (false !== mb_strpos($this->get_output_html(), '|||'))
        {
          // 論理値モード
          $template_convert = $this->get_action()->get_template_convert()->get_template_convert_bool();
        }
        else if (false !== mb_strpos($this->get_output_html(), ':::'))
        {
          // 複数値モード
          $template_convert = $this->get_action()->get_template_convert()->get_template_convert_multi();
        }
        else if (false !== mb_strpos($this->get_output_html(), ';;;'))
        {
          // 単一値モード
          $template_convert = $this->get_action()->get_template_convert()->get_template_convert_single();
        }
        else
        {
          // 置換対象の文字列が見つからなかったら処理終了
          break;
        }
        // テンプレートの置換処理を実行
        $template_convert->convert_template($this);
      }

      //-------------------------------
      // エスケープしていた値を元に戻す
      //-------------------------------
      // 論理値
      $this->set_output_html(str_replace('\|\|\|', '|||', $this->get_output_html()));
      // 複数値
      $this->set_output_html(str_replace('\:\:\:', ':::', $this->get_output_html()));
      // 単一値
      $this->set_output_html(str_replace('\;\;\;', ';;;', $this->get_output_html()));
    }
  }

  /**
   * アクションクラスインスタンス
   *
   * @access private
   */
  private $action = null;

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config = null;

  /**
   * モデルインスタンス
   *
   * @access private
   */
  private $model = null;

  /**
   * 出力するHTML文字列
   *
   * @access private
   */
  private $output_html = null;
}
