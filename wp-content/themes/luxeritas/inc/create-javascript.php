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

class create_Javascript {
	private $_tdel   = null;
	private $_js_dir = null;
	private $_depend = array();

	public function __construct() {
		$this->_tdel   = pdel( get_template_directory_uri() );
		$this->_js_dir = TPATH . DSEP . 'js' . DSEP;

		// Javascript の依存チェック用配列
		$this->_depend = array(
			'stickykit'=> $this->_js_dir . 'jquery.sticky-kit.min.js',
			'sscroll'  => $this->_js_dir . 'smoothScroll.min.js',
			'autosize' => $this->_js_dir . 'autosize.min.js',
		);
		foreach( $this->_depend as $key => $val ) {
			if( file_exists( $val ) === false ) unset( $this->_depend[$key] );
		}
	}

	/*
	------------------------------------
	UTF-8文字列をUnicodeエスケープする。ただし英数字と記号はエスケープしない。
	source: https://iizukaw.hatenadiary.org/entries/2009/04/22
	------------------------------------ */
	private function unicode_decode( $str ) {
		return preg_replace_callback( "/((?:[^\x09\x0A\x0D\x20-\x7E]{3})+)/", array( $this, 'decode_callback' ), $str );
	}

	private function decode_callback( $matches ) {
		$escaped = '';
		$char = mb_convert_encoding( $matches[1], 'UTF-16', 'UTF-8' );
		for( $i = 0, $l = strlen($char); $i < $l; $i += 2 ) {
			$escaped .=  "\u" . sprintf( "%02x%02x", ord( $char[$i] ), ord( $char[$i+1] ) );
		}
		return $escaped;
	}

	/*
	------------------------------------
	Unicodeエスケープされた文字列をUTF-8文字列に戻す
	source: https://iizukaw.hatenadiary.org/entries/2009/04/22
	------------------------------------ */
	private function unicode_encode( $str ) {
		return preg_replace_callback( "/\\\\u([0-9a-zA-Z]{4})/", array( $this, 'encode_callback' ), $str );
	}

	private function encode_callback( $matches ) {
		return mb_convert_encoding( pack( "H*", $matches[1] ), "UTF-8", "UTF-16" );
	}

	/*
	------------------------------------
	 非同期 CSS の読み込み
	------------------------------------ */
	public function create_css_load_script( $url, $media = null ) {
		global $luxe;
		$ret = '';

		if( file_exists( TPATH . DSEP . 'style.async.min.css' ) === true && filesize( TPATH . DSEP . 'style.async.min.css' ) <= 0 ) {
			return $ret;
		}

		$ret .= <<< SCRIPT
!function(d){
	var n = d.createElement('link');
	n.async = !0;
	n.defer = !0;

SCRIPT;
		if( $media !== null ) $ret .= "n.media = " . $media . "';";

		$ret .= <<< SCRIPT
	n.rel  = "stylesheet";
	n.href = "{$url}?v={$_SERVER['REQUEST_TIME']}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

SCRIPT;
		if( isset( $luxe['web_font_async'] ) ) {
			if( isset( $luxe['font_alphabet'] ) ) {
				$ret .= <<< SCRIPT
	n = d.createElement('link');
	n.async = !0;
	n.defer = !0;

SCRIPT;
				if( $media !== null ) $ret .= "n.media = " . $media . "';";

				$font_alphabet = Web_Font::$alphabet[$luxe['font_alphabet']][1];
				//$font_alphabet = TDEL . '/webfonts/d/' . $luxe['font_alphabet'] . '.css';

				$ret .= <<< SCRIPT
	n.rel  = "stylesheet";
	n.href = "{$font_alphabet}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

SCRIPT;
			}

			if( isset( $luxe['font_japanese'] ) ) {
				$ret .= <<< SCRIPT
	n = d.createElement('link');
	n.async = !0;
	n.defer = !0;

SCRIPT;
				if( $media !== null ) $ret .= "n.media = " . $media . "';";

				$font_japanese = Web_Font::$japanese[$luxe['font_japanese']][1];
				//$font_japanese = TDEL . '/webfonts/d/' . $luxe['font_japanese'] . '.css';

				$ret .= <<< SCRIPT
	n.rel  = "stylesheet";
	n.href = "{$font_japanese}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

SCRIPT;
			}
		}
		$ret .= <<< SCRIPT
}(document);

SCRIPT;
		return $ret;
	}

	/*
	------------------------------------
	 Copy ボタン
	------------------------------------ */
	public function create_copy_button_script() {
		global $luxe;

		$ret = '';

		if( file_exists( TPATH . DSEP . 'style.async.min.css' ) === true && filesize( TPATH . DSEP . 'style.async.min.css' ) <= 0 ) {
			return $ret;
		}

		if( isset( $luxe['copy_button_target'] ) && $luxe['copy_button_target'] === 'url' ) {
			$copy_msg    = $this->unicode_decode( __( 'You copied URL.', 'luxeritas' ) );
			$copy_target = 'true';
		}
		else {
			$copy_msg    = $this->unicode_decode( __( 'You copied this post title and URL.', 'luxeritas' ) );
			$copy_target = 'false';
		}

		$ret .= <<< SCRIPT
var luxeFadeOut = function(o, l) {
	o.style.opacity = 1;
	var w = window
	,   a = performance.now();
	w.requestAnimationFrame( function e(t) {
		var n = (t - a) / l;
		o.style.opacity = Math.max(1 - n, 0), n < 1 ? w.requestAnimationFrame(e) : (o.style.opacity = "", o.style.display = "none")
	})
}, luxeUrlCopy = function() {
	var o = document
	,   l = 0
	,   n = []
	,   a = []
	,   s = []
	,   i = ["cp-page-tops", "cp-page-bottoms"]
	,   p = ["cp-button-tops", "cp-button-bottoms"]
	,   e = {$copy_target}
	,   r = o.body.classList.contains("home")
	,   d = o.getElementsByTagName("h1")
	,   c = o.createElement("input")
	,   m = '<a style="color:#ff0;background:#000"';

	for( l = 0; l < i.length; l++ ) {
		null !== o.getElementById(i[l]) && a.push(o.getElementById(i[l])),
		null !== o.getElementById(p[l]) && s.push(o.getElementById(p[l]));
	} for( l = 0; l < a.length; l++ ) {
		"cp-page-tops" === a[l].id ? n[l] = o.getElementById("sns-tops").childNodes[1].childNodes[1] : n[l] = o.getElementById("sns-bottoms").childNodes[1].childNodes[1];
	}

	if( d = 0 < d.length && !0 !== r ? d[0].innerText : o.title, 0 < a.length ) var g = a[0].getAttribute("data-luxe-permalink");

	c.id = "cp-input-area",
	c.type = "text",
	c.style.position = "absolute",
	c.style.top = 0,
	c.style.zIndex = -10,
	c.value = !0 === e ? g : d + " | " + g, o.body.appendChild(c);

	var	u = []
	,	y = o.getElementById("cp-input-area");

	for( l = 0; l < a.length; l++ ) {
		u[l] = o.createElement("li");
		var h = u[l].style;

		h.flex = "1 1 100%",
		h.minWidth = "100%",
		h.height = "35px",
		h.maxHeight = "35px",
		h.marginTop = a[l].classList.contains("sns-count-true") && !0 === n[l].classList.contains("snsfb") ? "-18px" : "4px",
		h.marginBottom = "-40px",
		h.padding = "6px",
		h.textAlign = "center",
		h.fontSize = "13px",
		h.color = "#fff",
		h.background = "#000",
		h.borderRadius = "4px",
		h.cursor = "auto",
		u[l].innerHTML = "{$copy_msg}",
		u[l].id = "cp-page-tops" === a[l].id ? "cp-msg-tops" : "cp-msg-bottoms",
		luxeFadeOut(u[l], 3e3);

		try {
			y.select(), o.execCommand("copy")
		} catch (n) {
			var f = "This browser was old and could not be copied.";
			u[l].innerHTML = f,
			h.color = "#fff",
			h.background = "red",
			useZeroClipBoard = !0,
			console.log(f)
		}
		var x = s[l].innerHTML;
		x = x.split("<a").join(m),
		s[l].innerHTML = x,
		n[l].appendChild(u[l])
	}
	y.parentNode.removeChild(y),
	setTimeout( function() {
		for( l = 0; a.length > l; l++ ) {
			var e = s[l].innerHTML;
			e = -1 != e.indexOf(m) ? e.split(m).join("<a") : e.replace(/style=[\"\']{1}[^\"\']+?[\"\']{1} /gi, ""),
			s[l].innerHTML = e
		}

		var t = o.getElementById("cp-msg-tops")
		,   n = o.getElementById("cp-msg-bottoms");

		null !== t && t.parentNode.removeChild(t),
		null !== n && n.parentNode.removeChild(n)
	}, 3e3)
};

SCRIPT;
		return $ret;
	}
	/*
	------------------------------------
	 いろいろ
	------------------------------------ */
	public function create_luxe_dom_content_loaded_script() {
		global $luxe, $_is, $awesome;

		$ret = '';
		thk_default_set();

		$side_1_width = isset( $luxe['side_1_width'] ) ? $luxe['side_1_width'] : 366;

		$fa_plus_square  = '\f0fe';
		$fa_minus_square = '\f146';

		if( $awesome === 4 ) {
			$fa_plus_square  = '\f196';
			$fa_minus_square = '\f147';
		}

		require_once( INC . 'colors.php' );
		$conf = new defConfig();
		$colors_class = new thk_colors();

		$defaults = $conf->default_variables();
		$default_colors = $conf->over_all_default_colors();
		unset( $conf );

		$bg_color = isset( $luxe['body_bg_color'] ) ? $luxe['body_bg_color'] : $default_colors[$luxe['overall_image']]['contbg'];
		$inverse = $colors_class->get_text_color_matches_background( $bg_color );
		$rgb = $colors_class->colorcode_2_rgb( $inverse );
		$brap_rgba = 'background: rgba(' . $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'] . ', .5 )';

		$broken = false;
		$ca = new carray();

		$imp = $ca->thk_hex_imp_style();
		$imp_close = $ca->thk_hex_imp_style_close();

		if(
			stripos( $imp, '!;' ) === false ||
			stripos( $imp_close, '!' ) === false
		) {
			$broken = true;
		}
		else {
			$imp = str_replace( '!;', $imp_close, $imp );
		}

		$luxe_version = "?";
		$curent = wp_get_theme();
		if( TPATH === SPATH ) {
			$luxe_version = $curent->get('Version');
		}
		else {
			$parent = wp_get_theme( $curent->get('Template') );
			$luxe_version = $parent->get('Version');
		}

		$ret .= <<< SCRIPT
var luxeDOMContentLoaded = function() {
console.log("Luxeritas " + "{$luxe_version}" + ": loading success");
var w = window
,   d = document
,  luxeGetStyleValue = function(e, t) {
	// Get CSS
	return e && t ? w.getComputedStyle(e).getPropertyValue(t) : null
}, luxeShow = function( e ) {
	// Show an element
	if( e !== null && typeof e !== 'undefined' ) e.style.display = 'block';
}, luxeHide = function( e ) {
	// Hide an element
	if( e !== null && typeof e !== 'undefined' ) e.style.display = 'none';
};

try {  /* page.top */
	!function() {
		// トップに戻るボタンでトップに戻る
		var	e = d.getElementById("page-top")	// トップに戻るボタン
		,	a = e.style;
		e.onclick = function() {
			var n = performance.now()
			,   r = "scrollingElement" in document ? document.scrollingElement : document.documentElement
			,   a = r.scrollTop
			,   c = w.requestAnimationFrame(function e(l) {
				var o = 1 - (l - n) / 400;
				0 < o ? (r.scrollTop = o * a, w.requestAnimationFrame(e)) : (r.scrollTop = 0, w.cancelAnimationFrame(c))
			});
			return !1
		}
		// スクロール監視
		w.addEventListener( "scroll", function() {
			var f = w.pageYOffset;
			// スクロールが500に達したらボタン表示
			500 < f ? (a.opacity = ".5", a.visibility = "visible") : (a.opacity = "0", a.visibility = "hidden")
			// モバイルのレイヤーを閉じる
			if( null !== d.getElementById("ovlay") ) {
				d.documentElement;
				var l = d.querySelector("#layer #nav");
				if( null !== l ) l;
				else {
					var o = d.getElementById("sform")
					,   n = d.getElementById("side");
					if (null !== o && "block" === o.style.display) return;
					null !== n && n
				}
				var t = d.getElementById("layer");
				//if( null === t ) t = d.getElementById("sdblay");

				if( null !== t ) {
					o = t.offsetTop + t.offsetHeight;
					var r = t.offsetTop - w.innerHeight;
					(o < f || f < r) && remove_ovlay()
				}
			}
		}, false );
	}()
} catch (e) {
	console.error("page.top.error: " + e.message)
}

SCRIPT;

		if( $_is['customize_preview'] === true ) {
			// カスタマイズプレビューだと get_theme_mod で値を直接取ってこないとダメですた
			$luxe['awesome_load_css'] = get_theme_mod('awesome_load_css');
		}
		if( $luxe['awesome_load_css'] !== 'none' ) {
			/* placeholder にアイコンフォントを直接書くと、Nu Checker で Warning 出るので、jQuery で置換 */
			$ret .= <<< SCRIPT
!function() {
	for (var e = d.getElementsByClassName("search-field"), r = 0; e.length > r; ++r) {
		var l = e[r].outerHTML; - 1 != l.indexOf("query-input") && -1 != l.indexOf("placeholder") && (e[r].parentNode.innerHTML = l.replace('placeholder="', 'placeholder=" &#xf002; '))
	}
}();
SCRIPT;
		}

		/* 以下 グローバルナビ */
		$ret .= <<< SCRIPT
	function remove_ovlay() {
		var a = [
			"sidebar",
			"sform",
			"ovlay",
			"ovlay-style"
		];

		a.forEach( function( val ) {
			var f = d.getElementById(val);
			if( f !== null ) {
				if( val === "sidebar" || val === "sform" ) {
					f.removeAttribute("style");
				} else {
					f.parentNode.removeChild(f);
				}
			}
		}); d.body.removeAttribute('style'), d.documentElement.removeAttribute("style");
	}

SCRIPT;

		if( isset( $luxe['global_navi_visible'] ) ) {
				$ret .= <<< SCRIPT
try{ /* global.nav */
	!function() {
		var m, v
		,   u = Math
		,   e = d.querySelectorAll("#nav li");

		for( var t = 0, i = e.length; t < i; ++t ) e[t].addEventListener("mouseenter", function() {
			for( var e = this.childNodes, t = -1; ++t < e.length; ) {
				if( 1 == e[t].nodeType && "ul" == e[t].nodeName.toLowerCase() ) {
					var r, n = performance.now()
					,   a = e[t]
					,   l = a.style;
					l.display = "table";
					var h = a.offsetHeight
					,   s = a.offsetWidth
					,   f = 0;
					l.display = "block", l.overflow = "hidden", l.opacity = 1, l.height = 0, l.width = 0, r = w.requestAnimationFrame(function e(t) {
						var i = (t - n) / 300;
						if( 0 === f && (v = m = r), f = 1, m !== v ) {
							var o = d.querySelectorAll("#nav li ul");
							for (t = 0, i = o.length; t < i; ++t) o[t].removeAttribute("style");
							w.cancelAnimationFrame(r)
						}
						i < 1 ? (l.opacity = u.max(i, 0), l.height = u.min(i * h, h) + "px", l.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : (a.removeAttribute("style"), l.display = "table")
					})
				}
			}
		});
		for( var o = 0, r = e.length; o < r; ++o ) e[o].addEventListener("mouseleave", function() {
			for( var e = this.childNodes, t = -1; ++t < e.length; ) {
				if( 1 == e[t].nodeType && "ul" == e[t].nodeName.toLowerCase() ) {
					var o, r = performance.now()
					,   n = e[t]
					,   a = n.style
					,   l = n.offsetHeight
					,   i = n.offsetWidth
					,   h = 1
					,   s = d.querySelectorAll("#gnavi div > ul > li");
					for( var f = (t = 0, s.length); t < f; ++t ) {
						n.parentNode == s[t] && (h = 0);
					}
					a.height = l + "px", a.width = i + "px", a.display = "block", a.overflow = "hidden", o = w.requestAnimationFrame(function e(t) {
						var i = (t - r) / 250;
						0 === h && (v = o), i < (h = 1) ? (a.height = u.max((1 - i) * l, 0) + "px", w.requestAnimationFrame(e)) : n.removeAttribute("style")
					})
				}
			}
		})
	}();
} catch (e) {
	console.error("global.nav.error: " + e.message)
}

try{ /* mibile.nav */

SCRIPT;

			if( $luxe['global_navi_mobile_type'] !== 'luxury' ) {
				$ret .= <<< SCRIPT
	// モバイルメニュー (メニューオンリー版)
	//var nav = $('#nav')
	//,   men = $('.menu ul')
	var mob = d.querySelector(".mobile-nav")
	,   navid = d.getElementById("nav");

	mob.onclick = function() {
		var scltop = 0;

		if( d.getElementById("bwrap") !== null ) {
			remove_ovlay();
		} scltop = w.pageYOffset;

		/*
		$('body').append(
			'<div id=\"ovlay\">' +
			'<div id=\"bwrap\"></div>' +
			'<div id=\"close\"><i class=\"fa fas fa-times\"></i></div>' +
			'<div id=\"layer\" style=\"\"><div id=\"nav\">' + ( navid !== null ? navid.innerHTML : '' ) + '</div>' +
			'</div>' );
		*/
		var ctop = 0;
		if( d.getElementById("wpadminbar") !== null ) {
			ctop = ctop + d.getElementById("wpadminbar").offsetHeight;
		}

		var l = d.createElement("div");
		l.id = "ovlay";
		l.innerHTML =
			'<div id=\"bwrap\"></div>' +
			'<div id=\"close\" style="top:' + ( ctop + 10 ) + 'px"><i class=\"fa fas fa-times\"></i></div>' +
			'<div id=\"layer\" style=\"\"><div id=\"nav\">' + ( navid !== null ? navid.innerHTML : '' ) + '</div>' +
			'</div>';
		;
		d.body.appendChild( l );

		var s = d.createElement("style");
		s.id = "ovlay-style";
		s.innerText =
			'#bwrap{height:' + d.body.clientHeight + 'px;{$brap_rgba};}' +
			'#layer{top:' + ( scltop + ctop ) + 'px;}'

SCRIPT;
				if( $luxe['global_navi_open_close'] === 'individual' ) {
					$ret .= <<< SCRIPT
		+
		'#layer li[class*=\"children\"] li a::before{content:\"-\";}' +
		'#layer li[class*=\"children\"] a::before,' +
		'#layer li li[class*=\"children\"] > a::before{content:\"\\{$fa_plus_square}\";font-weight:400}' +
		'#layer li li[class*=\"children\"] li a::before{content:\"\\\\0b7\";}'

SCRIPT;
				}
				else {
					$ret .= <<< SCRIPT
		+
		'#layer li[class*=\"children\"] a{padding-left:20px;}' +
		'#layer li[class*=\"children\"] ul{display:block}' +
		'#layer li ul > li[class*=\"children\"] > a{padding-left:35px;}'

SCRIPT;
				}

				$ret .= <<< SCRIPT
		;
		d.getElementsByTagName("head")[0].appendChild( s );

		//$('#layer ul').show();
		luxeShow( d.querySelector("#layer ul") );
		//$('#layer .mobile-nav').hide();
		luxeHide( d.querySelector("#layer .mobile-nav") );

SCRIPT;
				if( $luxe['global_navi_open_close'] === 'individual' ) {
					$ret .= <<< SCRIPT

		//$('#layer ul ul').hide();
		luxeHide( d.querySelector("#layer ul ul") );
		//$('#layer ul li[class*=\"children\"] > a').click( function(e) {
		//d.querySelectorAll('#layer ul li[class*=\"children\"] > a').forEach(function(e) {
		var layer = d.querySelectorAll('#layer ul li[class*=\"children\"] > a');
		Array.prototype.forEach.call( layer, function(e) {
			e.addEventListener("click", function(F) {
				var m, g = luxeGetStyleValue
				,   t = this.parentNode
				,   a = t.getAttribute("class").match(/item-[0-9]+/)
				,   n = performance.now()
				,   u = Math;

				for( var e = t.childNodes, i = -1; ++i < e.length; ) {
					if( 1 == e[i].nodeType && "ul" == e[i].nodeName.toLowerCase() ) {
						m = e[i]
					}
				}

				var q = m.style

				if( g(m, "display") === "none" ) {
					m.style.display = "block";
					var h = m.offsetHeight
					,   s = m.offsetWidth;
					q.display = "block", q.opacity = 1, q.height = 0, q.width = 0, w.requestAnimationFrame(function e(t) {
						var i = (t - n) / 300;
						i < 1 ? (q.opacity = u.max(i, 0), q.height = u.min(i * h, h) + "px", q.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : (m.removeAttribute("style"), q.display = "block")
					})
				} else {
					var h = m.offsetHeight
					,   s = m.offsetWidth;
					w.requestAnimationFrame(function e(t) {
						var i = 1 - ( (t - n) / 300 );
						i > 0 ? (q.height = u.min(i * h, h) + "px", q.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : m.removeAttribute("style")
					})
				}

				if( d.getElementById(a + "-minus") !== null ) {
					var b = d.getElementById(a + "-minus");
					b.parentNode.removeChild(b);
				} else {
					var l = d.createElement("div");
					l.id = a + "-minus";
					l.innerHTML =
						'<style>' +
						'#layer li[class$=\"' + a + '\"] > a::before,' +
						'#layer li[class*=\"' + a + ' \"] > a::before,' +
						'#layer li li[class$=\"' + a + '\"] > a::before,' +
						'#layer li li[class*=\"' + a + ' \"] > a::before{content:\"\\{$fa_minus_square}\";}' +
						'</style></div>'
					;
					d.getElementById("ovlay").appendChild( l );
				} F.preventDefault(), F.stopImmediatePropagation();
			});
		});

SCRIPT;
				}
				$ret .= <<< SCRIPT
/*
		$('#layer').animate( {
			'marginTop' : '0'
		}, 500 );

		$('#bwrap, #close').click( function() {
			$('#layer').animate( {
				//'marginTop' : '-' + d.documentElement.clientHeight + 'px'
				'marginTop' : '-' + d.getElementById('layer').offsetHeight + 'px'
			}, 500);

			setTimeout(function(){
				remove_ovlay();
			}, 550 );
		});
*/

		var r = performance.now()
		,   s = d.getElementById("layer");

		w.requestAnimationFrame(function e(t) {
			var n = 1 - ( (t - r) / 480 )
			,   a = 1 - s.clientHeight * n;
			s.style.marginTop = a + "px", 0 > n ? s.style.marginTop = 0 : w.requestAnimationFrame(e)
		});

		var layerClose = function(e) {
			r = performance.now();
			w.requestAnimationFrame(function e(t) {
				var n = (t - r) / 480
				,   c = s.clientHeight
				,   a = 1 - c * n;
				s.style.marginTop = a + "px", 1 < n ? s.style.marginTop = 1 - c + "px" : w.requestAnimationFrame(e)
			});

			setTimeout(function() {
				remove_ovlay();
			}, 550 );
		};
		d.getElementById("bwrap").onclick = layerClose;
		d.getElementById("close").onclick = layerClose;
	}, mob.style.cursor = "pointer";

SCRIPT;
			}
			else {
				$ret .= <<< SCRIPT

	var luxeScrollOff = function( e ){
		e.preventDefault();
	}, no_scroll = function() {  // スクロール禁止
		// PC
		var sclev = "onwheel" in d ? "wheel" : "onmousewheel" in d ? "mousewheel" : "DOMMouseScroll";
		d.addEventListener( sclev, luxeScrollOff, false );
		// スマホ
		d.addEventListener( "touchmove", luxeScrollOff, {passive: false} );
	}, go_scroll =  function() { // スクロール復活 
		// PC
		var sclev = "onwheel" in d ? "wheel" : "onmousewheel" in d ? "mousewheel" : "DOMMouseScroll";
		d.removeEventListener( sclev, luxeScrollOff, false );
		// スマホ
		d.removeEventListener( "touchmove", luxeScrollOff, {passive: false} );
	}

	// モバイルメニュー ( Luxury 版 )
	//var nav = $('#nav')
	var mom = d.querySelector(".mob-menu")
	,   mos = d.querySelector(".mob-side")
	,   prv = d.querySelector(".mob-prev")
	,   nxt = d.querySelector(".mob-next")
	,   srh = d.querySelector(".mob-search")
	//,   men = $('.menu ul')
	,   mob = d.querySelector(".mobile-nav")
	,   prvid = d.getElementById("data-prev")
	,   nxtid = d.getElementById("data-next")
	,   navid = d.getElementById("nav")
	,   sdbid = d.getElementById("sidebar")
	,   mobmn = 'style=\"margin-top:-' + d.documentElement.clientHeight + 'px\"><div id=\"nav\">' + ( navid !== null ? navid.innerHTML : '' ) + '</div>' +
			'<style>#layer #nav{top:0;}#layer #nav-bottom{border:0}</style>'
	,   sdbar = ''
	,   sform = '>';

	if( sdbid !== null ) {
		sdbar = 'style=\"height:' + sdbid.offsetHeight + 'px;width:1px\">' +
			'<style>#side,div[id*=\"side-\"]{margin:0;padding:0}</style>'
	}

	// モバイルメニューの動き
	if( mom !== null ) {
		mom.onclick = function(){
			mobile_menu( "mom", mobmn );
		}, mom.style.cursor = "pointer";
	}

	if( mos !== null ) {
		mos.onclick = function(){
			mobile_menu( "mos", sdbar );
		}, mos.style.cursor = "pointer";
	}

	if( srh !== null ) {
		srh.onclick = function(){
			mobile_menu( "srh", sform );
		}, srh.style.cursor = "pointer";
	}

	if( prv !== null ) {
		if( prvid !== null ) {
			prv.onclick = function(){
				location.href = prvid.getAttribute("data-prev");
			}, prv.style.cursor = "pointer";
		} else {
			prv.style.opacity = ".4", prv.style.cursor = "not-allowed";
		}
	}
	if( nxt !== null ) {
		if( nxtid !== null ) {
			nxt.onclick = function(){
				location.href = nxtid.getAttribute("data-next");
			}, nxt.style.cursor = "pointer";
		} else {
			nxt.style.opacity = ".4", nxt.style.cursor = "not-allowed";
		}
	} function mobile_menu( cpoint, layer ) {
		//if( typeof layerName === "undefined" ) var layerName = "layer";
		if( d.getElementById("bwrap") !== null ) remove_ovlay();

		var scltop = w.pageYOffset;

		/*
		$('body').append(
			'<div id=\"ovlay\">' +
			'<div id=\"bwrap\"></div>' +
			'<div id=\"close\"><i class=\"fa fas fa-times\"></i></div>' +
			'<div id=\"layer\" ' + layer + '</div>' +
			'</div>' );
		*/

		var ctop = 0;
		if( d.getElementById("wpadminbar") !== null ) {
			ctop = ctop + d.getElementById("wpadminbar").offsetHeight;
		}

		var l = d.createElement("div");
		l.id = "ovlay";
		l.innerHTML =
			'<div id=\"bwrap\"></div>' +
			'<div id=\"close\" style="top:' + ( ctop + 10 ) + 'px"><i class=\"fa fas fa-times\"></i></div>' +
			'<div id=\"layer\" ' + layer + '</div>' +
			'</div>';
		;
		d.body.appendChild( l );

		var s = d.createElement("style");
		s.id = "ovlay-style";
		s.innerText =
			'#bwrap{height:' + d.body.clientHeight + 'px;{$brap_rgba};}' +
			'#layer{top:' + ( scltop + ctop ) + 'px;}'

SCRIPT;
				if( $luxe['global_navi_open_close'] === 'individual' ) {
					$ret .= <<< SCRIPT
		+
		'#layer li[class*=\"children\"] li a::before{content:\"-\";}' +
		'#layer li[class*=\"children\"] a::before,' +
		'#layer li li[class*=\"children\"] > a::before{content:\"\\{$fa_plus_square}\";font-weight:400}' +
		'#layer li li[class*=\"children\"] li a::before{content:\"\\\\0b7\";}'

SCRIPT;
				}
				else {
					$ret .= <<< SCRIPT
		+
		'#layer li[class*=\"children\"] a{padding-left:20px;}' +
		'#layer li[class*=\"children\"] ul{display:block}' +
		'#layer li ul > li[class*=\"children\"] > a{padding-left:35px;}'

SCRIPT;
				}

				$ret .= <<< SCRIPT
		;
		d.getElementsByTagName("head")[0].appendChild( s );

		//$('#layer ul').show();
		luxeShow( d.querySelector("#layer ul") );
		//$('#layer .mobile-nav').hide();
		luxeHide( d.querySelector("#layer .mobile-nav") );

		if( cpoint === "mos") {
			var top = w.pageYOffset;
			if( d.getElementById("wpadminbar") !== null ) {
				top = top + d.getElementById("wpadminbar").offsetHeight + "px";
			} else {
				top = top + "px";
			}

			var winwh  = d.documentElement.clientWidth
			,   width  = {$side_1_width}
			,   sdbar  = sdbid.style;

			if( width > winwh ) width = winwh - 6;

			sdbar.maxWidth = "98vw";
			sdbar.width    = width + "px";
			sdbar.position = "absolute";
			sdbar.right    =  winwh + "px";
			sdbar.top      = top;
			sdbar.zIndex   = "1100";
			sdbar.overflow = "hidden";
			sdbar.background = "#fff";
			sdbar.padding    = "1px";
			sdbar.border     = "3px solid #ddd";
			sdbar.borderRadius = "5px";
		}

SCRIPT;
			if( $luxe['global_navi_open_close'] === 'individual' ) {
				$ret .= <<< SCRIPT
		luxeHide( d.querySelector("#layer ul ul") );
		var layer = d.querySelectorAll('#layer ul li[class*=\"children\"] > a');
		Array.prototype.forEach.call( layer, function(e) {
			e.addEventListener("click", function(F) {
				var m, g = luxeGetStyleValue
				,   t = this.parentNode
				,   a = t.getAttribute("class").match(/item-[0-9]+/)
				,   n = performance.now()
				,   u = Math;

				for( var e = t.childNodes, i = -1; ++i < e.length; ) {
					if( 1 == e[i].nodeType && "ul" == e[i].nodeName.toLowerCase() ) {
						m = e[i]
					}
				}

				var q = m.style

				if( g(m, "display") === "none" ) {
					m.style.display = "block";
					var h = m.offsetHeight
					,   s = m.offsetWidth;
					q.display = "block", q.opacity = 1, q.height = 0, q.width = 0, w.requestAnimationFrame(function e(t) {
						var i = (t - n) / 300;
						i < 1 ? (q.opacity = u.max(i, 0), q.height = u.min(i * h, h) + "px", q.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : (m.removeAttribute("style"), q.display = "block")
					})
				} else {
					var h = m.offsetHeight
					,   s = m.offsetWidth;
					w.requestAnimationFrame(function e(t) {
						var i = 1 - ( (t - n) / 300 );
						i > 0 ? (q.height = u.min(i * h, h) + "px", q.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : m.removeAttribute("style")
					})
				}

				if( d.getElementById(a + "-minus") !== null ) {
					var b = d.getElementById(a + "-minus");
					b.parentNode.removeChild(b);
				} else {
					var l = d.createElement("div");
					l.id = a + "-minus";
					l.innerHTML =
						'<style>' +
						'#layer li[class$=\"' + a + '\"] > a::before,' +
						'#layer li[class*=\"' + a + ' \"] > a::before,' +
						'#layer li li[class$=\"' + a + '\"] > a::before,' +
						'#layer li li[class*=\"' + a + ' \"] > a::before{content:\"\\{$fa_minus_square}\";}' +
						'</style>'
					;
					d.getElementById("ovlay").appendChild( l );
				} F.preventDefault(), F.stopImmediatePropagation();
			});
		});

SCRIPT;
			}
			$ret .= <<< SCRIPT
		var s, r = performance.now()
		,   c = d.documentElement.clientWidth;

		if( cpoint === "mom" ) {
			s = d.getElementById("layer");
			w.requestAnimationFrame(function e(t) {
				var n = 1 - ( (t - r) / 480 )
				,   a = 1 - s.clientHeight * n;
				s.style.marginTop = a + "px", 0 > n ? s.style.marginTop = 0 : w.requestAnimationFrame(e)
			});
		} else if( cpoint === "mos" ) {
			//s = d.getElementById("sidebar");
			d.getElementById("primary").style.animation = "none";
			w.requestAnimationFrame(function e(t) {
				var n = 1 - ( (t - r) / 500 )
				,   a = c * n;
				sdbid.style.right = a + "px", 0 > n ? sdbid.style.right = "3px" : w.requestAnimationFrame(e)
			});
		} else if( cpoint === "srh" ) {
			s = d.getElementById("sform");
			w.scrollTo(0, 0);
			no_scroll();
			d.documentElement.style.overflow = "hidden";
			s.style.top = "-100%";
			luxeShow( s );
			w.requestAnimationFrame(function e(t) {
				var n = (t - r) / 250
				,   a = (ctop + 100) * n;
				s.style.top = a + "px", 1 < n ? s.style.top = ctop + 100 + "px" : w.requestAnimationFrame(e)
			});

			setTimeout(function() {
				var a = d.querySelector("#sform .search-field");
				a.focus(), a.click();
			}, 200 );
		}

		var layerClose = function(e) {
			r = performance.now();
			if( cpoint === "mom") {
				//s = d.getElementById("layer");
				w.requestAnimationFrame(function e(t) {
					var n = (t - r) / 480
					,   v = s.scrollHeight
					,   a = 1 - v * n;
					s.style.marginTop = a + "px", 1 < n ? s.style.marginTop = 1 - v + "px" : w.requestAnimationFrame(e)
				});
			} else if( cpoint === "mos") {
				d.documentElement.style.overflowX = "hidden";
				w.requestAnimationFrame(function e(t) {
					var n = (t - r) / 500
					,   a = 1 - n * c;
					sdbid.style.marginRight = a + "px", 1 < n ? (sdbid.style.marginRight = 1 - c + "px", sdbid.style.width = "100%") : w.requestAnimationFrame(e)
				});
			} else if( cpoint === "srh") {
				//s = d.getElementById("sform");
				w.requestAnimationFrame(function e(t) {
					var n = (t - r) / 450
					,   a = n * d.documentElement.clientHeight + ctop + 100;
					s.style.top = a + "px", 1 < n ? s.style.top = "100%" : w.requestAnimationFrame(e)
				});
			}

			setTimeout(function() {
				if( cpoint === "srh" ) {
					go_scroll();
					w.scrollTo( 0, scltop );
				}
				remove_ovlay();
			}, 550 );
		};
		d.getElementById("bwrap").onclick = layerClose;
		d.getElementById("close").onclick = layerClose;
	}

SCRIPT;
			}
			$ret .= "} catch(e) { console.error( 'mibile.nav.error: ' + e.message ); }\n";
		}

		$site = array();
		$wt = $ca->thk_id();
		$wt_selecter  = $wt;
		$wta_selecter = "#" . $wt . " a";
		$foot_prefix  = '#wp-';
		$wt_array  = $ca->thk_hex_array();
		$wt_txt  = array();
		$ins_func = $ca->ins_luxe();
		$csstext_array = $ca->csstext_imp();
		$site_array = $ca->thk_site_name();

		$css_txt  = 'cssText';
		$wt_txt[] = THK_COPY;

		if( strlen( $wt ) === 3 ) {
			if( $wt[2] !== 'k' )     $broken = true;
			elseif( $wt[1] !== 'h' ) $broken = true;
			elseif( $wt[0] !== 't' ) $broken = true;
		}
		else {
			$broken = true;
		}

		foreach( $wt_array as $val ) $wt_txt[] = $ca->hex_2_bin( $val );
		if(
			( is_array( $wt_txt ) && count( $wt_txt ) >= 5  ) && (
				stripos( $wt_txt[0], 'http' )  === false ||
				stripos( $wt_txt[1], 'style' ) === false ||
				stripos( $wt_txt[2], 'luxeritas' ) === false
			)
		) $broken = true;

		foreach( $site_array as $val ) $site[] = $ca->hex_2_bin( $val );
		if( is_array( $site ) && count( $site ) >= 4 && stripos( $site[0], 'luxeritas' ) === false ) {
			$broken = true;
		}

		foreach( $csstext_array as $key => $val ) {
			$csstext[] = $ca->hex_2_bin( $val );
			if( stripos( $csstext[$key], '!;' ) === false ) {
				$broken = true;
			}
			else {
				$csstext[$key] = str_replace( '!;', $imp_close, $csstext[$key] );
			}
		}

		$ret .= <<< SCRIPT
try{
	var cint = false
	,   c = thk_get_yuv()
	,   i = '{$csstext[0]}'
	,   b = '{$csstext[1]}'
	,   l = '{$csstext[2]}color:' + c[0] + '{$imp_close}'
	,   s = d.createElement('style');

	!function() {
		var h = w.location.href
		,   x  = d.getElementById("{$wt_selecter}")
		,   a = d.querySelector("{$wta_selecter}")
		,   g = d.getElementById("{$site[2]}")
		,   t = ( g !== null ? g.children : '' )
		,   f = false
		,   k = false
		,   j = 0;

		for( j = 0; j < t.length; j++ ){
			if( t[j].tagName.toLowerCase() !== '{$site[2]}' ) t[j].parentNode.removeChild(t[j]);
		} g = d.getElementsByTagName("{$site[2]}"); t = ( typeof g[0] !== "undefined" ? g[0].children : '' );
		for( j = 0; j < t.length; j++ ){
			if( t[j].id.toLowerCase() !== '{$site[4]}' && t[j].id.toLowerCase() !== '{$site[3]}' ) t[j].parentNode.removeChild(t[j]);
		} t = d.body.children;
		for( j = 0; j < t.length; j++ ) {
			if( t[j].id.toLowerCase() === '{$site[2]}' ) k = true; continue;
		} if( k === true ) {
			for( j = 0; j < t.length; j++ ) {
				if( t[j].id.toLowerCase() === '{$site[2]}' ) {
					f = true; continue;
				} if( f === true ) {
					if( '#' + t[j].id.toLowerCase() !== '{$foot_prefix}{$site[2]}' ) t[j].parentNode.removeChild(t[j]);
					if( '#' + t[j].id.toLowerCase() === '{$foot_prefix}{$site[2]}' ) break;
				}
			}
		} else {
			for( j = 0; j < t.length; j++ ) {
				if( t[j].className.toLowerCase() === 'container' ) {
					f = true; continue;
				} if( f === true ) {
					if( '#' + t[j].id.toLowerCase() !== '{$foot_prefix}{$site[2]}' ) t[j].parentNode.removeChild(t[j]);
					if( '#' + t[j].id.toLowerCase() === '{$foot_prefix}{$site[2]}' ) break;
				}
			}
		} var id = "{$wt}";
		setInterval( function() {
			var n = luxeGetStyleValue;
			if( document.getElementById(id) !== null ) {
				var luxhtml = document.getElementById(id).innerHTML;
				if( luxhtml.indexOf('{$site[0]}') != -1 && luxhtml.indexOf('{$site[1]}') != -1 ) {
					if( document.getElementById(id).parentNode.getAttribute('id') === '{$site[3]}' ) {
						//x.css({'{$css_txt}': b + l });
						//a.css({'{$css_txt}': i + l });
						x.style.{$css_txt} = b + l;
						a.style.{$css_txt} = i + l;
					} else {
						{$ins_func};
					}
				} else {
					{$ins_func};
				}
			} else {
				{$ins_func};
			} if( d.getElementById('{$site[2]}') === null || d.getElementsByTagName('{$site[2]}').length <= 0 || n(d.getElementById("{$site[2]}"), "display") == "none" || n(d.querySelector("{$site[2]}"), "display") == "none" ) {
				{$ins_func};
			}
		}, 1000 );
	 }(); function {$ins_func} {
		if( cint === false ) {
			var t = '{$wt_txt[1]}'
			,   a = d.createElement('div');
			if( d.getElementById('{$site[3]}') !== null ) {
				var s = d.getElementById('{$site[3]}')
				var r = s.innerHTML;
				t = t.replace('><', '>' + r + '<');
				//d.getElementById('{$site[3]}').remove();
				//$('#{$site[3]}').remove();
				s.parentNode.removeChild(s);
			} a.innerHTML = t + b  + l + '{$wt_txt[2]}{$wt_txt[0]}{$wt_txt[3]}' + i  + l + '{$wt_txt[4]}'; d.body.appendChild( a );
			cint = true;
		}
	} function thk_dummy(){}

	function thk_get_yuv() {
		var yuv = 255
		,   k = null
		,   e = ""
		,   i = "rgba(0, 0, 0, 0)"
		,   h = "transparent"
		,   g = "none"
		,   j = "background-color"
		,   n = luxeGetStyleValue
		,   m = n( d.body, j )
		,   c = n( d.getElementById("{$site[2]}"), j )
		,   a = n( d.getElementById("{$site[3]}"), j )
		,   b = n( d.getElementsByTagName("{$site[2]}")[0], j);

		if (a != i && a != h && a != g) {
			k = a
		} else {
			if (b != i && b != h && b != g) {
				k = b
			} else {
				if (c != i && c != h && c != g) {
					k = c
				} else {
					k = m
				}
			}
		}
		if( k != i && k != h && k != g ) {
			if( typeof(k) != "undefined" ) {
				e = k.split(",")
			}
		} else {
			e = ["255", "255", "255", "0"]
		}
		if( e.length >= 3 ) {
			e[0] = e[0].replace(/rgba\(/g, "").replace(/rgb\(/g, "");
			e[1] = e[1].replace(/ /g, "");
			e[2] = e[2].replace(/\)/g, "").replace(/ /g, "");
			yuv = 0.299 * e[0] + 0.587 * e[1] + 0.114 * e[2]
		}
		return yuv >= 128 ? ['black', 'white'] : ['white', 'black']
	};
	s.id = '{$wt}c';
	s.innerText = '{$imp}color:' + c[0] + '{$imp_close}}';
	document.getElementsByTagName('head')[0].appendChild( s );
	setInterval( function() {
		if( document.getElementById(s.id) === null ) {
			document.getElementsByTagName('head')[0].appendChild( s );
		}
	}, 1000 );
} catch(e) {
	console.error( 'html.body.error: ' + e.message );
	//var c = [], n = d.body; n.parentNode.removeChild( n );
}
};

!function(t) {
	"readyState" in t || (t.readyState = "loading", t.addEventListener("DOMContentLoaded", function e() {
		t.readyState = "interactive", this.removeEventListener("DOMContentLoaded", e, !1)
	}, !1), t.addEventListener("load", function e() {
		t.readyState = "complete", this.removeEventListener("load", e, !1)
	}, !1))
}(document);

var luxeDOMContentLoadedCheck = function(e) {
	"loading" !== document.readyState && "uninitialized" !== document.readyState && typeof luxeDOMContentLoaded == "function" ? (console.log("readyState: " + document.readyState), luxeDOMContentLoaded()) : window.setTimeout(function() {
		luxeDOMContentLoadedCheck(e)
	}, 100)
};

luxeDOMContentLoadedCheck();

SCRIPT;
		if( $broken !== false ) {
			if( $_is['admin'] === true ) {
				return false;
			}
			else {
				wp_die( __( 'This theme is broken.', 'luxeritas' ) );
			}
		}
		return $ret;
	}

	public function create_luxe_various_script() {
		global $luxe, $_is;


		$ret = '';
		//$home = THK_HOME_URL;

		$ret .= <<< SCRIPT
try {
	var jQeryCheck2 = function(e) {
		window.jQuery ? e(jQuery, window, document) : window.setTimeout(function() {
			jQeryCheck2(e)
		}, 100)
	};
	jQeryCheck2( function($, w, d) {
$( function(){

/* "passive" が使えるかどうかを検出 */
var luxePassiveSupported = false;
try {
	window.addEventListener("test", null, Object.defineProperty({}, "passive", { get: function() { luxePassiveSupported = true; } }));
} catch(err) {}

var luxeListenScroll = function( e ) {
	// スクロールイベント登録
	w.addEventListener( 'scroll', e, luxePassiveSupported ? { passive: true } : false );
	w.addEventListener( 'touchmove', e, luxePassiveSupported ? { passive: true } : false );
}, luxeDetachScroll = function( e ) {
	// スクロールイベント解除
	w.removeEventListener( 'scroll', e, luxePassiveSupported ? { passive: true } : false );
	w.removeEventListener( 'touchmove', e, luxePassiveSupported ? { passive: true } : false );
}, luxeGetStyleValue = function(e, t) {
	// Get CSS
	return e && t ? w.getComputedStyle(e).getPropertyValue(t) : null
}, luxeRemoveClass = function(t, s) {
	if( t.classList.contains(s) == true ) t.classList.remove(s);
/*
	for (var e = s.trim().split(/\s+/), i = (t.getAttribute("class") || "").trim().split(/\s+/), l = i.length; l--;) ~e.indexOf(i[l]) && i.splice(l, 1);
	t.setAttribute("class", i.join(" "))
*/
}, luxeAddClass = function(t, s) {
	if( t.classList.contains(s) == false ) t.classList.add(s);
/*
	for (var e, i = s.trim().split(/\s+/), l = (t.getAttribute("class") || "").trim(), r = l.split(/\s+/), a = i.length; a--;) ~r.indexOf(i[a]) && i.splice(a, 1);
	e = i.join(" "), l.length ? e.length ? t.setAttribute("class", l + " " + e) : t.setAttribute("class", l) : t.setAttribute("class", e)
*/
};

SCRIPT;

$ret .= "try{\n"; /* offset.set */
		$ret .= 'var offset = 0;';
		/* スムーズスクロール と 追従スクロール の offset 値 */
		if( isset( $this->_depend['sscroll'] ) || isset( $this->_depend['stickykit'] ) ) {
			if( isset( $luxe['head_band_visible'] ) && isset( $luxe['head_band_fixed'] ) ) {
				// 帯メニュー固定時の高さをプラス
				$ret .= "offset += $('.band').height();";
			}

			if( isset( $luxe['global_navi_sticky'] ) && $luxe['global_navi_sticky'] !== 'none' && $luxe['global_navi_sticky'] !== 'smart' ) {
				// グローバルナビ固定時の高さをプラス
				$ret .= "if( d.getElementById('nav') !== null ) {";
				$ret .= "offset += d.getElementById('nav').offsetHeight;";
				$ret .= "}";
			}
		}
		// アドミンバーの高さをプラス
		$ret .= <<< SCRIPT
if( d.getElementById('wpadminbar') !== null ) {
	offset += d.getElementById('wpadminbar').offsetHeight;
}

SCRIPT;
$ret .= "} catch(e) { console.error( 'offset.set.error: ' + e.message ); }\n";

		/* スムーズスクロール */
		if( isset( $this->_depend['sscroll'] ) ) {
			// Intersection Observer 有効時には使えない
			if( !isset( $luxe['lazyload_thumbs'] ) && !isset( $luxe['lazyload_contents'] ) && !isset( $luxe['lazyload_sidebar'] ) && !isset( $luxe['lazyload_footer'] ) ) {
				/* source & download: https://www.cssscript.com/smooth-scroll-vanilla-javascript/ */
				$ret .= <<< SCRIPT
try{
	//d.querySelectorAll('a[href^="#"]').forEach( function (a) {
	var sms = d.querySelectorAll('a[href^="#"]');
	Array.prototype.forEach.call( sms, function(a) {
		if( a.getAttribute('href') !== "#" ) {
			a.addEventListener('click', function () {
				smoothScroll.scrollTo(this.getAttribute('href'), 500);
			});
		}
	});
} catch(e) { console.error( 'smooth.scroll.error: ' + e.message ); }

SCRIPT;
			}
		}

$ret .= "try{\n"; /* stick.watch */

		/* 追従スクロール */
		if( isset( $this->_depend['stickykit'] ) ) {
			$stick_init_y = 0;
			$stick_init_y = isset( $luxe['global_navi_scroll_up_sticky'] ) ? 0 : 'offset';

			$ret .= <<< SCRIPT
	var stkwch  = false
	,   navid   = d.getElementById("nav")
	,   mob     = d.querySelector(".mobile-nav")
	,   skeep   = $("#side-scroll")
	,   skeepid = d.getElementById("side-scroll")
	,   sHeight = 0;

	function stick_primary( top ) {
		if( skeep.css('max-width') !== '32767px' ) {
			//skeep.stick_in_parent({parent:'#primary',offset_top:top,spacer:0,inner_scrolling:0,recalc_every:1});
			skeep.stick_in_parent({parent:'#primary',offset_top:top,spacer:0,inner_scrolling:0});
		}
	} stick_primary( {$stick_init_y} );

	// 非同期系のブログパーツがあった場合に追従領域がフッターを突き抜けてしまうのでその予防策
	// ＆ 追従領域がコンテンツより下にあった場合にフッターまでスクロールできない現象の対策
	function stick_watch() {
		var i		// setInterval
		,   s		// 現在の #side の高さ
		,   j = 0;	// インターバルのカウンター

		if( d.getElementById("sidebar") !== null ) {
			i = setInterval( function() {
				if( luxeGetStyleValue( skeepid, "max-width") !== "32767px" ) {
					if( d.getElementById("side") !== null ) {
						if( typeof d.getElementById("side").children[0] !== "undefined" ) {
							// #side aside の高さ（こっち優先）
							s = d.getElementById('side').children[0].offsetHeight
						} else {
							// #side の高さ
							s = d.getElementById("side").offsetHeight;
						}
					}

					if( s >= sHeight ) {
						sHeight = s;
						d.getElementById("sidebar").style.minHeight=s + "px";
						stick_primary( {$stick_init_y} );
						//skeep.trigger('sticky_kit:recalc');
					}

					++j;
					if( j >= 100 ) {
						clearInterval( i ); // PC 表示の時に30秒間だけ監視( 300ms * 100 )
					}
				}
			}, 300);
		}
	}

	if( luxeGetStyleValue( skeepid, "max-width" ) !== '32767px' ) {
		stick_watch();
	} var S = false	// リサイズイベント負荷軽減用
	, skprsz = ( "resize", function() {
		if( S === false ) {
			requestAnimationFrame( function() {
				S = false;
				if( d.getElementById('sidebar') !== null ) {
					sHeight = 0;
					if( skeepid !== null ) skeepid.style.minHeight = "";
					if( luxeGetStyleValue( skeepid, "max-width" ) !== "32767px" ) {
						stick_watch();
					} else {
						skeep.trigger("sticky_kit:detach");
					}
				}
			});
			S = true;
		}
	});

	// リサイズイベント登録
	w.addEventListener( "resize", skprsz, false );

SCRIPT;
		}

		/* グローバルナビTOP固定 */
		if(
			isset( $this->_depend['stickykit'] ) && isset( $luxe['global_navi_visible'] ) &&
			isset( $luxe['global_navi_sticky'] ) && $luxe['global_navi_sticky'] !== 'none'
		) {
			$nav_sticky = <<< NAV_STICKY
	top = 0;
	if( d.getElementById('wpadminbar') !== null ) {
		top += d.getElementById('wpadminbar').offsetHeight;
	}

NAV_STICKY;
			if( isset( $luxe['head_band_visible'] ) && isset( $luxe['head_band_fixed'] ) ) {
				// 帯メニュー固定時の高さをプラス
				$nav_sticky .= "top += $('.band').height();";
			}

			$nav_sticky .= <<< NAV_STICKY
	thk_unpin( navid );
	mnav = luxeGetStyleValue(mob, "display");
	e = d.getElementById("nav");
	r = ( e !== null ? e.getBoundingClientRect() : '' );
	y = w.pageYOffset;
	hidfgt = r.top + y;	// #nav のY座標 (この位置からナビ固定)
	navhid  = top - ( e !== null ? e.offsetHeight : 0 ) - 1 // グローバルナビの高さ分マイナス(リサイズイベント用)
	T = false;

NAV_STICKY;

			// グローバルナビを上スクロールの時だけ固定する場合
			if( isset( $luxe['global_navi_scroll_up_sticky'] ) ) {
				$nav_sticky .= <<< NAV_STICKY
	if( skeepid !== null ) skeepid.style.transition = "all .5s ease-in-out";
	hidfgb = hidfgt + ( e !== null ? e.offsetHeight : 0 );	// 上スクロールの時だけ固定する場合は、#nav の bottom 部分を Y座標にする

	if( y > hidfgb ) {
		skeep.trigger("sticky_kit:detach");
		stick_primary( top );
		thk_pin( e, navhid, "" );
		//luxeAddClass(e, "pinf");	// pin first の略。最初の一発目の position:fixed 挿入時に上スクロール判定されるために不自然な動きになるのを防ぐ
	}

	stkeve = ("scroll", function(E) {
		if( T === false ) {
			requestAnimationFrame( function() {
				T = false;

				//E.preventDefault();
				//E.stopPropagation();
				//E.stopImmediatePropagation();
				p = d.querySelector(".pin")
				y = w.pageYOffset;

				var difpos = nowpos - y; // スクロールの上下判定 ( difpos > 0 ) で上、( difpos <= 0 ) で下
				if( difpos === 0 ) difpos = opos;
				nowpos = y;
				opos = difpos;

				navhid = top - e.offsetHeight - 1; // ナビの高さ分マイナス(スクロールイベント用)

				if( y <= hidfgb && difpos <= 0 ) {
					thk_unpin( e );
				} else if( y <= hidfgt && difpos > 0 ) {
					thk_unpin( e );
				} else if( p == null && y > hidfgb ) {
					skeep.trigger("sticky_kit:detach");
					stick_primary( top );
					thk_pin( e, navhid, "" );
					//luxeAddClass(e, "pinf");
				} else if( p != null ) {
					var sdscrl = d.getElementById("side-scroll")
					,   sdstop = sdscrl != null ? v( sdscrl, "top") : void 0;

					if( difpos > 10 ) { // スクロールアップ時にナビ表示
						if( v(e, "top") !== top + "px" ) {
							//if( d.querySelector(".pinf") != null ) luxeRemoveClass(e, "pinf");
							thk_pin( e, top );
							// 追従スクロールの高さ調整
							if( sdscrl != null ) {
								if( sdstop === top + "px" && v( skeepid, "max-width" ) !== "32767px" ) {
									if( skeepid !== null ) skeepid.style.top = offset + "px";
									//skeep.trigger("sticky_kit:recalc");
								}
							}
						}
					} else if( difpos < -10 ) { // スクロールダウンでナビを画面上に隠す
						if( v(e, "top") !== navhid + "px" ) { // !== navhid だとカクッとなるので条件厳しく
							thk_pin( e, navhid );
							// 追従スクロールの高さ調整
							if( sdscrl != null ) {
								if( sdstop !==  top + "px" && v( skeepid, "max-width" ) !== "32767px" ) {
									if( skeepid !== null ) skeepid.style.top = 0;
									//skeep.trigger("sticky_kit:recalc");
								}
							}
						}
					}
				} else if( p == null && y <= hidfgt ) {
					if( v(e, "top") !== navhid + "px" ) {
						if( v( skeepid, "max-width" ) !== "32767px" ) {
							if( skeepid !== null ) skeepid.style.top = 0;
							//skeep.trigger("sticky_kit:recalc");
						}
						thk_pin( e, navhid );
					}
				}
			});
			T = true;
		}
	});

NAV_STICKY;
			}
			// グローバルナビを常に固定する場合
			else {
				$nav_sticky .= <<< NAV_STICKY
	if( y > hidfgt ) {
		thk_pin( e, top, '' );
	}

	T = false;
	stkeve = ( "scroll", function(){
		if( T === false ) {
			requestAnimationFrame( function() {
				T = false;
				//p = $('.pin')[0];
				p = d.querySelector(".pin")

				if( w.pageYOffset <= hidfgt ) {
					thk_unpin( e );
				} else if( p === null ) {
					thk_pin( e, top, '' );
				}
			});
			T = true;
		}
	});

NAV_STICKY;
			}

			$block_if_else = "
				{$nav_sticky};
				if( mnav !== 'none' ) {
					luxeListenScroll( stkeve );
				} else {
					thk_unpin( e );
					luxeDetachScroll( stkeve );
				}
			\n";

			$stick = '';

			if( $luxe['global_navi_sticky'] === 'smart' ) {
				$stick .= $block_if_else;
			} elseif( $luxe['global_navi_sticky'] === 'pc' ) {
				$stick .= str_replace( "!== 'none'", "=== 'none'", $block_if_else );
			} else {
				$stick .= $nav_sticky . 'luxeListenScroll( stkeve );';
			}

			$ret .= <<< SCRIPT
	var e, r, p, y
	,   top = 0
	,   navhid = 0
	,   mnav
	,   hidfgt
	,   hidfgb
	//,   resz = false	// リサイズイベントかどうかの判別
	,   nowpos = 0		// スクロール位置
	,   opos = -1
	/* ,   stktim = null	// スクロールイベント負荷軽減用 */
	/* ,   stkint = 200	// インターバル(PC では少し速く:100、モバイルでは少し遅く:200) */
	,   stkeve
	,   v = luxeGetStyleValue;

	function thk_nav_stick() {
		{$stick}
	}

	thk_nav_stick();

	var R = false;
	w.addEventListener( "resize", function() {
		if( R === false ) {
			requestAnimationFrame( function() {
				R = false;
				//resz = true;
				//thk_nav_stick();
				e.style.width = "";
				//resz = false;
			});
			R = true;
		}
	}, false );

	function thk_pin( o, sp, trs, cls ) {
		var s = o.style;
		if( typeof trs === "undefined" ) var trs = "all .5s ease-in-out";
		if( typeof cls === "undefined" ) var cls = "pin";

		s.transition = trs,
SCRIPT;

		if( isset( $luxe['head_band_fixed'] ) ) {
			$ret .= 's.top = sp - d.getElementById("head-band").offsetHeight + "px",';
		}
		else {
			$ret .= 's.top = sp + "px",';
		}

		$ret .= <<< SCRIPT
		s.position = "fixed",
		s.width = o.clientWidth + 'px'

		luxeAddClass( o, cls );
		//$('body').css('marginTop', d.getElementById('nav').offsetHeight + 'px');
		d.body.style.marginTop = o.offsetHeight + "px";
	} function thk_unpin( o ) {
		/* o.css({ 'transition': '', 'top': sp + '', 'position': '' }); */
		o.removeAttribute("style");
		luxeRemoveClass( o, "pin" );
		d.body.removeAttribute("style");
		//$('body').removeAttr('style');
	}

SCRIPT;
		}

$ret .= "} catch(e) { console.error( 'stick.watch.error: ' + e.message ); }\n";

		if( $luxe['gallery_type'] === 'tosrus' && $_is['customize_preview'] === false ) {
			/* Tosrus */
			$ret .= <<< SCRIPT
try{ /* tosrus */
	$("a[data-rel^=tosrus]").tosrus({
		caption : {
			add : true,
			attributes : ["data-title","title", "alt", "rel"]
		},
		pagination : {
			add : true,
		},
		infinite : true,
		wrapper : {
			onClick: "close"
		}
	});
} catch(e) { console.error( 'tosrus.error: ' + e.message ); }

SCRIPT;
		}

		if( $luxe['gallery_type'] === 'lightcase' && $_is['customize_preview'] === false ) {
			/* Lightcase */
			$ret .= "try{\n"; /* lightcase */
			$ret .= "$('a[data-rel^=lightcase]').lightcase();\n";
			$ret .= "} catch(e) { console.error( 'lightcase.error: ' + e.message ); }\n";
		}

		if( $luxe['gallery_type'] === 'fluidbox' && $_is['customize_preview'] === false ) {
			/* Fluidbox */
			$ret .= "try{\n"; /* fluidbox */
			$ret .= "$(function () {;\n";
			$ret .= "$('.post a[data-fluidbox]').fluidbox();;\n";
			$ret .= "});;\n";
			$ret .= "} catch(e) { console.error( 'fluidbox.error: ' + e.message ); }\n";
		}

		if( isset( $luxe['head_band_search'] ) ) {
			/* 帯メニュー検索ボックスのサイズと色の変更 */
			$ret .= <<< SCRIPT
try { /* head.band.search */
	var subm = d.querySelector("#head-search button[type=submit]")
	,   text = d.querySelector("#head-search input[type=text]")
	,   menu = d.querySelector(".band-menu ul");
	"block" != luxeGetStyleValue(text, "display") && (text.onclick = function() {
		subm.style.color = "#888",
		menu.style.right = "210px",
		text.style.width = "200px",
		text.style.color = "#000",
		text.style.backgroundColor = "rgba(255, 255, 255, 1.0)",
		text.setAttribute("placeholder", "")
	}, text.onblur = function() {
		subm.removeAttribute("style"),
		menu.removeAttribute("style"),
		text.removeAttribute("style"),
		text.setAttribute("placeholder", "Search ...")
	})
} catch (e) {
	console.error("head.band.search.error: " + e.message)
}
SCRIPT;
		}

		if( isset( $luxe['autocomplete'] ) ) {
			/* 検索ボックスのオートコンプリート (Google Autocomplete) */
			$ret .= <<< SCRIPT
		!function() {
			try{ /* autocomplete */
			$('.search-field, .head-search-field').autocomplete({
				source: function(request, response){
					$.ajax({
						url: "//www.google.com/complete/search",
						data: {
							hl: 'ja',
							ie: 'utf_8',
							oe: 'utf_8',
							client: 'firefox', // For XML: use toolbar, For JSON: use firefox, For JSONP: use firefox
							q: request.term
						},
						dataType: "jsonp",
						type: "GET",
						success: function(data) {
							response(data[1]);
						}
					});
				},
				delay: 300
			});
			} catch(e) { console.error( 'autocomplete.error: ' + e.message ); }
		}();

SCRIPT;
		}

		if( isset( $this->_depend['autosize'] ) ) {
			/* コメント欄の textarea 自動伸縮 */
			$ret .= "try{\n"; /* comment.autosize */
			$ret .= "autosize($('textarea#comment'));\n";
			$ret .= "} catch(e) { console.error( 'comment.autosize.error: ' + e.message ); }\n";
		}

		$ret .= <<< SCRIPT
});
});
} catch (e) {
	console.error("jquery.check2.error: " + e.message)
};

/* IE8以下、Firefox2 以下で getElementsByClassName 使えない時用 */
/*
if (typeof(document.getElementsByClassName) == "undefined") {
	document.getElementsByClassName = function(o) {
		var q = new Array(),
			p = document;
		if (p.all) {
			var m = p.all
		} else {
			var m = p.getElementsByTagName("*")
		}
		for (var l = j = 0, k = m.length; l < k; l++) {
			var n = m[l].className.split(/\s/);
			for (var i = n.length - 1; i >= 0; i--) {
				if (n[i] === o) {
					q[j] = m[l];
					j++;
					break
				}
			}
		}
		return q
	}
};
*/

SCRIPT;
		return $ret;
	}

	/*
	------------------------------------
	 SNS のカウント数読み込み
	------------------------------------ */
	public function create_sns_count_script() {
		global $luxe;
		$ret = '';
		$ajaxurl = admin_url( 'admin-ajax.php' );

		$ret .= <<< SCRIPT
var luxeGetSnsCount = function(h, i, j, k, f) {
	var g = h.location.search;
	jQuery.ajax({
		type: "GET",
		url: "{$ajaxurl}",
		data: {
			action: "thk_sns_real",
			sns: i,
			url: f
		},
		dataType: "text",
		async: true,
		cache: false,
		timeout: 30000,
		success: function(b) {
			if (isFinite(b) && b !== "") {
				jQuery(j).text(String(b).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"))
			} else {
				if (typeof(b) === "string" && b !== "") {
					var a = b.slice(0, 11);
					jQuery(j).text(a)
				} else {
					jQuery(j).text("!")
				}
			}
		},
		error: function() {
			jQuery(j).text("!")
		}
	})
};
!function(b, d) {
	b.addEventListener("load", function c() {
		var m = d.getElementsByClassName("sns-count-true")
		,   g = d.getElementsByClassName("sns-cache-true")
		,   r = d.getElementsByClassName("feed-count-true");

		if (m.length > 0 || g.length > 0) {
			var k = g.length > 0 ? g[0] : m[0]
			,   f = k.getAttribute("data-luxe-permalink")
			,   e = k.getAttribute("data-incomplete")
			,   l = e.split(",");

			if (g.length > 0) {
				var s = jQuery.ajax({
					type: "POST",
					url: "{$ajaxurl}",
					data: {
						action: "thk_sns_cache",
						url: f
					},
					dataType: "text",
					async: true,
					cache: false
				})
				,   h = setInterval(function() {
					if (s && s.readyState > 0) {
						s.abort();
						clearInterval(h)
					}
				}, 500)
			}

			var q = {
				t: ".pinit-count",
				h: ".hatena-count",
				p: ".pocket-count",
				f: ".facebook-count"
			};

			Object.keys(q).forEach(function(i) {
				var j = this[i];
				if (g.length < 1 || l.indexOf(i) >= 0) {
					luxeGetSnsCount(b, i, j, m, f)
				}
			}, q)
		}
		if (r.length > 0 && d.getElementsByClassName("feed-cache-true").length < 1) {
			luxeGetSnsCount(b, "r", ".feedly-count", r, f)
		}
	}, false );
}(window, document);

SCRIPT;
		return $ret;
	}
}
