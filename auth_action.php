<?php

/**
 * 認証基底アクションクラス
 *
 * 認証が必要なアクションの基底クラス
 *
 * @access  public
 * @create  2019/05/04
 * @version 0.1
 */
abstract class auth_action extends action
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
   * ビジネスロジックを実行する(認証後にexecuteメソッドから呼ぶ)
   *
   * @access protected
   */
  abstract protected function execute_auth();

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
}
