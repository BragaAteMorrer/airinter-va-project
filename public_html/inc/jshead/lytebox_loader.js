/**
 * plugin Lytebox for CKEditor (guppy v6.x)
 *
 * Author : jchouix
 * website : http://lebrikabrak.info
 * licence : GPL
 * version : 1.2 (2013/11/17)
 *
 * This plugin is based to the original script written by Sven Zinke (www.webutler.de)
 */

const lytebox_script = document.createElement("script");
lytebox_script.type = "text/javascript";
lytebox_script.src = site3 + "inc/photo/lytebox/lytebox.js";
document.head.appendChild(lytebox_script);

const lytebox_link = document.createElement("link");
lytebox_link.rel = "stylesheet";
lytebox_link.type = "text/css";
lytebox_link.href = site3 + "inc/photo/lytebox/lytebox.css";
document.head.appendChild(lytebox_link);