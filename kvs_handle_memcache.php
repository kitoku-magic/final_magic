<?php

require_once('kvs_handle.php');

/**
 * KVSハンドルクラス memcache
 *
 * memcache用のKVSハンドルクラス
 *
 * @access  public
 * @create  2015/10/10
 * @version 0.1
 */
class kvs_handle_memcache extends kvs_handle
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
    $this->set_config(null);
    $this->set_conn(null);
    $this->set_host_name(null);
    $this->set_port_number(null);
    $this->set_error_message(null);
  }

  /**
   * memcache接続処理
   *
   * @access public
   * @return boolean 接続に成功したらtrueを返す
   */
  public function connect()
  {
    // nullならmemcache接続
    if (true === is_null($this->get_conn()))
    {
      // memcache接続
      $this->set_conn(new Memcached());
      $r = $this->get_conn()->addServer($this->get_config()->search('memcache_host'), $this->get_config()->search('memcache_port'));
      if (false === $r)
      {
        // memcache接続失敗
        $this->set_error_message($this->get_config()->search('kvs_connect_error'));
        return false;
      }
    }

    return true;
  }

  /**
   * memcache切断処理
   *
   * @access public
   */
  public function disconnect()
  {
    // null以外ならmemcache切断
    if (false === is_null($this->get_conn()))
    {
      $this->get_conn()->quit();
    }
  }

  /**
   * データ設定
   *
   * @access public
   * @param string $key キー名
   * @param string $val 設定したい値
   * @param int $expire 期限切れとなるまでの時間(0の場合無期限)
   * @return boolean データが設定出来なかった時はfalseを返す
   */
  public function set($key, $val, $expire = 0)
  {
    return $this->get_conn()->set($key, $val, $expire);
  }

  /**
   * データ取得
   *
   * @access public
   * @param string $key キー名
   * @param callable $cache_cb Read-throughキャッシュコールバック
   * @param float $cas_token CASトークン
   * @return mixed キー名に対応した値が存在しなかったらfalseを返す
   */
  public function get($key, $cache_cb = null, $cas_token = 0.0)
  {
    return $this->get_conn()->get($key, $cache_cb, $cas_token);
  }

  /**
   * データ削除
   *
   * @access public
   * @param string $key キー名
   * @param int $time サーバーがアイテムを削除するまでの待ち時間(0の場合即時削除)
   * @return boolean データが削除出来なかった時はfalseを返す
   */
  public function delete($key, $time = 0)
  {
    // 若干怪しい話があったので「空データを設定してから」「削除」の２段階で実装
    $this->get_conn()->set($key, '');

    return $this->get_conn()->delete($key, $time);
  }

  /**
   * 排他制御処理を実行する
   *
   * 書いておいてアレだけど、この辺 http://dekokun.github.io/posts/2013-09-30.html を読んでみても
   * やっぱりRDB側で対応すべき処理かなぁと
   *
   * @access public
   * @param array $commands 排他制御処理を行う処理の名前が格納された配列
   *
   * 以下の形式になっている必要がある
   *
   * array(
   *   0 => array(
   *     'object' => 'クラス名かインスタンス名',
   *     'method' => 'メソッド名',
   *     'argument' => 'メソッドの引数が格納された配列',
   *     'failure_Processing' => array(
   *       0 => array(
   *         'method' => 'memcacheのメソッド名',
   *         'key' => '変更対象のキー名',
   *         'val' => '変更前の値'
   *       ),
   *       1 => ...
   *     )
   *   ),
   *   1 => ...
   * )
   *
   * @param string $lock_key ロックのキー名
   * @param string $lock_val ロックのキーに設定する値
   * @param int $retry_count 処理が失敗した時に何回再実行するか
   * @return boolean 処理が正常に実行されたらtrue、失敗したらfalse
   */
  public function exec_exclusion_control($commands, $lock_key, $lock_val, $retry_count)
  {
    $rollback_ret = true;
    // まずはロックが取得できるかチェック
    $r = $this->get_lock($lock_key, $lock_val);
    if (true === $r)
    {
      // ロックが取得出来たら、一つずつ処理を実行していく
      $command_idx = 0;
      while (count($commands) > $command_idx)
      {
        try
        {
          // 処理実行
          $command_ret = call_user_func_array(array($commands[$command_idx]['object'], $commands[$command_idx]['method']), $commands[$command_idx]['argument']);
          if (false === $command_ret || null === $command_ret)
          {
            // 処理が失敗した
            throw new custom_exception('処理が失敗しました', 1);
          }
          else
          {
            // 処理が成功したので、次の処理に進む
            $command_idx++;
          }
        }
        catch (custom_exception $e)
        {
          // 処理が失敗した時は、更新処理の全てをロールバックする
          $command_idx = 0;
          $rollback_ret = $this->rollback_exclusion_control($commands, $command_idx);
          if (false === $rollback_ret)
          {
            // ロールバックが失敗したら、すぐに処理を終了する(ロックは解放しない)
            return false;
          }
          if (0 < $retry_count)
          {
            // 再実行フラグが設定されている時は、最初の処理からやり直す
            $retry_count--;
            $command_idx = 0;
            continue;
          }
          else
          {
            // 再実行フラグが設定されていない場合も、処理を終了する(ロックは解放しない)
            return false;
          }
        }
      }
      // 全ての処理が終わったのでロックを解放する
      $r = $this->release_lock($lock_key);
      if (false === $r)
      {
        // ロック解放失敗
        return false;
      }
    }
    else
    {
      // ロック取得失敗
      return false;
    }

    return true;
  }

  /**
   * 排他制御を行う為のロックを取得する
   *
   * @access private
   * @param string $lock_key ロックのキー名
   * @param string $lock_val ロックのキーに設定する値
   * @return boolean ロックが取得出来たらtrue、失敗したらfalse
   */
  private function get_lock($lock_key, $lock_val)
  {
    $lock_time = microtime(true) + $this->get_config->search('exclusion_control_lock_time');
    while (true)
    {
      // 既にロックが取得されているかどうか調べる為に、値を追加してチェックする
      $r = $this->get_conn()->add($lock_key, $lock_val);
      // 値が追加出来なければfalseが返る(=ロックが取得されている)
      if (false === $r)
      {
        // ロックの取得の再試行の時間内ならば処理を繰り返す
        if ($lock_time > microtime(true))
        {
          continue;
        }
        else
        {
          return $r;
        }
      }
      else
      {
        return $r;
      }
    }
  }

  /**
   * 排他制御を行う為のロックを解放する
   *
   * @access private
   * @param string $lock_key ロックのキー名
   * @return boolean ロックが解放出来たらtrueを返す、解放出来なかったらfalse
   */
  private function release_lock($lock_key)
  {
    return $this->delete($lock_key);
  }

  /**
   * 排他制御処理失敗時のロールバックを行う
   *
   * @access private
   * @param array $commands 排他制御処理を行う処理の名前が格納された配列
   * @param int $command_idx 排他制御処理を行う処理の位置
   * @return boolean ロールバックが成功したらtrue、失敗したらfalse
   */
  private function rollback_exclusion_control($commands, &$command_idx)
  {
    $rollback_ret = null;

    // 処理の数だけ繰り返す
    $command_count = count($commands);
    for (; $command_idx < $command_count; $command_idx++)
    {
      // 処理に対応した更新処理のロールバックを全て実行する
      foreach ($commands[$command_idx]['failure_Processing'] as $key => $process)
      {
        // ロールバック実行
        $rollback_ret = call_user_func_array(array($this->get_conn(), $process['method']), array($process['key'], $process['val']));
        if (false === $rollback_ret || null === $rollback_ret)
        {
          return false;
        }
      }
    }

    return true;
  }
}
