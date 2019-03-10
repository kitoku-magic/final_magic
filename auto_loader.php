<?php

/**
 * オートロード管理クラス
 *
 * オートロードするクラスを処理するクラス
 *
 * @access  public
 * @create  2019/03/10
 * @version 0.1
 */
class auto_loader
{
  /**
   * クラスをオートロードする
   *
   * @access public
   * @param string $class_name クラス名
   */
  public function load($class_name)
  {
    if (false === function_exists('stream_resolve_include_path'))
    {
      $path = $this->stream_resolve_include_path($class_name . '.php');
    }
    else
    {
      $path = stream_resolve_include_path($class_name . '.php');
    }

    if (false !== $path &&
        true === is_readable($path) &&
        true === is_file($path))
    {
      require_once $path;
    }
  }

  /**
   * クラス名をインクルードパスから解決する
   *
   * PHP5.3.2から使用可能のstream_resolve_include_path()の代替え
   *
   * @access protected
   * @param string $class_name クラス名
   * @return mixed クラス名のフルパスかfalse
   */
  protected function stream_resolve_include_path($class_name)
  {
    $paths = PATH_SEPARATOR === ':' ?
      preg_split('#(?<!phar):#', get_include_path()) :
      explode(PATH_SEPARATOR, get_include_path());

    foreach ($paths as $prefix)
    {
      $ds = DIRECTORY_SEPARATOR === substr($prefix, -1) ? '' : DIRECTORY_SEPARATOR;
      $file = $prefix . $ds . $class_name;

      if (true === file_exists($file))
      {
        return $file;
      }
    }

    return false;
  }
}
