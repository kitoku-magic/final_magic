<?php

/**
 * コントローラークラス
 *
 * MVCAのCを担当するクラス
 *
 * @access  public
 * @create  2010/08/12
 * @version 0.1
 */
class controller
{
  /**
   * コンストラクタ
   *
   * @access public
   */
  public function __construct()
  {
    $this->init();
  }

  /**
   * 初期化処理
   *
   * @access protected
   */
  protected function init()
  {
    $this->set_framework_base_path('');
    $this->set_base_path('');
    $this->set_screen('');
    $this->set_process('');
    $this->set_is_ajax(false);
  }

  /**
   * アプリケーションベースディレクトリのパス設定
   *
   * @access public
   * @param string $base_path アプリケーションベースディレクトリのパス
   */
  public function set_base_path($base_path)
  {
    $this->base_path = $base_path;
  }

  /**
   * アプリケーションベースディレクトリのパス取得
   *
   * @access protected
   * @return string アプリケーションベースディレクトリのパス
   */
  protected function get_base_path()
  {
    return $this->base_path;
  }

  /**
   * フレームワークベースディレクトリのパス設定
   *
   * @access public
   * @param string $framework_base_path フレームワークベースディレクトリのパス
   */
  public function set_framework_base_path($framework_base_path)
  {
    $this->framework_base_path = $framework_base_path;
  }

  /**
   * フレームワークベースディレクトリのパス取得
   *
   * @access protected
   * @return string フレームワークベースディレクトリのパス
   */
  protected function get_framework_base_path()
  {
    return $this->framework_base_path;
  }


  /**
   * リクエスト先の画面名設定
   *
   * @access protected
   * @param string $screen リクエスト先の画面名
   */
  protected function set_screen($screen)
  {
    $this->screen = $screen;
  }

  /**
   * リクエスト先の画面名取得
   *
   * @access protected
   * @return string リクエスト先の画面名
   */
  protected function get_screen()
  {
    return $this->screen;
  }

  /**
   * リクエスト先の処理名設定
   *
   * @access protected
   * @param string $process リクエスト先の処理名
   */
  protected function set_process($process)
  {
    $this->process = $process;
  }

  /**
   * リクエスト先の処理名取得
   *
   * @access protected
   * @return string リクエスト先の処理名
   */
  protected function get_process()
  {
    return $this->process;
  }

  /**
   * Ajaxリクエストか否かを設定
   *
   * @access protected
   * @param bool $is_ajax Ajaxリクエストか否か
   */
  protected function set_is_ajax($is_ajax)
  {
    $this->is_ajax = $is_ajax;
  }

  /**
   * Ajaxリクエストか否かを取得
   *
   * @access protected
   * @return bool Ajaxリクエストか否か
   */
  protected function get_is_ajax()
  {
    return $this->is_ajax;
  }

  /**
   * リクエスト毎に実行される共通処理を行う
   *
   * @access public
   * @param string $system_config_file_name システム設定ファイル名
   */
  public function execute($system_config_file_name)
  {
    //---------
    // 共通処理
    //---------
    // 優先度高：設定ファイルのパースとかリクエストの度に実行する必要がない処理を検討する
    // 優先度中：以下の処理でコントローラーに書くのが不適切なモノを検討する
    // 例外ハンドラの設定
    set_exception_handler(array($this, 'system_exception_handler'));
    // エラーハンドラの設定
    set_error_handler(array($this, 'system_error_handler'), E_ALL | E_STRICT);
    // シャットダウンハンドラの設定
    register_shutdown_function(array($this, 'system_shutdown_handler'));

    // フレームワーク設定ファイル内をパース
    $config = config::get_instance();
    $config->set_base_path($this->get_base_path());
    $config->set_config_data($this->get_framework_base_path() . '/config/final_magic_config.conf');

    // システム設定ファイル内をパース
    $config->set_config_data($this->get_base_path() . '/config/' . $system_config_file_name);

    // システムのソースディレクトリを設定
    set_include_path(get_include_path() . PATH_SEPARATOR . $config->search('app_base_dir') . '/src');

    // タイムゾーンの設定
    date_default_timezone_set($config->search('time_zone'));

    //---------------
    // PHP.iniの設定
    //---------------
    // mb関数で使用される文字エンコーディング
    ini_set('mbstring.internal_encoding', $config->search('pg_character_set'));
    // セッションの保存先のディレクトリ
    ini_set('session.save_path', $config->search('session_save_path'));
    // エントロピーソースとして使用する乱数生成ファイル
    ini_set('session.entropy_file', '/dev/urandom');
    // 乱数生成ファイルから読み込むバイト数
    ini_set('session.entropy_length', '32');
    // セッションIDのハッシュ化に使うハッシュ関数の種類
    ini_set('session.hash_function', 'sha512');
    // セッションIDの保存にCookieを使用するか否か
    ini_set('session.use_cookies', '1');
    // セッションIDの保存にCookieのみを使用するか否か
    ini_set('session.use_only_cookies', '1');
    // クッキーの有効期限はブラウザが閉じられるまで
    ini_set('session.cookie_lifetime', '0');
    // セッションの寿命の分数
    ini_set('session.cache_expire', '180');
    // セッションIDをURLに自動で埋め込むか否か
    ini_set('session.use_trans_sid', '0');
    // JavaScriptからセッションクッキーにアクセスさせない
    ini_set('session.cookie_httponly', 'On');
    // セッションの名前
    ini_set('session.name', $config->search('session_name'));

    // 全てのリクエストパラメータをチェック
    // $_REQUEST内のパラメータ値を変更しても
    // 以下の変数内のパラメータ値に反映されないので、個別にチェックする
    $_GET = $this->check_request_parameter($_GET);
    $_POST = $this->check_request_parameter($_POST);
    $_FILES = $this->check_request_parameter($_FILES);
    $_COOKIE = $this->check_request_parameter($_COOKIE);

    // パラメータから対象画面名を取得
    $this->set_screen($_GET['screen']);
    // パラメータから対象処理名を取得
    $this->set_process($_GET['process']);
    // URLにパラメータが付加されていない時
    if (null === $this->get_screen() && null === $this->get_process())
    {
      // トップページへアクセス
      $this->set_screen($config->search('app_top_function'));
      $this->set_process('input');
    }
    else
    {
      // クラス名の存在チェック
      if (false === in_array($this->get_screen() . '_' . $this->get_process(), $config->search('class_name_list'), true))
      {
        throw new custom_exception('クラス名が存在しません', __CLASS__ . ':' . __FUNCTION__);
      }
    }

    // Ajaxリクエストか否かを判定して設定
    // クラス名の存在チェック
    if (true === in_array($this->get_screen() . '_' . $this->get_process(), $config->search('ajax_action_class_name_list'), true))
    {
      if (true === isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        'fm_xml_http_request' === $_SERVER['HTTP_X_REQUESTED_WITH'])
      {
        $this->set_is_ajax(true);
      }
    }

    // 実行対象のフォームインスタンスの取得
    $form_obj = $this->get_dispatch_class_obj('form');
    // 実行対象のアクションインスタンスの取得
    $action_obj = $this->get_dispatch_class_obj('action');
    $action_obj->set_form($form_obj);
    // 全てのフォーム値をフォームクラスに設定
    $action_obj->set_form_data(array($_GET, $_POST, $_FILES, $_COOKIE));

    // DBを使う設定の時にはDBハンドルを設定
    if ('true' === $config->search('db_used'))
    {
      $action_obj->set_storage_handlers(db_manager::get_storage_handlers());
    }
    $action_obj->set_template_convert(new template_convert());
    $action_obj->set_template_file_path($this->get_screen() . '_' . $this->get_process() . $config->search('template_file_extension'));

    // アクションの実行
    $action_obj->execute();

    $view_obj = new view();
    $view_obj->set_action($action_obj);

    if (true === $this->get_is_ajax())
    {
      // 画面を表示せずに、クライアントにレスポンスを返す
      $view_obj->return_response();
    }
    else
    {
      // 画面を表示
      $view_obj->show_display();
    }
  }

  /**
   * 例外発生時の共通処理
   *
   * @access public
   * @param Exception $e 発生した例外クラスインスタンス
   */
  public function system_exception_handler(Exception $e)
  {
    $message = $e->getMessage();
    if ($e instanceof custom_exception)
    {
      $message = $e->get_message();
      $e->write_log();
    }

    if (true === $this->get_is_ajax())
    {
      header('HTTP/1.1 500 Internal Server Error');
      exit();
    }
    else
    {
      // display_errorsの値によって処理を変更する
      if ('1' === ini_get('display_errors')) {
        echo '<pre>' . $message . '</pre>';
      } else {
        // エラー画面にリダイレクト
        $this->redirect_location('303 See Other', 'error.html');
      }
    }

  }

  /**
   * エラー発生時の共通処理
   *
   * @access public
   * @param int $errno エラーのレベル
   * @param string $errstr エラーメッセージ
   * @param string $errfile エラーが発生したファイル名
   * @param int $errline エラーが発生した行番号
   * @param array $errcontext エラーが発生した場所のアクティブシンボルテーブルを指す配列
   */
  public function system_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
  {
    // 例外ハンドラを呼び出す
    $this->system_exception_handler(new custom_exception($errstr, __CLASS__ . ':' . __FUNCTION__, $errno, $errfile, $errline));
  }

  /**
   * スクリプト終了時の共通処理
   *
   * @access public
   */
  public function system_shutdown_handler()
  {
    // 最後に発生したエラーを取得
    $error = error_get_last();
    if (null !== $error)
    {
      // エラーが発生していた場合は例外ハンドラを呼び出す
      $this->system_exception_handler(new custom_exception($error['message'], __CLASS__ . ':' . __FUNCTION__, $error['type'], $error['file'], $error['line']));
    }
  }

  /**
   * 実行対象のクラスを設定する
   *
   * @access protected
   * @param string $class_type クラスの種類(actionかform)
   * @return object 実行対象のクラスインスタンス
   */
  protected function get_dispatch_class_obj($class_type)
  {
    // クラスファイルパスを生成
    $class_file_path = $this->create_class_file_path($class_type);
    // クラス名を生成
    $class_name = $this->create_class_name($class_type);

    // 実行対象ファイルの存在チェック
    if (check::check_exist_file($class_file_path))
    {
      // 実行対象ファイルの読み込み
      require_once($class_file_path);
    }
    else
    {
      throw new custom_exception($class_type . 'の読み込み不可', __CLASS__ . ':' . __FUNCTION__);
    }

    // 実行対象クラスの存在チェック
    if (check::check_exist_class($class_name))
    {
      // 実行対象インスタンスの生成
      $class_obj = new $class_name;
    }
    else
    {
      throw new custom_exception($class_type . 'インスタンスの生成不可', __CLASS__ . ':' . __FUNCTION__);
    }

    return $class_obj;
  }

  /**
   * 読み込むクラスファイルのパスを生成する
   *
   * @access protected
   * @param string $class_type クラスの種類(actionかform)
   * @return string クラスファイルが置いてあるパス
   */
  protected function create_class_file_path($class_type)
  {
    $scr = $this->get_screen();
    // 生成するクラスファイルのパス
    $class_file_path = $this->get_base_path() . '/src/' . $class_type . '/' . $scr . '/' . $scr . '_';

    // フォームか否かの判断
    if ('form' === $class_type)
    {
      // フォーム
      $class_file_path .= $class_type . '.php';
    }
    else
    {
      // アクション
      $class_file_path .= $this->get_process() . '_' . $class_type . '.php';
    }

    return $class_file_path;
  }

  /**
   * クラス名を生成する
   *
   * @access protected
   * @param string $class_type クラスの種類(actionかform)
   * @return string クラス名
   */
  protected function create_class_name($class_type)
  {
    // 生成するクラス名
    $class_name = $this->get_screen() . '_';

    // フォームか否かの判断
    if ('form' === $class_type)
    {
      // フォーム
      $class_name .= $class_type;
    }
    else
    {
      // アクション
      $class_name .= $this->get_process() . '_' . $class_type;
    }

    return $class_name;
  }

  /**
   * 入力値に不正なデータがないかなどをチェックする
   *
   * @access protected
   * @param array $request リクエストパラメータ
   * @return array 不正な文字を除去後のリクエストパラメータ
   */
  protected function check_request_parameter($request)
  {
    // 配列の時は要素を変数に展開してチェック
    if (true === is_array($request))
    {
      return array_map(array($this, 'check_request_parameter'), $request);
    }
    else
    {
      // magic_quotes_gpc対策
      if (1 === get_magic_quotes_gpc())
      {
        // エスケープ文字を取り除く
        $request = stripslashes($request);
      }
      // 文字エンコードの確認
      if (false === mb_check_encoding($request))
      {
        throw new custom_exception('文字コード不正: 対象文字 = ' . $request, __CLASS__ . ':' . __FUNCTION__);
      }
      // NULLバイト攻撃対策
      // NULLバイト文字を取り除く
      $request = $this->delete_null_byte($request);

      return $request;
    }
  }

  /**
   * NULLバイト文字を取り除く
   *
   * @access protected
   * @param string $check_val チェック対象の文字列
   * @return string NULLバイト文字を除去後の文字列
   */
  protected function delete_null_byte($check_val)
  {
    // チェックする文字列の長さ
    $val_length = mb_strlen($check_val);

    // 文字列の長さ分ループ
    for ($i = 0; $i < $val_length; $i++)
    {
      // １文字取得
      $character = mb_substr($check_val, $i, 1);
      // Unicodeのコードポイント値を取得
      $code_point = utility::mb_ord($character);
      // NULLバイト文字なら取り除いて処理終了
      if (0 === $code_point)
      {
        return str_replace("\0", '', $check_val);
      }
    }

    return $check_val;
  }

  /**
   * 指定したステータスコードで指定画面にリダイレクトする
   *
   * @access protected
   * @param string $http_status HTTPステータスコードと文字列(303 See Otherなど)
   * @param string $path_from_document_root ドキュメントルートからのパスとファイル名
   */
  protected function redirect_location($http_status, $path_from_document_root)
  {
    // URIを生成
    $uri = 'http://' . config::get_instance()->search('host_name') . DIRECTORY_SEPARATOR . $path_from_document_root;
    // ステータスコードを発行
    header('HTTP/1.1 ' . $http_status);
    // リダイレクトを発行
    header('Location: ' . $uri);
    exit();
  }

  /**
   * フレームワークベースディレクトリのパス
   *
   * @access private
   */
  private $framework_base_path;

  /**
   * アプリケーションベースディレクトリのパス
   *
   * @access private
   */
  private $base_path;

  /**
   * リクエスト先の画面名
   *
   * @access private
   */
  private $screen;

  /**
   * リクエスト先の処理名
   *
   * @access private
   */
  private $process;

  /**
   * Ajaxリクエストか否か
   *
   * @access private
   */
  private $is_ajax;
}
