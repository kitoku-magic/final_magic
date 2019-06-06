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
  }

  /**
   * ファイルアップロードのチェックとデータの設定
   *
   * @access protected
   * @param array $file_upload_settings ファイルアップロードの設定情報
   * @return array エラーが無ければ空配列
   */
  protected function check_and_set_file_upload(array $file_upload_settings)
  {
    $errors = array();

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
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'は不正なパラメータです');
        continue;
      }

      $is_continue = true;
      switch ($file_upload_value['error'])
      {
        case UPLOAD_ERR_OK:
          // OK
          break;
        case UPLOAD_ERR_NO_FILE:
          // ファイル未選択
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はファイルが選択されていません');
          $is_continue = false;
          break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          // php.ini定義の最大サイズ超過か、フォーム定義の最大サイズ超過 (設定した場合のみ)
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はファイルサイズが大きすぎます');
          $is_continue = false;
          break;
        default:
          // その他のエラー
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'は原因不明のエラーが発生しました');
          $is_continue = false;
          break;
      }

      if (false === $is_continue)
      {
        $errors[$file_upload_setting['name']] = true;
        continue;
      }

      if (false === isset($file_upload_value['size']) ||
          $file_upload_value['size'] > $file_upload_setting['max_file_size'])
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はアップロード可能なファイルサイズを超えています');
        continue;
      }

      if (false === isset($file_upload_value['tmp_name']) ||
          true === utility::is_empty($file_upload_value['tmp_name']) ||
          false === is_uploaded_file($file_upload_value['tmp_name']))
      {
        $errors[$file_upload_setting['name']] = true;
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
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'はビットマップ画像なのでアップロード出来ません');
        continue;
      }

      if (false === array_key_exists($mime_type, $file_upload_setting['allow_mime_types']))
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'は許可されていないファイル形式です');
        continue;
      }

      if (false === isset($file_upload_value['name']) ||
        true === utility::is_empty($file_upload_value['name']))
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のファイル名が不明です');
        continue;
      }

      // 文字コード
      if (false === mb_check_encoding($file_upload_value['name']))
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の文字コードが不正です');
        continue;
      }

      $tmp_locale = setlocale(LC_CTYPE, '0');
      setlocale(LC_CTYPE, config::get_instance()->search('app_locale'));

      $path_info_name = pathinfo($file_upload_value['name']);

      setlocale(LC_CTYPE, $tmp_locale);

      if (true === utility::is_empty($path_info_name['filename']))
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のファイル名が空です');
        continue;
      }

      if (false === isset($path_info_name['extension']))
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の拡張子が不明です');
        continue;
      }

      if (false === array_key_exists($mime_type, $file_upload_setting['allow_extensions']) ||
          false === array_key_exists($path_info_name['extension'], $file_upload_setting['allow_extensions'][$mime_type]))
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の拡張子とファイル形式が合っていません');
        continue;
      }

      if (false === isset($file_upload_value['type']) ||
          true === utility::is_empty($file_upload_value['type']))
      {
        $errors[$file_upload_setting['name']] = true;
        $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のファイル形式が不明です');
        continue;
      }

      if ($mime_type !== $file_upload_value['type'])
      {
        $errors[$file_upload_setting['name']] = true;
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
          $errors[$file_upload_setting['name']] = true;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'の長さが最大文字数を超えています');
          continue;
        }

        // アンダーバーが連続
        if (false !== mb_strpos($file_upload_value['name'], '__'))
        {
          $errors[$file_upload_setting['name']] = true;
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
          $errors[$file_upload_setting['name']] = true;
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
          $errors[$file_upload_setting['name']] = true;
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
        // アンダーバー・半角スペース
        $white_list .= '_ ';
        $white_list .= '])+\z/u';

        if (1 !== preg_match($white_list, $path_info_name['filename']))
        {
          $errors[$file_upload_setting['name']] = true;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'に許可されていない文字が含まれています');
          continue;
        }
      }

      if (false === array_key_exists($file_upload_setting['name'], $errors))
      {
        $log_error_message = '';
        $file_path = $config->search('app_file_tmp_save_path') . DIRECTORY_SEPARATOR . $file_upload_setting['save_path_identifier'] . DIRECTORY_SEPARATOR . utility::get_date_time_with_timezone(utility::get_current_time_stamp())->format('Ymd');
        $r = utility::make_directory($file_path);
        if (false === $r)
        {
          $log_error_message = 'アップロードファイルの保存先ディレクトリの作成に失敗しました';
        }
        if ('' === $log_error_message)
        {
          chmod($file_path, 0700);
          // ファイル名を推測困難にしたいケースの場合
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
          $form->execute_accessor_method('set', $file_upload_setting['name'], $file_upload_value['name']);
          $form->execute_accessor_method('set', $file_upload_setting['path'], $full_file_path);
        }
        else
        {
          $errors[$file_upload_setting['name']] = true;
          $form->execute_accessor_method('set', $file_upload_setting['name'] . '_error', $file_upload_setting['show_name'] . 'のアップロードに失敗しました。もう一度アップロードして下さい');
          log::get_instance()->write($log_error_message);
        }
      }
    }

    return $errors;
  }
}
