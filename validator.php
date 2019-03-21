<?php

/**
 * バリデーターの基底クラス
 *
 * @access  public
 * @create  2019/03/21
 * @version 0.1
 */
class validator
{
  const REQUIRED_MESSAGE = '{show_name}は必須項目です';

  const NOT_EMPTY_MESSAGE = '{show_name}を入力してください';

  const MAX_LENGTH_MESSAGE = '{show_name}は入力可能桁数を超えています';

  const MAIL_FORMAT_MESSAGE = '{show_name}の書式が不正です';

  public function __construct()
  {
    $this->init();
  }

  protected function init()
  {
    $this->validation_settings = array();
  }

  public function validate(form $form)
  {
    $result = true;

    foreach ($this->validation_settings as $name => $validation_setting)
    {
      foreach ($validation_setting['rules'] as $rule_name => $options)
      {
        $ret = $this->$rule_name($form->execute_accessor_method('get', $name), $options);
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

  protected function add(
    $name,
    $show_name,
    $validation_rules
  ) {
    $this->add_validation_settings($name, $show_name, $validation_rules);
  }

  protected function required($value, $options)
  {
    return $value !== null;
  }

  protected function not_empty($value, $options)
  {
    return $value !== '';
  }

  protected function max_length($value, $options)
  {
    return mb_strlen($value) <= $options['value'];
  }

  protected function mail_format($value, $options)
  {
    $r = filter_var($value, FILTER_VALIDATE_EMAIL);
    if (false === $r)
    {
      return false;
    }
    return true;
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

  /**
   * バリデーション設定配列
   *
   * @access private
   */
  private $validation_settings;
}
