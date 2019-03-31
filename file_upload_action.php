<?php

/**
 * ファイルアップロードアクションクラス
 *
 * ファイルアップロードに関する処理を行うクラス
 *
 * @access  public
 * @create  2019/03/23
 * @version 0.1
 */
abstract class file_upload_action extends action
{
  /**
   * コンストラクタ
   *
   * @access public
   */
  public function __construct()
  {
    parent::__construct();
    $this->init();
  }

  /**
   * 初期化処理
   *
   * @access protected
   */
  protected function init()
  {
    $this->set_form(null);
    $this->set_model(null);
    $this->set_storage_handlers(array());
    $this->set_template_convert(null);
    $this->set_template_file_path('');
  }

  /**
   * フォームインスタンス設定
   *
   * @access public
   * @param form $form フォームインスタンス
   */
  public function set_form($form)
  {
    $this->form = $form;
  }

  /**
   * フォームインスタンス取得
   *
   * @access public
   * @return form フォームインスタンス
   */
  public function get_form()
  {
    return $this->form;
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
   * ストレージハンドラー配列設定
   *
   * @access public
   * @param array $storage_handlers ストレージハンドラー配列
   */
  public function set_storage_handlers(array $storage_handlers)
  {
    $this->storage_handlers = $storage_handlers;
  }

  /**
   * ストレージハンドラー配列取得
   *
   * @access public
   * @return array ストレージハンドラー配列
   */
  public function get_storage_handlers()
  {
    return $this->storage_handlers;
  }

  /**
   * テンプレート置換処理インスタンス設定
   *
   * @access public
   * @param template_convert $template_convert テンプレート置換処理インスタンス
   */
  public function set_template_convert($template_convert)
  {
    $this->template_convert = $template_convert;
  }

  /**
   * テンプレート置換処理インスタンス取得
   *
   * @access public
   * @return template_convert テンプレート置換処理インスタンス
   */
  public function get_template_convert()
  {
    return $this->template_convert;
  }

  /**
   * テンプレートファイルパス設定
   *
   * @access public
   * @param string $template_file_path テンプレートファイルパス
   */
  public function set_template_file_path($template_file_path)
  {
    $this->template_file_path = $template_file_path;
  }

  /**
   * テンプレートファイルパス取得
   *
   * @access public
   * @return string テンプレートファイルパス
   */
  public function get_template_file_path()
  {
    return $this->template_file_path;
  }

  protected function check_and_set_file_upload(array $file_upload_settings)
  {
    $result = true;

    $form = $this->get_form();

    $config = config::get_instance();

    foreach ($file_upload_settings as $file_upload_setting)
    {
      $file_upload_value = $form->execute_accessor_method('get', $file_upload_setting['name']);

      // 任意項目で且つ、ファイルがアップロードされていない時はチェックしない
      if (false === $file_upload_setting['required'] &&
        true === isset($file_upload_value['name']) &&
        '' === $file_upload_value['name'] &&
        true === isset($file_upload_value['type']) &&
        '' === $file_upload_value['type'] &&
        true === isset($file_upload_value['tmp_name']) &&
        '' === $file_upload_value['tmp_name'] &&
        false === is_uploaded_file($file_upload_value['tmp_name']) &&
        true === isset($file_upload_value['error']) &&
        UPLOAD_ERR_NO_FILE === $file_upload_value['error'] &&
        true === isset($file_upload_value['size']) &&
        0 === $file_upload_value['size'])
      {
        continue;
      }

      if (false === isset($file_upload_value['error']) ||
          false === is_int($file_upload_value['error']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'は不正なパラメータです');
        continue;
      }

      $is_continue = false;
      switch ($file_upload_value['error'])
      {
        case UPLOAD_ERR_OK:
          // OK
          break;
        case UPLOAD_ERR_NO_FILE:
          // ファイル未選択
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はファイルが選択されていません');
          $is_continue = true;
          break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          // php.ini定義の最大サイズ超過か、フォーム定義の最大サイズ超過 (設定した場合のみ)
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はファイルサイズが大きすぎます');
          $is_continue = true;
          break;
        default:
          // その他のエラー
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'は原因不明のエラーが発生しました');
          $is_continue = true;
          break;
      }

      if (true === $is_continue)
      {
        $result = false;
        continue;
      }

      if (false === isset($file_upload_value['size']) ||
          $file_upload_value['size'] > $file_upload_setting['max_file_size'])
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はアップロード可能なファイルサイズを超えています');
        continue;
      }

      if (false === isset($file_upload_value['tmp_name']) ||
          true === utility::is_empty($file_upload_value['tmp_name']) ||
          false === is_uploaded_file($file_upload_value['tmp_name']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はアップロードされていません');
        continue;
      }

      // ファイルの内容からMIME-TYPEを取得
      $finfo = new finfo(FILEINFO_MIME);
      $mime_type = $finfo->file($file_upload_value['tmp_name']);
      $mime_types = explode(';', $mime_type);
      $mime_type = $mime_types[0];
      if ('image/bmp' === $mime_type)
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はビットマップ画像なのでアップロード出来ません');
        continue;
      }

      if (false === array_key_exists($mime_type, $file_upload_setting['allow_mime_types']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'は許可されていないファイル形式です');
        continue;
      }

      if (false === isset($file_upload_value['name']) ||
        true === utility::is_empty($file_upload_value['name']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のファイル名が不明です');
        continue;
      }

      // 文字コード
      if (false === mb_check_encoding($file_upload_value['name']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の文字コードが不正です');
        continue;
      }

      $tmp_locale = setlocale(LC_CTYPE, '0');
      setlocale(LC_CTYPE, config::get_instance()->search('app_locale'));

      $path_info_name = pathinfo($file_upload_value['name']);

      setlocale(LC_CTYPE, $tmp_locale);

      if (true === utility::is_empty($path_info_name['filename']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のファイル名が空です');
        continue;
      }

      if (false === isset($path_info_name['extension']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の拡張子が不明です');
        continue;
      }

      if (false === array_key_exists($mime_type, $file_upload_setting['allow_extensions']) ||
          false === array_key_exists($path_info_name['extension'], $file_upload_setting['allow_extensions'][$mime_type]))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の拡張子とファイル形式が合っていません');
        continue;
      }

      if (false === isset($file_upload_value['type']) ||
          true === utility::is_empty($file_upload_value['type']))
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のファイル形式が不明です');
        continue;
      }

      if ($mime_type !== $file_upload_value['type'])
      {
        $result = false;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のファイル形式が矛盾しています');
        continue;
      }

      // ファイル名のチェックは、使用しない場合もあるので任意
      if (true === isset($file_upload_setting['is_file_name_check']) &&
          true === $file_upload_setting['is_file_name_check'])
      {
        // ファイル名の最大文字数
        $file_name_length = mb_strlen($file_upload_value['name']);
        if ($file_upload_setting['max_length'] < $file_name_length)
        {
          $result = false;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の長さが最大文字数を超えています');
          continue;
        }

        // アンダーバーが連続
        if (false !== mb_strpos($file_upload_value['name'], '__'))
        {
          $result = false;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'にアンダーバーが連続して含まれています');
          continue;
        }

        // 許容可能なファイル名のチェック（以下などを参考）
        // http://www.all.undo.jp/asr/1st/document/01_03.html
        // https://dobon.net/vb/dotnet/file/invalidpathchars.html

        // 戦闘か末尾にスペース（trimしていない前提）
        if (' ' === mb_substr($path_info_name['filename'], 0, 1) ||
            ' ' === mb_substr($path_info_name['filename'], -1, 1) ||
            '　' === mb_substr($path_info_name['filename'], 0, 1) ||
            '　' === mb_substr($path_info_name['filename'], -1, 1))
        {
          $result = false;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の先頭か末尾に空白が入っています');
          continue;
        }

        $reserved_words = array(
          'CON' => true,
          'PRN' => true,
          'AUX' => true,
          'NUL' => true,
          'CLOCK$' => true,
          'COM0' => true,
          'COM1' => true,
          'COM2' => true,
          'COM3' => true,
          'COM4' => true,
          'COM5' => true,
          'COM6' => true,
          'COM7' => true,
          'COM8' => true,
          'COM9' => true,
          'LPT0' => true,
          'LPT1' => true,
          'LPT2' => true,
          'LPT3' => true,
          'LPT4' => true,
          'LPT5' => true,
          'LPT6' => true,
          'LPT7' => true,
          'LPT8' => true,
          'LPT9' => true,
        );

        // 予約語
        if (true === array_key_exists(strtoupper($file_upload_value['name']), $reserved_words) ||
            true === array_key_exists(strtoupper($path_info_name['filename']), $reserved_words))
        {
          $result = false;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'は予約語です');
          continue;
        }

        // 許容する文字
        $white_list = '/\A(?:[';
        // ひらがな
        $white_list .= $config->search('pattern_hiragana');
        // カタカナ
        $white_list .= $config->search('pattern_katakana');
        $white_list .= '][';
        // 濁点・半濁点
        $white_list .= $config->search('pattern_dakuten');
        $white_list .= ']?|';
        // 長音
        $white_list .= $config->search('pattern_choon');
        $white_list .= '|';
        // 漢字
        $white_list .= $config->search('pattern_kanji');
        $white_list .= '?|[';
        // 全角英数字・半角英数字
        $white_list .= $config->search('pattern_all_width_alphabet_number');
        $white_list .= ']|[';
        // 全角記号と全角スペース
        $white_list .= $config->search('pattern_full_width_sign');
        $white_list .= ']|[';
        // アンダーバー・半角スペース・ドット（拡張子で使うので一つだけ）
        $white_list .= '_ .';
        $white_list .= '])+\z/u';

        $error_message = '';
        for ($i = 0, $dot = 0; $i < $file_name_length; $i++)
        {
          $char = mb_substr($file_upload_value['name'], $i, 1);
          $char_code_point = utility::mb_ord($char);
          if (1 !== preg_match($white_list, $char))
          {
            $error_message = 'に許可されていない文字が含まれています';
            break;
          }
          else if (0x2e === $char_code_point)
          {
            $dot++;
            if (1 < $dot)
            {
              $error_message = 'にドットが複数含まれています';
              break;
            }
          }
        }

        if ('' !== $error_message)
        {
          $result = false;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . $error_message);
          continue;
        }
      }

      if (true === $result)
      {
        $log_error_message = '';
        $file_path = $config->search('app_file_tmp_save_path') . DIRECTORY_SEPARATOR . $file_upload_setting['save_path_identifier'] . DIRECTORY_SEPARATOR . date_create()->format('Ymd');
        // エラーチェックは戻り値で行うのでエラー抑制演算子を付ける
        $r = @mkdir($file_path, 0700, true);
        if (false === $r)
        {
          // 同時に同名のディレクトリを作るアクセスがあった時のチェック用
          if (false === is_dir($file_path))
          {
            // ディレクトリ作成に失敗
            $log_error_message = 'アップロードファイルの保存先ディレクトリの作成に失敗しました';
          }
        }
        if ('' === $log_error_message)
        {
          if (true === isset($file_upload_setting['is_secret']) &&
            true === $file_upload_setting['is_secret'])
          {
            $token = security::get_token();
          }
          else
          {
            $token = '';
          }
          $file_name = hash('sha512', $token . hash_file('sha512', $file_upload_value['tmp_name']) . utility::get_unique_id()) . '.' . $path_info_name['extension'];
          $full_file_path = $file_path . DIRECTORY_SEPARATOR . $file_name;
          $fp = @fopen($full_file_path, 'xb');
          if (false === $fp)
          {
            $log_error_message = 'アップロードファイルの保存先パスのオープンに失敗しました';
          }
          else
          {
            if (true === move_uploaded_file($file_upload_value['tmp_name'], $full_file_path))
            {
              chmod($full_file_path, 0600);
            }
            else
            {
              $log_error_message = 'アップロードファイルの保存に失敗しました';
            }
            fclose($fp);
          }
        }
        if ('' === $log_error_message)
        {
          $form->execute_accessor_method('set', $file_upload_setting['path'], $full_file_path);
        }
        else
        {
          $result = false;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のアップロードに失敗しました。もう一度アップロードして下さい');
          log::get_instance()->write($log_error_message);
        }
      }

      return $result;
    }
  }

  /**
   * ビジネスロジックを実行する。
   * 個別actionにexecuteメソッドが無い時(認証時)に実行される
   *
   * @access public
   */
  public function execute()
  {
    // 優先度中：クライアント側のキャッシュを許可するだが、再度検討
    //session_cache_limiter('private_no_expire');

    session_start();

    // ユーザーの認証セッションの場合、定期的にセッションIDを切り替える
    // セッションIDの更新処理
    if (true === isset($_SESSION['auth_login_id']))
    {
      // 10分以上経過していたらセッションを無効にする
      if (($_SESSION['session_time'] + (10 * 60)) < time())
      {
        // 「ログインしている」というデータを消す
        unset($_SESSION['auth_login_id']);
      }
      else
      {
        // 一定時間(ここでは５分)経過していたらセッションIDを更新する
        if (($_SESSION['session_time'] + (5 * 60)) < time())
        {
          // セッションIDの変更と古いセッションの破棄
          session_regenerate_id(true);
          // セッション基準時間を更新
          $_SESSION['session_time'] = time();
        }
      }
    }

    // 認証状態のチェック
    if (false === isset($_SESSION['auth_login_id']))
    {
      // 認証されていないかタイムアウト
      $this->get_form()->set_auth_error(true);
      return;
    }
    // ビジネスロジックの実行
    $this->execute_auth();
  }

  /**
   * 全てのフォームデータをModelのセッターに設定する
   *
   * @access public
   * @param array $request_parameter_array 設定したいリクエストパラメータの種類が格納されている配列($_GET,$_POST,$_FILES,$_COOKIE)
   */
  public function set_form_to_model($request_parameter_array)
  {
    foreach ($request_parameter_array as $request_parameter_name)
    {
      foreach ($request_parameter_name as $key => $val)
      {
        // モデルのセッターに値を設定していく
        $this->get_model()->create_accessor_name('set', $key, $val);
      }
    }
  }

  /**
   * 全てのフォームデータをフォームクラスに設定する
   *
   * @access public
   * @param array $request_parameter_array 設定したいリクエストパラメータの種類が格納されている配列($_GET,$_POST,$_FILES,$_COOKIE)
   */
  public function set_form_data($request_parameter_array)
  {
    $form = $this->get_form();
    $properties = $form->get_all_properties();
    foreach ($properties as $field => $value)
    {
      $is_exists = false;
      foreach ($request_parameter_array as $request_parameter_name)
      {
        foreach ($request_parameter_name as $key => $val)
        {
          if ($field === $key)
          {
            $is_exists = true;
            // フォームのセッターに値を設定していく
            $form->execute_accessor_method('set', $key, $val);
            break 2;
          }
        }
      }
      if (false === $is_exists)
      {
        $form->execute_accessor_method('set', $field, null);
      }
    }
  }

  /**
   * Modelを連想配列化したデータをセッションデータに設定する
   *
   * @access protected
   * @param array $model_array 連想配列化したモデルクラスインスタンス
   * @param array $no_session_array セッションデータに設定しない項目名がキーになっている配列
   */
  protected function set_model_to_session($model_array, $no_session_array = array())
  {
    foreach ($model_array as $key => $val)
    {
      // キーがセッションに設定しない配列に含まれていなければデータをセット
      if (false === in_array($key, $no_session_array))
      {
        $_SESSION[$key] = $val;
      }
    }
  }

  /**
   * ビジネスロジックを実行する(認証後に下記のexecuteメソッドから呼ぶ)
   *
   * @access protected
   */
  protected function execute_auth()
  {
  }

  /**
   * HTMLに表示するデータのセット
   *
   * @access protected
   */
  protected function set_html()
  {
  }

  /**
   * フォームインスタンス
   *
   * @access private
   */
  private $form;

  /**
   * モデルインスタンス
   *
   * @access private
   */
  private $model;

  /**
   * ストレージハンドラー配列
   *
   * @access private
   */
  private $storage_handlers;

  /**
   * テンプレート置換処理インスタンス
   *
   * @access private
   */
  private $template_convert;

  /**
   * テンプレートファイルパス
   *
   * @access private
   */
  private $template_file_path;
}
