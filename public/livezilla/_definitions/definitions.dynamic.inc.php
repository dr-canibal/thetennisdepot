<?php

/****************************************************************************************
* LiveZilla definitions.dynamic.inc.php
* 
* Copyright 2001-2009 SPAUN Power GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
***************************************************************************************/ 

define("TEMPLATE_HTML_EXTERN",PATH_TEMPLATES . "chat.tpl");
define("TEMPLATE_HTML_ADD_SERVER",PATH_TEMPLATES . "addserver.tpl");
define("TEMPLATE_HTML_BROWSER",PATH_TEMPLATES . "browser.tpl");
define("TEMPLATE_HTML_INDEX",PATH_TEMPLATES . "index.tpl");
define("TEMPLATE_HTML_INDEX_ERRORS",PATH_TEMPLATES . "index_errors.tpl");
define("TEMPLATE_SCRIPT_ALERT",PATH_TEMPLATES . "alert.tpl");
define("TEMPLATE_HTML_MESSAGE_EXTERN",PATH_TEMPLATES . "messageexternal.tpl");
define("TEMPLATE_HTML_MESSAGE_INTERN",PATH_TEMPLATES . "messageinternal.tpl");
define("TEMPLATE_HTML_MESSAGE_ADD",PATH_TEMPLATES . "messageadd.tpl");
define("TEMPLATE_HTML_MESSAGE_ADD_ALTERNATE",PATH_TEMPLATES . "messageaddalt.tpl");
define("TEMPLATE_HTML_SUPPORT",PATH_TEMPLATES . "support.tpl");
define("TEMPLATE_HTML_RATEBOX",PATH_TEMPLATES . "ratebox.tpl");
define("TEMPLATE_HTML_MAP",PATH_TEMPLATES . "map.tpl");
define("TEMPLATE_HTML_BUTTON_MESSAGE",PATH_TEMPLATES . "button_message.tpl");
define("TEMPLATE_SCRIPT_EXTERN",PATH_TEMPLATES . "jscript/jsextern.tpl");
define("TEMPLATE_SCRIPT_CHAT",PATH_TEMPLATES . "jscript/jschat.tpl");
define("TEMPLATE_SCRIPT_FRAME",PATH_TEMPLATES . "jscript/jsframes.tpl");
define("TEMPLATE_SCRIPT_TRACK",PATH_TEMPLATES . "jscript/jstrack.tpl");
define("TEMPLATE_SCRIPT_CONNECTOR",PATH_TEMPLATES . "jscript/jsconnector.tpl");
define("TEMPLATE_SCRIPT_BOX",PATH_TEMPLATES . "jscript/jsbox.tpl");
define("TEMPLATE_SCRIPT_DATA",PATH_TEMPLATES . "jscript/jsdata.tpl");
define("TEMPLATE_SCRIPT_GROUPS",PATH_TEMPLATES . "jscript/jsgroups.tpl");
define("TEMPLATE_SCRIPT_GLOBAL",PATH_TEMPLATES . "jscript/jsglobal.tpl");
define("TEMPLATE_SCRIPT_INVITATION",PATH_TEMPLATES . "invitations/classic/invitation.tpl");
define("TEMPLATE_SCRIPT_INVITATION_LOGO",PATH_TEMPLATES . "invitations/classic/invitation_logo.tpl");
define("TEMPLATE_EMAIL_TRANSCRIPT",PATH_TEMPLATES . "emails/transcript.tpl");
define("TEMPLATE_EMAIL_MAIL",PATH_TEMPLATES . "emails/mail.tpl");

define("PATH_DATA",LIVEZILLA_PATH . "_data_".$CONFIG["gl_lzid"]."/");
define("PATH_USERS",LIVEZILLA_PATH . "_intern_".$CONFIG["gl_lzid"]."/");
define("PATH_MESSAGES",LIVEZILLA_PATH . "_messages_".$CONFIG["gl_lzid"]."/");
define("PATH_MESSAGES_CLOSED",LIVEZILLA_PATH . "_messages_".$CONFIG["gl_lzid"]."/closed/");
define("PATH_RATINGS",LIVEZILLA_PATH . "_ratings_".$CONFIG["gl_lzid"]."/");
define("PATH_CHAT_LOGS",LIVEZILLA_PATH . "_chats_".$CONFIG["gl_lzid"]."/");

define("PATH_DATA_INTERNAL" , PATH_DATA . "_internal/");
define("PATH_DATA_EXTERNAL" , PATH_DATA . "_external/");
define("PATH_FRAMES",PATH_TEMPLATES . "frames/");
define("FILE_CARRIERLOGO",PATH_IMAGES . "carrier_logo.gif");
define("FILE_INVITATIONLOGO",PATH_IMAGES . "invitation_logo.gif");
define("FILE_ANS_MESSAGES",PATH_MESSAGES . "ans.ts");
define("FILE_ANS_RATINGS",PATH_RATINGS . "ans.ts");
?>