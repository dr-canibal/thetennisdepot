<?php

/****************************************************************************************
* LiveZilla definitions.files.inc.php
* 
* Copyright 2001-2009 SPAUN Power GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
***************************************************************************************/ 

define("PATH_GROUPS",LIVEZILLA_PATH . "_groups/");
define("PATH_FILTER",LIVEZILLA_PATH . "_filter/");
define("PATH_UPLOADS_INTERNAL",LIVEZILLA_PATH . "uploads/internal/");
define("PATH_UPLOADS_EXTERNAL",LIVEZILLA_PATH . "uploads/external/");
define("PATH_BANNER",LIVEZILLA_PATH . "banner/");
define("PATH_IMAGES",LIVEZILLA_PATH . "images/");
define("PATH_CONFIG",LIVEZILLA_PATH . "_config/");
define("PATH_INTERN_IMAGES",LIVEZILLA_PATH . "intern_images/");
define("PATH_TEMPLATES",LIVEZILLA_PATH . "templates/");
define("PATH_VISITCARDS",LIVEZILLA_PATH . "vcards/");
define("FILE_EXTENSION_FILTER",".lzf");
define("FILE_EXTENSION_VISITCARD",".vcf");
define("FILE_EXTENSION_PASSWORD",".pwd");
define("FILE_EXTENSION_WEBCAM_PICTURE",".wc.jpg");
define("FILE_EXTENSION_PROFILE_PICTURE",".jpg");
define("FILE_EXTENSION_CHANGE_PASSWORD",".cpw");
define("FILE_ACTION_SUCCEEDED",1);
define("FILE_ACTION_ERROR",2);
define("FILE_ACTION_NONE",0);
define("FILE_ERROR_LOG",LIVEZILLA_PATH . "_log/livezilla.log");
define("FILE_SERVER_DISABLED",LIVEZILLA_PATH . "_config/_SERVER_DISABLED_");
define("FILE_SERVER_IDLE",LIVEZILLA_PATH . "_config/_SERVER_IDLE_");
define("FILE_SERVER_GEO_SSPAN",LIVEZILLA_PATH . "_config/_GEO_SPAN_");
define("FILE_SERVER_FILE","server.php");
define("FILE_TYPE_VISITCARD","user_visitcard");
define("FILE_TYPE_USERFILE","user_file");
define("FILE_TYPE_CARRIERLOGO","carrier_logo");
define("FILE_TYPE_USERPICTURE","user_picture");
define("FILE_TYPE_FOLDER","user_folder");
define("FILE_TYPE_ADMIN_BANNER","administrator_banner");
define("FILE_INDEX","index.htm");
define("FILE_CHAT","livezilla.php");
define("FILE_CONFIG",LIVEZILLA_PATH . "_config/config.inc.php");
define("SCHEME_HTTP","http://");
define("SCHEME_HTTP_SECURE","https://");
define("EX_MESSAGE","lzms");
define("EX_MAIL","lzma");
define("EX_INTERN_SESSION","lzis");
define("EX_CHAT_OPEN","lzoc");
define("EX_CHAT_ACTIVE","lzac");
define("EX_CHAT_RATING","lzur");
define("EX_CHAT_INTERN_CLOSED","lzic");
define("EX_CHAT_INTERN_DECLINED","lzid");
define("EX_CHAT_REQUEST","lzcr");
define("EX_CHAT_REQUEST_ACCEPTED","lzra");
define("EX_CHAT_REQUEST_DECLINED","lzrd");
define("EX_CHAT_FORWARDING","lzcf");
define("EX_BROWSER_GUIDE","lzbg");
define("EX_BROWSER_HISTORY","lzbh");
define("EX_STATIC_INFO","lzsi");
define("EX_BROWSER_SESSION","lzvt");
define("EX_CHAT_SESSION","lzec");
define("EX_ALERT","lzal");
define("EX_FILE_UPLOAD_REQUEST","lzar");
define("EX_FILE_UPLOAD","lzfu");
define("EX_PERMISSION","lzpe");
?>