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

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
<style>
html, body {
	height: 100%;
	margin: 0;
	padding: 0;
}
body {
	padding: 10px;
	background: #fff;
}
/*
#wrap, #around {
	margin: auto;
	height: 100%;
	position: absolute;
	top: auto;
	bottom: auto;
	left: 0;
	right: 0;
}
*/
#wrap {
    z-index: 500000;
    position: absolute;
    overflow: visible;
    top: 5%;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    min-width: 0;
}
#around {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;

	transition: all .2s;
	margin: auto;
	box-sizing: content-box;
	width: 375px;
	height: 667px;
	min-width: 375px;
	min-height: 667px;
max-height: 100%;
    max-width: 100%;

	padding: 40px 20px;
	background: #333;
	border: 5px solid #666;
	border-radius: 20px;


}
iframe {
	box-sizing: border-box;
	width: 100%;
	height: 100%;

	border-top: 3px solid #ccc;
	border-left: 3px solid #ccc;
	border-bottom: 3px solid #fff;
	border-right: 3px solid #fff;
}

iframe::-webkit-scrollbar{
	width: 6px;
}
iframe::-webkit-scrollbar-track{
	background: red;
	border-radius: 30px;
}
iframe::-webkit-scrollbar-thumb{
	border-radius: 30px;
　　	background: #81D4FA;
	box-shadow: inset 0 0 0 2px #fff;
}

</style>
</head>
<body>
<div id="wrap">
<div id="around">
<iframe src="http://themes.local/archives/16741?respond_preview=true" onmousewheel sandbox="allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts"></iframe>
</div>
</div>
</body>
</html>
