<?php
/**
 * Luxeritas WordPress Theme - free/libre wordpress platform
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * @copyright Copyright (C) 2015 Thought is free.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 * @author LunaNuko
 * @link https://thk.kanzae.net/
 * @translators rakeem( http://rakeem.jp/ )
 */

require( INC . 'post-meta-boxes.php');
require( INC . 'post-side-boxes.php' );

/*---------------------------------------------------------------------------
 * admin init
 *---------------------------------------------------------------------------*/
add_action( 'admin_init', function() {
	global $luxe, $_is;

	$path = TPATH !== SPATH ? SPATH : TPATH;

	// ブロックエディタ ・ TinyMCE 共通
	if( $_is['edit_posts'] === true ) {
		// エディタ CSS のタイムスタンプチェック
		if( file_exists( $path . DSEP . 'editor-style.css' ) === true && file_exists( $path . DSEP . 'editor-style.min.css' ) === true ) {
			$etime = filemtime( $path . DSEP . 'editor-style.css' );
			if( $etime !== filemtime( $path . DSEP . 'editor-style.min.css' ) ) {
				global $wp_filesystem;
				require_once( INC . 'compress.php' );
				thk_create_editor_style();
				$filesystem = new thk_filesystem();
				$filesystem->init_filesystem();
				$wp_filesystem->touch( $path . DSEP . 'editor-style.min.css', $etime );
			}
		}
	}

	// クラシックブロック用にブロックエディタと旧エディタ両方で読み込むけど、いずれ旧エディタのみにする予定
	require( INC . 'tinymce-before-init.php' );

	// ブロックエディタを無効化する設定になってた場合
	if( isset( $luxe['block_editor_off'] ) ) {
		add_filter( 'use_block_editor_for_post', '__return_false' );
	}
}, 9 );

/*---------------------------------------------------------------------------
 * block categories
 *---------------------------------------------------------------------------*/
add_filter( 'block_categories', function( $categories, $post ) {
	return array_merge( $categories,
		[
			[
				'slug' => 'luxe-blocks',
				'title' => 'Luxeritas Blocks',
				//'icon'  => 'layout',
			]
		]
	);
}, 10, 2 );

/*---------------------------------------------------------------------------
 * enqueue block editor assets
 *---------------------------------------------------------------------------*/
add_action( 'enqueue_block_editor_assets', function() {
	global $luxe;

	$uri = TDEL !== SDEL ? SDEL : TDEL;

	wp_enqueue_style( 'editor-style', $uri . '/editor-style.min.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks' ] );
	wp_enqueue_style( 'editor-style-gutenberg', TDEL . '/editor-style-gutenberg.min.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks' ] );

	if( !isset( $luxe['block_debug'] ) && !isset( $luxe['block_editor_luxe_off'] ) ) {
		wp_enqueue_style( 'luxe-blocks-style', TDEL . '/styles/luxe-blocks-style.min.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks'] );
		wp_enqueue_style( 'luxe-blocks-editor-style', TDEL . '/css/luxe-blocks-editor-style.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks'] );

		wp_enqueue_script(
			'luxe-blocks',
			TDEL . '/js/luxe-blocks.js?v=' . $_SERVER['REQUEST_TIME'],
			array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-rich-text', 'wp-i18n', 'wp-editor', 'wp-compose', 'wp-autop' ),
			false
		);

		if( function_exists( 'wp_set_script_translations' ) === true ) {
			wp_set_script_translations( 'luxe-blocks', 'luxeritas', get_template_directory() . '/languages/admin' );
		}

		$theme = THEME;
		if( TPATH !== SPATH ) {
			$curent = wp_get_theme();
			$theme = wp_get_theme( $curent->get('Template') )->get('Name');
		}
		wp_localize_script( 'luxe-blocks', 'themeName', strtolower( $theme ) );

		/* 登録されてるショートコードの一覧を blocks.js に渡す */
		$registed = get_phrase_list( 'shortcode', true, false );
		foreach( (array)$registed as $key => $value ) {
			$dec = htmlspecialchars_decode($key);
			if( !isset( $registed[$dec] ) ) {
				unset( $registed[$key] );
			}
			$registed[$dec] = @json_decode( $value, true );
		}
		foreach( (array)$registed as $key => $value ) {
			foreach( (array)$value as $k => $val ) {
				if( $k === 'label' ) {
					$registed[$key]['label'] = mb_strimwidth( $val, 0, 25 );
				}
			}
		}

		if( empty( $registed ) ) {
			$registed = ['empty'=>
				['label'=>__('There is no active shortcode.','luxeritas'),'php'=>false,'close'=>false,'hide'=>false,'active'=>false]
			];
		}

		asort( $registed );
		wp_localize_script( 'luxe-blocks', 'luxeShortcodeList', $registed );

		/* 定型文の一覧を blocks.js に渡す */
		$registed = get_phrase_list( 'phrase', true, false );
		foreach( (array)$registed as $key => $value ) {
			$dec = htmlspecialchars_decode($key);
			if( !isset( $registed[$dec] ) ) {
				unset( $registed[$key] );
			}
			$registed[$dec] = @json_decode( $value, true );
		}
		foreach( (array)$registed as $key => $value ) {
			$registed[$key]['file'] = strlen( $key ) . '-' . md5( $key );
			foreach( (array)$value as $k => $val ) {
				if( $k === 'label' ) {
					$registed[$key]['label'] = mb_strimwidth( $val, 0, 25 );
				}
			}
		}

		if( empty( $registed ) ) {
			$registed = ['empty'=>
				['label'=>'','close'=>false,'file'=>'']
			];
		}

		asort( $registed );
		wp_localize_script( 'luxe-blocks', 'luxePhraseList', $registed );

		/* Nonce */
		wp_localize_script( 'luxe-blocks', 'luxePhraseNonce', wp_create_nonce( 'phrase_popup' ) );

		/* シンタックスハイライターの一覧を blocks.js に渡す */
		$highlighter = thk_syntax_highlighter_list();
		asort( $highlighter, SORT_NATURAL | SORT_FLAG_CASE );
		wp_localize_script( 'luxe-blocks', 'luxeHighlighterList', $highlighter );
	}
}, 11 );

/*---------------------------------------------------------------------------
 * admin head
 *---------------------------------------------------------------------------*/
add_action( 'admin_head', function() {
	global $luxe;

	// 以下３つの require はクラシックブロック用にブロックエディタと旧エディタ両方で読み込むけど、いずれ旧エディタのみにする予定
	require( INC . 'thk-post-style.php' );			// TinyMCE 用のスタイル
	require( INC . 'phrase-post.php' );			// 定型文の挿入ボタン
	require( INC . 'shortcode-post.php' );			// ショートコードの挿入ボタン
	if( isset( $luxe['blogcard_enable'] ) ) {
		require( INC . 'blogcard-post-func.php' );	// ブログカードの挿入ボタン
	}

	// 旧エディタを使用してる場合のみ
	if( _is_block_editor() === false ) {
		// 投稿画面のボタン挿入(クイックタグ)
		$teditor_buttons_d = get_theme_admin_mod( 'teditor_buttons_d' );
		if( !empty( $teditor_buttons_d ) ) {
			$luxe['teditor_buttons_d'] = $teditor_buttons_d;
		}

		require( INC . 'quicktags.php' );
	}
}, 100 );

/*---------------------------------------------------------------------------
 * タブの入力ができるようにする
 *---------------------------------------------------------------------------*/
//add_action( 'admin_footer', function() {
add_action( 'admin_print_footer_scripts', function() {
	//旧エディタを使用してる場合のみ
	if( _is_block_editor() === false ) {
?>
<script>
var textareas = document.getElementsByTagName('textarea');
var count = textareas.length;
for( var i = 0; i < count; i++ ) {
	textareas[i].onkeydown = function(e){
		if( e.keyCode === 9 || e.which === 9 ) {
			e.preventDefault();
			var s = this.selectionStart;
			this.value = this.value.substring( 0, this.selectionStart ) + "\t" + this.value.substring( this.selectionEnd );
			this.selectionEnd = s + 1;
		}
	}
}
</script>
<?php
	}
}, 99 );

/*---------------------------------------------------------------------------
 * ブロックエディタのカラーパレット
 *---------------------------------------------------------------------------*/
add_action( 'after_setup_theme', function() {
	add_theme_support( 'editor-color-palette', [
		[
			'name'  => 'White',
			//'slug'  => 'white',
			'color'	=> '#ffffff',
		], [
			'name'  => 'Whitesmoke',
			//'slug'  => 'whitesmoke',
			'color' => '#f5f5f5',
		], [
			'name'  => 'Lightgray',
			//'slug'  => 'lightgray',
			'color' => '#d3d3d3',
		], [
			'name'  => 'Gray',
			//'slug'  => 'gray',
			'color' => '#808080',
		], [
			'name'  => 'Black',
			//'slug'  => 'black',
			'color' => '#000000',
		], [
			'name'  => 'Navy',
			//'slug'  => 'navy',
			'color' => '#000080',
		], [
			'name'  => 'Blue',
			//'slug'  => 'blue',
			'color' => '#0000ff',
		], [
			'name'  => 'Dodgerblue',
			//'slug'  => 'dodgerblue',
			'color' => '#1e90ff',
		], [
			'name'  => 'Deepskyblue',
			//'slug'  => 'deepskyblue',
			'color' => '#00bfff',
		], [
			'name'  => 'Aqua',
			//'slug'  => 'aqua',
			'color' => '#00ffff',
		], [
			'name'  => 'Blueviolet',
			//'slug'  => 'blueviolet',
			'color' => '#8a2be2',
		], [
			'name'  => 'Purple',
			//'slug'  => 'purple',
			'color' => '#800080',
		], [
			'name'  => 'Magenta',
			//'slug'  => 'magenta',
			'color' => '#ff00ff',
		], [
			'name'  => 'Red',
			//'slug'  => 'red',
			'color' => '#ff0000',
		], [
			'name'  => 'Crimson',
			//'slug'  => 'crimson',
			'color' => '#dc143c',
		], [
			'name'  => 'Saddlebrown',
			//'slug'  => 'saddlebrown',
			'color' => '#8b4513',
		], [
			'name'  => 'Coral',
			//'slug'  => 'coral',
			'color' => '#ff7f50',
		], [
			'name'  => 'Orange',
			//'slug'  => 'orange',
			'color' => '#ffa500',
		], [
			'name'  => 'Pink',
			'slug'  => 'pink',
			'color' => '#ffc0cb',
		], [
			'name'  => 'Green',
			//'slug'  => 'green',
			'color' => '#008000',
		], [
			'name'  => 'Springgreen',
			//'slug'  => 'springgreen',
			'color' => '#00ff7f',
		], [
			'name'  => 'Greenyellow',
			//'slug'  => 'greenyellow',
			'color' => '#adff2f',
		], [
			'name'  => 'Yellow',
			//'slug'  => 'yellow',
			'color' => '#ffff00',
		], [
			'name'  => 'Lightyellow',
			//'slug'  => 'lightyellow',
			'color' => '#ffffe0',
		],
	]);
});
