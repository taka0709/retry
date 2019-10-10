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

/*---------------------------------------------------------------------------
 * シンタックスハイライター
 * 投稿内で使用されてるショートコード用のスクリプトだけをロードする
 *---------------------------------------------------------------------------*/
if( function_exists('thk_highlighter_load') === false ):
function thk_highlighter_load( $loads, $list, $active ) {
	global $luxe, $post;

	foreach( $list as $key => $val ) {
		if( strpos( $post->post_content, '<code class="language-' . str_replace( 'highlight_', '', $key ) ) !== false ) {
			$active = true;
			break;
		}
	}
	if( $active === true ) {
		$jsdir  = TPATH . DSEP . 'js' . DSEP . 'prism' . DSEP;
		$cssdir = TPATH . DSEP . 'css' . DSEP . 'prism' . DSEP;

		if( !isset( $loads[1]['prism'] ) ) {
			$loads[0] .= thk_fgc( $jsdir . 'prism.js' );
			$loads[1]['prism'] = true;
		}

		// CSS
		if( isset( $luxe['highlighter_css'] ) && $luxe['highlighter_css'] !== 'none' ) {
			$highlighter_css = trim( thk_fgc( $cssdir . 'prism-' . $luxe['highlighter_css'] . '.min.css' ) );
			$highlighter_css .= 'pre[class*="language-"]{margin:20px 0 30px 0}';
			if( TDEL !== SDEL ) {
				wp_add_inline_style( 'luxech', $highlighter_css );
			}
			else {
				wp_add_inline_style( 'luxe', $highlighter_css );
			}
		}

		// Javascript
		foreach( $list as $key => $val ) {
			if( strpos( $post->post_content, '<code class="language-' . str_replace( 'highlight_', '', $key ) ) !== false ) {
				$lang = str_replace( 'highlight_', '', $key );

				if( !isset( $loads[1][$key] ) ) {
					/*
					 * 他言語の依存チェック
					*/
					// markup
					if(
						!isset( $loads[1]['markup'] ) &&
						( $lang === 'php' || $lang === 'aspnet' )
					) {
						$loads[0] .= thk_fgc( $jsdir . 'markup.js' );
						$loads[1]['markup'] = true;
					}
					// css
					if(
						!isset( $loads[1]['css'] ) &&
						( $lang === 'markup' || $lang === 'php' || $lang === 'aspnet' || $lang === 'sass' )
					) {
						$loads[0] .= thk_fgc( $jsdir . 'css.js' );
						$loads[1]['css'] = true;
					}
					// clike
					if(
						!isset( $loads[1]['clike'] ) &&
						( $lang === 'markup' || $lang === 'javascript' || $lang === 'java' || $lang === 'php' || $lang === 'aspnet' || $lang === 'c' || $lang === 'cpp' || $lang === 'csharp' || $lang === 'ruby' || $lang === 'nginx' )
					) {
						$loads[0] .= thk_fgc( $jsdir . 'clike.js' );
						$loads[1]['clike'] = true;
					}
					// javascript
					if(
						!isset( $loads[1]['javascript'] ) &&
						( $lang === 'markup' || $lang === 'php' || $lang === 'aspnet' )
					) {
						$loads[0] .= thk_fgc( $jsdir . 'javascript.js' );
						$loads[1]['javascript'] = true;
					}
					// c
					if(
						!isset( $loads[1]['c'] ) &&
						$lang === 'cpp'
					) {
						$loads[0] .= thk_fgc( $jsdir . 'c.js' );
						$loads[1]['c'] = true;
					}
					// basic
					if(
						!isset( $loads[1]['basic'] ) &&
						$lang === 'vbnet' 
					) {
						$loads[0] .= thk_fgc( $jsdir . 'basic.js' );
						$loads[1]['basic'] = true;
					}
					// sql
					if(
						!isset( $loads[1]['sql'] ) &&
						$lang === 'plsql' 
					) {
						$loads[0] .= thk_fgc( $jsdir . 'sql.js' );
						$loads[1]['sql'] = true;
					}

					// 言語ごとの読み込み
					$loads[0] .= thk_fgc( $jsdir . $lang . '.js' );
					$loads[1][$key] = true;
				}
			}
		}

		if( !isset( $loads[1]['options'] ) ) {
			$loads[0] .= thk_fgc( $jsdir . 'prism-options.js' );
			$loads[1]['options'] = true;
		}
	}
	return $loads;
}
endif;

/*---------------------------------------------------------------------------
 * サイトマップ用インラインスタイル
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_sitemap_inline_style' ) === false ):
function thk_sitemap_inline_style() {
	return <<< STYLE
#sitemap .sitemap-home {
	margin: 0 0 0 20px;
}
#sitemap ul {
	margin: 0 0 30px 0x;
}
#sitemap ul ul,
#sitemap ul ul ul,
#sitemap ul ul ul ul {
	margin: 0 0 0 3px;
	padding: 0;
}
#sitemap li {
	line-height: 1.7;
	margin: 0 0 0 10px;
	padding: 0 0 0 22px;
	border-left: 1px solid #000;
	list-style-type: none;
}
#sitemap li:before {
	content: "-----";
	font-size: 1.4rem;
	margin-left: -23px;
	margin-right: 12px;
	letter-spacing: -3px;
}
#sitemap .sitemap-home a,
#sitemap li a {
	text-decoration: none;
}
STYLE;
}
endif;

/*---------------------------------------------------------------------------
 * インラインスクリプトの読み込み
 *---------------------------------------------------------------------------*/
call_user_func( function() {
	global $luxe, $_is, $awesome, $post;

	// リスト型ページが全文表示か、もしくはスティッキーポストが全文表示の場合に true
	$list_view_content = false;
	if(
		( isset( $luxe['list_view'] ) && $luxe['list_view'] === 'content' ) ||
		( isset( $luxe['sticky_no_excerpt'] ) && $luxe['sticky_no_excerpt'] && is_sticky() === true )
	) {
		$list_view_content = true;
	}

	// 検索結果のハイライト用インラインスタイル
	if( $_is['search'] === true && isset( $luxe['search_highlight'] ) ) {
		if( isset( $luxe['child_css'] ) && TDEL !== SDEL ) {
			wp_add_inline_style( 'luxech', thk_search_highlight_inline_style() );
		}
		else {
			wp_add_inline_style( 'luxe', thk_search_highlight_inline_style() );
		}
	}

	// サイトマップ用インラインスタイル
	if( is_page_template( 'pages/sitemap.php' ) === true ) {
		if( isset( $luxe['child_css'] ) && TDEL !== SDEL ) {
			wp_add_inline_style( 'luxech', thk_sitemap_inline_style() );
		}
		else {
			wp_add_inline_style( 'luxe', thk_sitemap_inline_style() );
		}
	}

	$load = '';
	$css_dir = TPATH . DSEP . 'css' . DSEP;
	$styles_dir = TPATH . DSEP . 'styles' . DSEP;

	// オープニングアニメ用インラインスタイル
	if( isset( $luxe['opening_anime_pages'] ) && isset( $luxe['opening_anime'] ) ) {
		$css = '';
		$referer = '';

		switch( $luxe['opening_anime'] ) {
			case 'curtain_dark': $css = 'op-curtain-dark.css'; break;
			case 'curtain_white': $css = 'op-curtain-white.css'; break;
			case 'shutter_dark': $css = 'op-shutter-dark.css'; break;
			case 'shutter_white': $css = 'op-shutter-white.css'; break;
			case 'stretch_sideways': $css = 'op-stretch-sideways.css'; break;
			case 'stretch_vertically': $css = 'op-stretch-vertically.css'; break;
			case 'rotate_title': $css = 'op-rotate-title.css'; break;
			case 'fadein': $css = 'op-fadein.css'; break;
			default: break;
		}

		if( isset( $luxe['opening_anime_external_only'] ) ) $referer = wp_get_raw_referer();
		if( $_is['home'] === true || $_is['front_page'] === true ) {
			if( isset( $luxe['opening_anime_top_always'] ) ) {
				$load .= thk_fgc( $styles_dir . $css );
			}
			elseif( stripos( (string)$referer, THK_HOME_URL ) === false ) {
				$load .= thk_fgc( $styles_dir . $css );
			}
		}
		elseif( $luxe['opening_anime_pages'] === 'all' ) {
			if( stripos( (string)$referer, THK_HOME_URL ) === false ) {
				$load .= thk_fgc( $styles_dir . $css );
			}
		}
	}

	if( $_is['singular'] === true || ( $_is['singular'] === false && $list_view_content === true ) ) {
		foreach( [
			'<div class="wp-block-luxe-blocks-vertical"'	=> 'vertical.css',			// 縦書き
			'<span class="wp-block-luxe-blocks-topic-icon"'	=> 'topic.css',				// トピック
			'<div class="wp-block-luxe-blocks-accordion"'	=> 'accordion-' . $awesome . '.css',	// アコーディオン
			'<div class="wp-block-luxe-blocks-profile"'	=> 'profile.css',			// 紹介文（Profile）
			' luxe-overlay-'				=> 'block-overlay.css',			// オーバーレイ
			] as $key => $css ) {
			if( strpos( $post->post_content, $key ) !== false || $list_view_content === true ) {
				$load .= thk_fgc( $styles_dir . $css );
			}
		}

		// 吹き出し
		if( strpos( $post->post_content, '<div class="wp-block-luxe-blocks-balloon"' ) !== false || $list_view_content === true ) {
			// 全共通
			$balloon_dir = $css_dir . 'balloon' . DSEP;
			$load .= thk_fgc( $balloon_dir . 'common.css' );
			// 通常共通
			if(
				strpos( $post->post_content, '<div class="luxe-bl-lmain"' ) !== false ||
				strpos( $post->post_content, '<div class="luxe-bl-rmain"' ) !== false
			) {
				$load .= thk_fgc( $balloon_dir . 'normal-common.css' );
			}
			// 左通常
			if( strpos( $post->post_content, '<div class="luxe-bl-lbf"' ) !== false ) {
				$load .= thk_fgc( $balloon_dir . 'normal-left.css' );
			}
			// 右通常
			if( strpos( $post->post_content, '<div class="luxe-bl-rbf"' ) !== false ) {
				$load .= thk_fgc( $balloon_dir . 'normal-right.css' );
			}
			// 考え共通
			if(
				strpos( $post->post_content, '<div class="luxe-bl-ltk"' ) !== false ||
				strpos( $post->post_content, '<div class="luxe-bl-rtk"' ) !== false
			) {
				$load .= thk_fgc( $balloon_dir . 'thought-common.css' );
			}
			// 左考え
			if( strpos( $post->post_content, '<div class="luxe-bl-tk-lbf"' ) !== false ) {
				$load .= thk_fgc( $balloon_dir . 'thought-left.css' );
			}
			// 右考え
			if( strpos( $post->post_content, '<div class="luxe-bl-tk-rbf"' ) !== false ) {
				$load .= thk_fgc( $balloon_dir . 'thought-right.css' );
			}
		}
	}

	// レスポンシブプレビュー
	if( isset( $_GET['respond_frame'] ) && $_is['customize_preview'] === false && $_is['edit_posts'] === true ) {
		$load .=  thk_fgc( $css_dir . 'respond.css' );
	}

	$load = trim( thk_simple_css_minify( $load ) );

	if( !empty( $load ) ) {
		if( isset( $luxe['child_css'] ) && TDEL !== SDEL ) {
			wp_add_inline_style( 'luxech', $load );
		}
		else {
			wp_add_inline_style( 'luxe', $load );
		}
	}

	// シンタックスハイライター
	$load = array( '', array() );
	$highlighter_list = thk_syntax_highlighter_list();
	$highlighter_active = false;

	if( $_is['singular'] === true ) {
		$load = thk_highlighter_load( $load, $highlighter_list, $highlighter_active );
	}
	else {
		if( have_posts() === true ) {
			while( have_posts() === true ) {
				the_post();
				if(
					( isset( $luxe['list_view'] ) && $luxe['list_view'] === 'content' ) ||
					( isset( $luxe['sticky_no_excerpt'] ) && $luxe['sticky_no_excerpt'] && is_sticky() === true )
				) {
					$load = thk_highlighter_load( $load, $highlighter_list, $highlighter_active );
				}
			}
		}
	}

	if( !empty( $load[0] ) ) {
		wp_enqueue_script( 'luxe-inline-script', TURI . '/js/thk-dummy.js', array( 'jquery' ), false );
		$load[0] = '(function(){var jqueryCheck=function(b){if(window.jQuery){b(jQuery)}else{window.setTimeout(function(){jqueryCheck(b)},100)}};jqueryCheck(function(a){;' . "\n" . $load[0] . '});}());';
		wp_add_inline_script( 'luxe-inline-script', $load[0] );
	}
});
