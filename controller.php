<?php

require_once('config.php');
require_once('custom_exception.php');
require_once('check.php');
require_once('model.php');
require_once('action.php');
require_once('view.php');
require_once('db_manager.php');
require_once('dao.php');
require_once('utility.php');
require_once('debug_util.php');
require_once('security.php');
require_once('view.php');

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
    $this->set_config_obj(new config());
    $this->set_base_path(null);
    $this->set_screen(null);
    $this->set_process(null);
  }

  /**
   * 設定ファイルクラスインスタンス設定
   *
   * @access protected
   * @param config $config_obj 設定ファイルクラスインスタンス
   */
  protected function set_config_obj($config_obj)
  {
    $this->config_obj = $config_obj;
  }

  /**
   * 設定ファイルクラスインスタンス取得
   *
   * @access protected
   * @return config 設定ファイルクラスインスタンス
   */
  protected function get_config_obj()
  {
    return $this->config_obj;
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
   * 設定ファイルのデータを設定する
   *
   * @access public
   * @param string $file_name 設定ファイル名
   */
  public function set_config($file_name)
  {
    $this->get_config_obj()->set_base_path($this->get_base_path());
    $this->get_config_obj()->set_config_data($file_name);
  }

  /**
   * リクエスト毎に実行される共通処理を行う
   *
   * @access public
   */
  public function execute()
  {
    try
    {
      //---------
      // 共通処理
      //---------
      // 優先度高：設定ファイルのパースとかリクエストの度に実行する必要がない処理を検討する
      // 優先度中：以下の処理でコントローラーに書くのが不適切なモノを検討する
      // エラーハンドラの設定
      set_error_handler(array($this, 'system_error_handler'));
      // シャットダウンハンドラの設定
      register_shutdown_function(array($this, 'system_shutdown_handler'));

      // 設定ファイル内をパース
      $this->get_config_obj()->set_config_data('../../../final_magic/config/final_magic.conf');

      // タイムゾーンの設定
      date_default_timezone_set('Asia/Tokyo');
      //---------------
      // PHP.iniの設定
      //---------------
      // mb関数で使用される文字エンコーディング
      ini_set('mbstring.internal_encoding', 'UTF-8');
      // セッションの保存先のディレクトリ
      ini_set('session.save_path', $this->get_config_obj()->search('session_save_path'));
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
      ini_set('session.name', $this->get_config_obj()->search('session_name'));

      // 優先度中：クライアント側のキャッシュを許可するだが、再度検討
      //session_cache_limiter('private_no_expire');

      // ログイン前にセッション機構を使う為に、認証成功時ではなくここに書く
      session_start();

      // 全てのリクエストパラメータをチェック
      // $_REQUEST内のパラメータ値を変更しても
      // 以下の変数内のパラメータ値に反映されないので、個別にチェックする
      $_GET = $this->check_request_parameter($_GET);
      $_POST = $this->check_request_parameter($_POST);
      $_COOKIE = $this->check_request_parameter($_COOKIE);

      // パラメータから対象画面名を取得
      $this->set_screen($_GET['screen']);
      // パラメータから対象処理名を取得
      $this->set_process($_GET['process']);
      // URLにパラメータが付加されていない時
      if (null === $this->get_screen() && null === $this->get_process())
      {
        // トップページへアクセス
        $this->set_screen($this->get_config_obj()->search('app_top_function'));
        $this->set_process('input');
      }
      else
      {
        // クラス名の存在チェック
        if (false === in_array($this->get_screen() . '_' . $this->get_process(), $this->get_config_obj()->search('class_name_list')))
        {
          throw new custom_exception('クラス名が存在しません', 1);
        }
      }

      // 実行対象のモデルインスタンスの取得
      $model_obj = $this->get_dispatch_class_obj('model');

      // 実行対象のアクションインスタンスの取得
      $action_obj = $this->get_dispatch_class_obj('action');
      $action_obj->set_config($this->get_config_obj());
      $action_obj->set_model($model_obj);
      // 全てのフォーム値をモデルに設定
      $action_obj->set_form_to_model(array('_GET', '_POST', '_COOKIE'));
      // DBを使う設定の時にはDBハンドルを設定
      if (0 === strcmp('true', $this->get_config_obj()->search('db_used')))
      {
        $action_obj->set_db_handle(db_manager::get_handle($this->get_config_obj()));
      }
      $action_obj->set_template_convert(new template_convert());

      // アクションの実行
      $action_obj->execute();

      $view_obj = new view();
      $view_obj->set_config($this->get_config_obj());
      $view_obj->set_action($action_obj);
      // 画面を表示
      $view_obj->show_display();
    }
    catch (custom_exception $e)
    {
      //--------------------------------------------------
      // 後続処理でcatchしてなければ、例外は基本ここに来る
      //--------------------------------------------------
      // ログ書き込み
      $e->set_config($this->get_config_obj());
      $e->write_log();
      // エラー画面にリダイレクト
      $this->redirect_location('303 See Other', 'error.html');
    }
  }

  /**
   * エラー発生時の共通処理(Warningエラー等の発生時はこっち)
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
    $e = new custom_exception($errstr, $errno);

    if (8 !== $e->getCode())
    {
      // NOTICEエラー以外は例外を投げる
      throw $e;
    }
    else
    {
      // NOTICEエラー時はログ書き込みのみ行う
      $e->set_config($this->get_config_obj());
      $e->write_log();
    }
  }

  /**
   * エラー発生時の共通処理(Fatalエラー等の発生時はこっち)(優先度高：ここでも捕まえられないエラーはどうしましょ)
   *
   * @access public
   */
  public function system_shutdown_handler()
  {
    // catch出来ないエラーかどうか
    $isError = false;

    // 最後に発生したエラーを取得
    if ($error = error_get_last())
    {
      // エラータイプの判断
      // 以下のエラーはcatch出来ない
      // 優先度中：switchで判断して問題ないか？文字列は入らないか？
      switch ($error['type'])
      {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_CORE_WARNING:
        case E_COMPILE_ERROR:
        case E_COMPILE_WARNING:
          $isError = true;
          break;
      }
    }

    // catch出来ないエラーの場合はログに書き込む
    if ($isError)
    {
      $e = new custom_exception($error['message'], $error['type']);
      $e->set_config($this->get_config_obj());
      $e->write_log();
    }
  }

  /**
   * 実行対象のクラスを設定する
   *
   * @access protected
   * @param string $class_type クラスの種類(actionかmodel)
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
      throw new custom_exception($class_type . 'の読み込み不可', 1);
    }

    // 実行対象クラスの存在チェック
    if (check::check_exist_class($class_name))
    {
      // 実行対象インスタンスの生成
      $class_obj = new $class_name;
    }
    else
    {
      throw new custom_exception($class_type . 'インスタンスの生成不可', 1);
    }

    return $class_obj;
  }

  /**
   * 読み込むクラスファイルのパスを生成する
   *
   * @access protected
   * @param string $class_type クラスの種類(actionかmodel)
   * @return string クラスファイルが置いてあるパス
   */
  protected function create_class_file_path($class_type)
  {
    $scr = $this->get_screen();
    // 生成するクラスファイルのパス
    $class_file_path = $this->get_base_path() . 'program/' . $scr . '/' . $scr . '_';

    // モデルか否かの判断
    if (0 === strcmp('model', $class_type))
    {
      // モデル
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
   * @param string $class_type クラスの種類(actionかmodel)
   * @return string クラス名
   */
  protected function create_class_name($class_type)
  {
    // 生成するクラス名
    $class_name = $this->get_screen() . '_';

    // モデルか否かの判断
    if (0 === strcmp('model', $class_type))
    {
      // モデル
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
      if (false === mb_check_encoding($request, $this->get_config_obj()->search('pg_character_set')))
      {
        throw new custom_exception('文字コード不正', 1);
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
      // ASCIIコード値を取得
      $ascii_code = ord($character);
      // NULLバイト文字なら取り除いて処理終了
      if (0 === $ascii_code)
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
   * @param string $file_path_from_app_root アプリケーションベースディレクトリからのパスとファイル名
   */
  protected function redirect_location($http_status, $path_from_app_root)
  {
    // URIを生成
    $uri = 'http://' . $this->get_config_obj()->search('host_name') . DIRECTORY_SEPARATOR . $path_from_app_root;
    // ステータスコードを発行
    header('HTTP/1.1 ' . $http_status);
    // リダイレクトを発行
    header('Location: ' . $uri);
    exit();
  }

  /**
   * 設定ファイルクラスインスタンス
   *
   * @access private
   */
  private $config_obj;

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
}
