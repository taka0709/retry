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

// Amp 用のスタイルとスクリプト挿入
$bootstrap   = 'maxcdn' . '.bootstrapcdn' . '.com';
$fontawesome = 'use' . '.fontawesome' . '.com';
$ampproject  = 'cdn' . '.ampproject' . '.org';

if( $awesome === 4 ) {
?>
<link rel="stylesheet" href="https://<?php echo $bootstrap; ?>/font-awesome/4.7.0/css/font-awesome.min.css" />
<?php
}
else {
?>
<link rel="stylesheet" href="https://<?php echo $fontawesome; ?>/releases/v5.10.1/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous" />
<?php
}
?>
<script async src="https://<?php echo $ampproject; ?>/v0.js"></script>
<?php
$amp_extensions = thk_amp_extensions();

foreach( $amp_extensions as $key => $val ) {
	if( isset( $luxe[$key] ) ) {
?>
<script async custom-element="<?php echo $key; ?>" src="https://<?php echo $ampproject, $val; ?>"></script>
<?php
	}
}
unset( $amp_extensions );
?>
<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
<?php
wp_enqueue_style( 'luxe-amp', TDEL . '/style-amp.css', false, array(), 'screen' );
wp_add_inline_style( 'luxe-amp', thk_direct_style( TPATH . DSEP . 'style-amp.min.css' ) );

$load = "";
$css_dir = TPATH . DSEP . 'css' . DSEP;
$styles_dir = TPATH . DSEP . 'styles' . DSEP;

// ブロックエディタのギャラリー
if( strpos( $post->post_content, '<ul class="wp-block-gallery ' ) !== false ) {
	$load .= thk_fgc( $styles_dir . 'wp-block-gallery-amp.css' );
}

// 縦書き
if( strpos( $post->post_content, '<div class="wp-block-luxe-blocks-vertical"' ) !== false ) {
	$load .= thk_fgc( $styles_dir . 'vertical.css' );
}

// アコーディオン
if( strpos( $post->post_content, '<div class="wp-block-luxe-blocks-accordion"' ) !== false ) {
	$load .= thk_fgc( $styles_dir . 'accordion-' . $awesome . '.css' );
}

// 紹介文（Profile）
if( strpos( $post->post_content, '<div class="wp-block-luxe-blocks-profile"' ) !== false ) {
	$load .= thk_fgc( $styles_dir . 'profile.css' );
}

// 吹き出し
if( strpos( $post->post_content, '<div class="wp-block-luxe-blocks-balloon"' ) !== false ) {
	// 全共通
	$balloon_dir = $cssdir . 'balloon' . DSEP;
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

// シンタックスハイライター
if( isset( $luxe['highlighter_css'] ) && $luxe['highlighter_css'] !== 'none' ) {
	if( strpos( $post->post_content, '<code class="language-' ) !== false ) {
		$prism_dir = $css_dir . 'prism' . DSEP;
		$load .= thk_fgc( $prism_dir . 'prism-amp-' . $luxe['highlighter_css'] . '.css' );
	}
}

$load = trim( thk_simple_css_minify( $load ) );
wp_add_inline_style( 'luxe-amp', $load );

// AMP 用子テーマ
if( isset( $luxe['child_css'] ) && TDEL !== SDEL ) {
	wp_enqueue_style( 'luxech-amp', SDEL . '/style-amp.css', false, array(), 'screen' );
	wp_add_inline_style( 'luxech-amp', thk_direct_style( SPATH . DSEP . 'style-amp.min.css' ) );
}

// amp-custom 用カスタムヘッダー (投稿単位の AMP 用追加 CSS)
$ampcustom = get_post_meta( $post->ID, 'amp-custom', true );
if( !empty( $ampcustom ) ) {
	if( TDEL === SDEL ) {
		wp_add_inline_style( 'luxe-amp', $ampcustom );
	}
	else {
		wp_add_inline_style( 'luxech-amp', $ampcustom );
	}
}

unset( $load, $css_dir, $styles_dir );
