<?php
/*******************************************************************************
 *   Constantes
 *******************************************************************************
 *   GuppY PHP Script - version 6
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 *  v6.00.24 (August 12, 2026) : Date and variable charset
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'constantes.php')) {
    header('location:../index.php');
    die();
}

if (!defined('CHEMIN')) { 
    define('CHEMIN', '');
}
$charset = 'utf-8';
define ('OPCACHEDISABLE', '1'); // 1 pour enable ne pas toucher
define ('OPCACHETEMPOSECONDE', '1'); // Temporisation cache en seconde
define ('OPCACHEINISET', '0'); // 0 pour  Disable, 1 pour Enable, pour desactiver le cache

define ('LGMINIPASSWORDMBR', '8'); // Nombre de caractères minimum du mot de passe
define ('LGMAXPASSWORDMBR', '20'); // Nombre de caractères maximum du mot de passe
define ('LGMINIPSEUDOMBR', '7'); // Nombre de caractères minimum du pseudo
define ('LGMAXPSEUDOMBR', '20'); // Nombre de caractères maximum du pseudo

define('MP_VERSION',      '6.00.24');
define('MP_DATE',         'August 12, 2026');
define('MP_COPYRIGHT',    '2005-2026');

$keyfile = CHEMIN.'data/config/securekey.dtb';
define('CRYPT_KEY',       is_file($keyfile) ? ReadFullDB($keyfile) : 'qO_83;pwY9');

define('CONNECTOR',       '||');
define('USER_COOKIE',     'GuppYUser6');
define('PREF_COOKIE',     'GuppYPref6');
define('ADMP_COOKIE',     'GuppYAdmPref6');
define('CTCT_COOKIE',     'GuppYContact6');
define('CNCT_COOKIE',     'GuppYConnect6');
define('POST_COOKIE',     'GuppYPost6');
define('COOKIE_COOKIE',   'GuppYCookie6');

define('TYP_AGENDA',      'ag');
define('TYP_ART',         'ar');
define('TYP_ARCH',        'arch');
define('TYP_BANNER',      'ba');
define('TYP_BRUB',        'br');
define('TYP_BLOG',        'bl');
define('TYP_BSS',         'bs');
define('TYP_THINK',       'ci');
define('TYP_DNLOAD',      'dn');
define('TYP_FAQ',         'fa');
define('TYP_FREEBOX',     'fb');
define('TYP_FORUM',       'fr');
define('TYP_THREAD',      'frth');
define('TYP_GUESTBK',     'gb');
define('TYP_LINKS',       'li');
define('TYP_MAIL',        'ma');
define('TYP_MENUBOX',     'mb');
define('TYP_DDMENUBOX',   'dm');
define('TYP_MENUICO',     'mi');
define('TYP_NEWS',        'ne');
define('TYP_NWL',         'nl');
define('TYP_OPTMENU',     'om');
define('TYP_OPTDDMENU',   'od');
define('TYP_PHOTO',       'ph');
define('TYP_REACT',       'ra');
define('TYP_REBLOG',      'rb');
define('TYP_RECO',        're');
define('TYP_RSS',         'rs');
define('TYP_RULES',       'ru');
define('TYP_SOCNET',      'sn');

define('DBEXT',           '.dtb');
define('INCEXT',          '.inc');

define('DATAREP',         'data/');
define('REDACREP',        'redac/');
define('INCREP',          'inc/');

define('RSSNEWS',         	DATAREP.'%s-news.xml');
define('RSSART',          	DATAREP.'%s-articles.xml');
define('RSSBLOG',         	DATAREP.'%s-blog.xml');
define('RSSALL',         	DATAREP.'%s-all.xml');

define('CACHEREP',        	CHEMIN.DATAREP.'cache/');
define('CONFIGREP',       	CHEMIN.DATAREP.'config/');
define('USERREP',         	CHEMIN.DATAREP.'users/');
define('DOCSREP',         	CHEMIN.DATAREP.'dbdocs/');
define('TEMPREP',         	CHEMIN.DATAREP.'tmp/');
define('PHOTOREP',        	CHEMIN.'photo/');
define('CRYPTREP',        	CHEMIN.INCREP.'crypt/');
define('SOCIALINCREP',    	CHEMIN.INCREP.'reseau_social/');

define('SAVEREP',         	CHEMIN.'save/');
define('HLPREP',         	CHEMIN.'articles.php?lng=');
define('REMOTEHLP',		  'https://ghc.freeguppy.org/articles.php?lng=');

define('CONFIG',          	CONFIGREP.'config'.INCEXT);
define('MAPSURLS',        	CONFIGREP.'mapsurls'.DBEXT);
define('DBPLUGIN',        	CONFIGREP.'plugin'.DBEXT);
define('DBANTISPAM',      	CONFIGREP.'antispam'.DBEXT);
define('ADMNOTES',        	CONFIGREP.'admnotes.dtb');
define('DBRULES',         	CONFIGREP.TYP_RULES.INCEXT);
define('CHECK_INSTALL',   	CONFIGREP.'chk_install.dtb');
define('FRSIGNS_INI',     	CONFIGREP.'frsigns.ini');
define('FRSIGNS_IMG',     	CHEMIN.'img/frsigns/');
define('LICENCES',        	CONFIGREP.'licences.dtb');

define('MSGREP',          	USERREP.'msg/');
define('COUNTMSGREP',     	USERREP.'counters/');
define('ADMINS',          	USERREP.'admins'.DBEXT);
define('GROUPS',          USERREP.'groups'.DBEXT);
define('MEMBERS',         USERREP.'members'.DBEXT);
define('NEWSLET_LIST',    USERREP.'nwlist'.DBEXT);
define('NEWSLET_WAIT',    USERREP.'nwlist.tmp');
define('GROUPSNWL',       USERREP.'groupsnwl'.DBEXT);
define('MEMBERSNWL',      USERREP.'membersnwl'.DBEXT);
define('GROUPSCOL',       USERREP.'groupscol'.DBEXT);
define('MEMBERSCOL',      USERREP.'memberscol'.DBEXT);

define('ARCHREP',         DOCSREP.'archive/');
define('DBBASE',          DOCSREP.'docs/');
define('DBBASECarousel',  DOCSREP.'carousels/');
define('DBBASESOCIAL',    DOCSREP.'sociaux/');
define('DBIPBASE',        DOCSREP.'ipdocs/');
define('DBCOUNT',         DOCSREP.'counters/');
define('DBINDEX',         DOCSREP.'index/');
define('NEXTID',          DOCSREP.'nextid'.DBEXT);
define('NEXTIDBK',        DOCSREP.'nextidbk'.DBEXT);
define('DOCID',           DOCSREP.'docid'.DBEXT);
define("DOCDATES",        DOCSREP."docdates".DBEXT);
define("CHILDREN",        DOCSREP."children".DBEXT);

define('SOCIALDATA',   	  	DBBASESOCIAL.'socnet'.DBEXT);
define('SOCIALDATACOUNTER', DBBASESOCIAL.'nextid'.DBEXT);

define('SITEMAPS',        CHEMIN.'sitemaps.xml');
define('SM_BLOG',         CHEMIN.'smblog.xml');
define('SM_ART',          CHEMIN.'smart.xml');
define('SM_NEWS',         CHEMIN.'smnews.xml');
define('SM_PHOTO',        CHEMIN.'smphoto.xml');
define('SM_DNLOAD',       CHEMIN.'smdnload.xml');
define('SM_FAQ',          CHEMIN.'smfaq.xml');
define('SM_LINKS',        CHEMIN.'smlinks.xml');
define('SM_GUESTBK',      CHEMIN.'smguestbk.xml');
define('SM_FORUM',        CHEMIN.'smforum.xml');
define('SM_AGENDA',       CHEMIN.'smagenda.xml');
define('SM_URLS',         CHEMIN.'smurls.xml');

define('DBAGENDA',        DBINDEX.TYP_AGENDA.DBEXT);
define('DBART',           DBINDEX.TYP_ART.DBEXT);
define('DBBLOG',          DBINDEX.TYP_BLOG.DBEXT);
define('DBBRUB',          DBINDEX.TYP_BRUB.DBEXT);
define('DBNEWS',          DBINDEX.TYP_NEWS.DBEXT);
define('DBDNLOAD',        DBINDEX.TYP_DNLOAD.DBEXT);
define('DBFAQ',           DBINDEX.TYP_FAQ.DBEXT);
define('DBFORUMCAT',      DBINDEX.TYP_FORUM.'cat'.DBEXT);
define('DBFORUM',         DBINDEX.TYP_FORUM.DBEXT);
define('DBFORUMCOUNTER',  DBINDEX.TYP_FORUM.'count'.DBEXT);
define('DBTHREAD',        DBINDEX.TYP_THREAD.DBEXT);
define('DBLINKS',         DBINDEX.TYP_LINKS.DBEXT);
define('DBPHOTO',         DBINDEX.TYP_PHOTO.DBEXT);
define('DBREACT',         DBINDEX.TYP_REACT.DBEXT);
define('DBREBLOG',        DBINDEX.TYP_REBLOG.DBEXT);
define('DBRSS',           DBINDEX.TYP_RSS.DBEXT);
define('DBBSS',           DBINDEX.TYP_BSS.DBEXT);
define('DBFREEBOX',       DBINDEX.TYP_FREEBOX.DBEXT);
define('DBMENUBOX',       DBINDEX.TYP_MENUBOX.DBEXT);
define('DBOPTMENU',       DBINDEX.TYP_OPTMENU.DBEXT);
define('DBARTMENU',       DBINDEX.TYP_ART.TYP_OPTMENU.DBEXT);
define('DBDDMENUBOX',     DBINDEX.TYP_DDMENUBOX.DBEXT);
define('DBOPTDDMENU',     DBINDEX.TYP_OPTDDMENU.DBEXT);
define('DBMENUICO',       DBINDEX.TYP_MENUICO.DBEXT);
define('DBSOCNET',        DBINDEX.TYP_SOCNET.DBEXT);
define('DBKEYWORDS',      DBINDEX.'kw'.DBEXT);

define('ARCHDBBASE',      ARCHREP.'docs/');
define('ARCHDBIPBASE',    ARCHREP.'ipdocs/');
define('ARCHDBCOUNT',     ARCHREP.'counters/');
define('DOCIDARCH',       ARCHREP.'docid'.DBEXT);
define('DBAGENDAARCHDATE',ARCHREP.TYP_AGENDA.'arch'.DBEXT);
define('DBAGENDAARCH',    ARCHREP.TYP_AGENDA.DBEXT);
define('DBAGENDAARCHDATEEPUR',ARCHREP.TYP_AGENDA.'archepur'.DBEXT);
define('DBBLOGARCHDATE',  ARCHREP.TYP_BLOG.'arch'.DBEXT);
define('DBBLOGARCHDATEEPUR',ARCHREP.TYP_BLOG.'archepur'.DBEXT);
define('DBBLOGARCH',      ARCHREP.TYP_BLOG.DBEXT);
define('DBREBLOGARCH',    ARCHREP.TYP_REBLOG.DBEXT);
define('DBNEWSARCHDATE',  ARCHREP.TYP_NEWS.'arch'.DBEXT);
define('DBNEWSARCH',      ARCHREP.TYP_NEWS.DBEXT);
define('DBNEWSARCHDATEEPUR',  ARCHREP.TYP_NEWS.'archepur'.DBEXT);
define('DBFORUMARCHDATE', ARCHREP.TYP_FORUM.'arch'.DBEXT);
define('DBFORUMARCHDATEEPUR', ARCHREP.TYP_FORUM.'archepur'.DBEXT);
define('DBFORUMARCH',     ARCHREP.TYP_FORUM.DBEXT);
define('DBTHREADARCH',    ARCHREP.TYP_THREAD.DBEXT);

define('STATSREP',        CHEMIN.DATAREP.'stats/'); 

define('CURRSTATSREP',    STATSREP.'current/');
define('OLDSSTATSREP',    STATSREP.'olds/');
define('DBCOUNTER',       STATSREP.'counter'.DBEXT);
define('DBSTATS',         STATSREP.'stats'.DBEXT);
define('DBSTATSBK',       STATSREP.'statsbk'.DBEXT);
define('DBIPSTATS',       STATSREP.'ipstats'.DBEXT);
define('DBLOGDATE',       STATSREP.'log_date'.DBEXT);
define('DBLOGSTATS',      STATSREP.'log_stats'.DBEXT);
define('DBLOGBOOK',       STATSREP.'logbook'.DBEXT);
define('DBCOUNTERLOGBK',  STATSREP.'counterlogbook'.DBEXT);
define('DBLOGBOOKSPAM',   STATSREP.'logbookspam'.DBEXT);

define('DBLOGH',          CURRSTATSREP.'logh'.DBEXT);
define('DBLOGD',          CURRSTATSREP.'logd'.DBEXT);
define('DBLOGM',          CURRSTATSREP.'logm'.DBEXT);
define('DBLOGY',          CURRSTATSREP.'logy'.DBEXT);

define('AOF_TYPE',   0);
define('AOF_ID',     1);
define('AOF_STATUS', 2);

define('HIT_TIME',        1800);
define('POLL_TIME',       3600*24*7);
define('ANTISPAM_COUNT',  100);
define('ANTISPAM_DELAY',  30);
define('NB_NOTES',        100);
define('DBCHK_DEFAULT',   1000);
define('DBCHK_MAX',       5000);

define('BOX_TOP',     'T');
define('BOX_BOTTOM',  'B');
define('BOX_LEFT',    'L');
define('BOX_RIGHT',   'R');
define('BOX_SIDE',    'LR');
define('BOX_CENTER',  'C');
define('BOX_ABOVE',   'A');
define('BOX_UNDER',   'U');
define('BOX_EDITO',   'E');
define('BOX_BLOG',    'B');
define('BOX_LSIDE',   'LS');
define('BOX_RSIDE',   'RS');

define('STATS_NBPAGE',    20);
define('STATS_NBLANGUE',  2);
define('STATS_NBBROWSER', 31);
define('STATS_NBOS',      10);
define('STATS_NBHEURE',   24);
define('STATS_NBJOUR',    31);
define('STATS_NBMOIS',    12);
define('MOTSINTERDITS',  CONFIGREP.'motsinterdits.inc');
define('CONTROLSKIN',  CONFIGREP.'controlskin.inc');
?>
