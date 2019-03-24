<?php

/**
 * バリデーターの基底クラス
 *
 * @access  public
 * @create  2019/03/21
 * @version 0.1
 */
abstract class validator
{
  const REQUIRED_MESSAGE = '{show_name}は必須項目です';

  const NOT_EMPTY_MESSAGE = '{show_name}を入力してください';

  const MIN_LENGTH_MESSAGE = '{show_name}は入力必要桁数に足りていません';

  const MAX_LENGTH_MESSAGE = '{show_name}は入力可能桁数を超えています';

  const MAIL_FORMAT_MESSAGE = '{show_name}の書式が不正です';

  const ALPHA_NUM_MESSAGE = '{show_name}は半角英数字で入力してください';

  const JAPANESE_MESSAGE = '{show_name}は日本語で入力してください';

  const JAPANESE_EXTEND_MESSAGE = '{show_name}は日本語で入力してください';

  const HIRAGANA_MESSAGE = '{show_name}はひらがなで入力してください';

  const NUMBER_MESSAGE = '{show_name}は数値を入力してください';

  const INTEGER_MESSAGE = '{show_name}は整数を入力してください';

  const RANGE_MESSAGE = '{show_name}は有効な値の範囲外です';

  const DATE_MESSAGE = '{show_name}は不正な日付です';

  const TELEPHONE_FORMAT_MESSAGE = '{show_name}は不正な番号です';

  public function __construct()
  {
    $this->init();
  }

  protected function init()
  {
    $this->set_form();
    $this->set_validation_settings(array());
    $this->set_is_any_item(false);
  }

  abstract public function set_validation_setting();

  public function validate()
  {
    $result = true;

    $form = $this->get_form();

    foreach ($this->validation_settings as $name => $validation_setting)
    {
      // 項目毎に初期化して、前回の状態を引き継がないようにする
      $this->set_is_any_item(false);
      foreach ($validation_setting['rules'] as $rule_name => $options)
      {
        $function_name = 'is_' . $rule_name;
        $ret = $this->$function_name($form->execute_accessor_method('get', $name), $options);
        if (false === $ret)
        {
          $result = false;
          if (true === isset($options['message']))
          {
            $message = $options['message'];
          }
          else
          {
            $message_format = constant('self::' . strtoupper($rule_name) . '_MESSAGE');
            $message = str_replace('{show_name}', $validation_setting['show_name'], $message_format);
          }
          // formのエラーメッセージセッターに設定
          $form->execute_accessor_method('set', $name . '_error', $message);
          // チェックを継続する設定になっていなければ、次の項目へ
          if (false === isset($options['is_next']) ||
              true !== $options['is_next'])
          {
            break;
          }
        }
      }
    }

    return $result;
  }

  protected function is_required($value, $options)
  {
    return $value !== null;
  }

  protected function is_not_empty($value, $options)
  {
    return $value !== '';
  }

  protected function is_allow_empty($value, $options)
  {
    $this->set_is_any_item(true);
  }

  protected function is_min_length($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      return utility::check_min_length($value, $options['value']);
    }
    else
    {
      return true;
    }
  }

  protected function is_max_length($value, $options)
  {
    return utility::check_max_length($value, $options['value']);
  }

  protected function is_mail_format($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      $r = filter_var($value, FILTER_VALIDATE_EMAIL);
      if (false === $r)
      {
        return false;
      }
      return true;
    }
    else
    {
      return true;
    }
  }

  protected function is_alpha_num($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      return ctype_alnum(strval($value));
    }
    else
    {
      return true;
    }
  }

  protected function is_japanese($value, $options)
  {
    // https://qiita.com/tabo_purify/items/df8f2aa17094b1a60c82 のコメント欄を参考

    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      $config = config::get_instance();
      return 1 === preg_match(
        '/\A(?:[' . $config->search('pattern_hiragana') . $config->search('pattern_katakana') . '][' . $config->search('pattern_dakuten') . ']?|' . $config->search('pattern_choon') . '|' . $config->search('pattern_kanji') . '?)+\z/u',
        $value
      );
    }
    else
    {
      return true;
    }
  }

  protected function is_japanese_extend($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      $config = config::get_instance();
      return 1 === preg_match(
        '/\A(?:[' . $config->search('pattern_hiragana') . $config->search('pattern_katakana') . '][' . $config->search('pattern_dakuten') . ']?|' . $config->search('pattern_choon') . '|' . $config->search('pattern_kanji') . '?|[' . $config->search('pattern_full_width_number') . ']|' . $config->search('pattern_full_width_space') . ')+\z/u',
        $value
      );
    }
    else
    {
      return true;
    }
  }

  protected function is_hiragana($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      $config = config::get_instance();
      return 1 === preg_match(
        '/\A(?:[' . $config->search('pattern_hiragana') .  '][' . $config->search('pattern_dakuten') .  ']?)+\z/u',
        $value
      );
    }
    else
    {
      return true;
    }
  }

  protected function is_number($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      if (true === is_array($value))
      {
        $result = true;
        foreach ($value as $val)
        {
          $result = $this->is_number($val, $options);
          if (false === $result)
          {
            break;
          }
        }
        return $result;
      }
      else
      {
        return ctype_digit(strval($value));
      }
    }
    else
    {
      return true;
    }
  }

  protected function is_integer($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      if (true === is_array($value))
      {
        $result = true;
        foreach ($value as $val)
        {
          $result = $this->is_integer($val, $options);
          if (false === $result)
          {
            break;
          }
        }
        return $result;
      }
      else
      {
        $r = filter_var($value, FILTER_VALIDATE_INT);
        if (false === $r)
        {
          return false;
        }
        return true;
      }
    }
    else
    {
      return true;
    }
  }

  protected function is_range($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      if (true === is_array($value))
      {
        $result = true;
        foreach ($value as $val)
        {
          $result = $this->is_range($val, $options);
          if (false === $result)
          {
            break;
          }
        }
        return $result;
      }
      else
      {
        return $options['min'] <= $value && $value <= $options['max'];
      }
    }
    else
    {
      return true;
    }
  }

  protected function is_date($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      return utility::check_date($value, $options['format']);
    }
    else
    {
      return true;
    }
  }

  protected function is_telephone_format($value, $options)
  {
    $is_check = $this->is_check($value);

    if (true === $is_check)
    {
      return utility::check_telephone($value);
    }
    else
    {
      return true;
    }
  }

  protected function is_check($value)
  {
    $result = true;

    // 任意項目で且つ、値が空の時はチェックしない
    if (true === $this->get_is_any_item() &&
        true === utility::is_empty($value))
    {
      $result = false;
    }

    return $result;
  }

  public function set_form(form $form = null)
  {
    $this->form = $form;
  }

  protected function get_form()
  {
    return $this->form;
  }

  protected function set_validation_settings(array $validation_settings)
  {
    $this->validation_settings = $validation_settings;
  }

  protected function get_validation_settings()
  {
    return $this->validation_settings;
  }

  protected function add_validation_settings($name, $show_name, array $validation_rules)
  {
    $this->validation_settings[$name] = array(
      'show_name' => $show_name,
      'rules' => $validation_rules
    );
  }

  protected function set_is_any_item($is_any_item)
  {
    $this->is_any_item = $is_any_item;
  }

  protected function get_is_any_item()
  {
    return $this->is_any_item;
  }

  /**
   * フォームクラスインスタンス
   *
   * @access private
   */
  private $form;

  /**
   * バリデーション設定配列
   *
   * @access private
   */
  private $validation_settings;

  /**
   * 任意項目か否か
   *
   * @access private
   */
  private $is_any_item;
}
