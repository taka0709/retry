<?php
/**
 * WordPress の基本設定
 *
 * このファイルは、インストール時に wp-config.php 作成ウィザードが利用します。
 * ウィザードを介さずにこのファイルを "wp-config.php" という名前でコピーして
 * 直接編集して値を入力してもかまいません。
 *
 * このファイルは、以下の設定を含みます。
 *
 * * MySQL 設定
 * * 秘密鍵
 * * データベーステーブル接頭辞
 * * ABSPATH
 *
 * @link http://wpdocs.osdn.jp/wp-config.php_%E3%81%AE%E7%B7%A8%E9%9B%86
 *
 * @package WordPress
 */

// 注意:
// Windows の "メモ帳" でこのファイルを編集しないでください !
// 問題なく使えるテキストエディタ
// (http://wpdocs.osdn.jp/%E7%94%A8%E8%AA%9E%E9%9B%86#.E3.83.86.E3.82.AD.E3.82.B9.E3.83.88.E3.82.A8.E3.83.87.E3.82.A3.E3.82.BF 参照)
// を使用し、必ず UTF-8 の BOM なし (UTF-8N) で保存してください。

// ** MySQL 設定 - この情報はホスティング先から入手してください。 ** //
/** WordPress のためのデータベース名 */
define('DB_NAME', 'brownwombat75_blog');

/** MySQL データベースのユーザー名 */
define('DB_USER', 'brownwombat75');

/** MySQL データベースのパスワード */
define('DB_PASSWORD', 'zvaxGecvxVb8');

/** MySQL のホスト名 */
define('DB_HOST', 'mysql736.db.sakura.ne.jp');

/** データベースのテーブルを作成する際のデータベースの文字セット */
define('DB_CHARSET', 'utf8');

/** データベースの照合順序 (ほとんどの場合変更する必要はありません) */
define('DB_COLLATE', '');

/**#@+
 * 認証用ユニークキー
 *
 * それぞれを異なるユニーク (一意) な文字列に変更してください。
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org の秘密鍵サービス} で自動生成することもできます。
 * 後でいつでも変更して、既存のすべての cookie を無効にできます。これにより、すべてのユーザーを強制的に再ログインさせることになります。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '8Jil2XE9w~yOi nX=UEl6W6r{oh.vB/|-bTv{n=Q<6c-;5zEGfpG?QcCsz_jaCRi');
define('SECURE_AUTH_KEY',  '1v-,1@rmUO-=Y,uPIl})VrnM8r)i6_8+taZE&QgW^;|{n=,-C*>W}iW5k>WxG3<{');
define('LOGGED_IN_KEY',    'q79-=2d|;BV,R}8k(EUOyI}+h;|Ca`.ZtC[)s1}Y$0Ef!GvF}J}F*ewGLV>!)}Ga');
define('NONCE_KEY',        '+S]-!<%HN*%Hy2/h*`#Sd&sdCKw|Ft(e1a5JYuX1ceP`DL(E9|%wi2v=Xp+(nl![');
define('AUTH_SALT',        'y8`{K%FN;izX]3k~F3*-I[MMY!5(VlCzZ/s0C8@)^</@KL.eF3zGn_~MZ*-RbFr8');
define('SECURE_AUTH_SALT', '-8AS)8Y8*QE]+XCV!2Ovw?wC+.WUf)<`ZNDk}5x:O$vdi_@sbDm-L4KdvDa;v(4_');
define('LOGGED_IN_SALT',   'D$Ko@z5}2}0 ?,[P[4lqv_p`;-Y?v00~|jfYZu-X&;[l)cZ+yG14Fg+d9uQORzAB');
define('NONCE_SALT',       'y`?*nQWZU+C0URMuZr.`{L!(*+5nk<Z=/ !lg6/aDar.pB?.Bz](7]O4||vkk)zb');

/**#@-*/

/**
 * WordPress データベーステーブルの接頭辞
 *
 * それぞれにユニーク (一意) な接頭辞を与えることで一つのデータベースに複数の WordPress を
 * インストールすることができます。半角英数字と下線のみを使用してください。
 */
$table_prefix = 'wp_';

/**
 * 開発者へ: WordPress デバッグモード
 *
 * この値を true にすると、開発中に注意 (notice) を表示します。
 * テーマおよびプラグインの開発者には、その開発環境においてこの WP_DEBUG を使用することを強く推奨します。
 *
 * その他のデバッグに利用できる定数については Codex をご覧ください。
 *
 * @link http://wpdocs.osdn.jp/WordPress%E3%81%A7%E3%81%AE%E3%83%87%E3%83%90%E3%83%83%E3%82%B0
 */
define('WP_DEBUG', false);

/* 編集が必要なのはここまでです ! WordPress でのパブリッシングをお楽しみください。 */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
