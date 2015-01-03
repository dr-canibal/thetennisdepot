<html>
<head>
	<META NAME="robots" CONTENT="index,follow">
	<title><!--config_gl_site_name--></title>
	<link rel="stylesheet" type="text/css" href="./templates/style.css">
</head>
<body>
	<div id="lz_chat_floor" align="center">
	<div id="lz_chat_floor_contents">
	<form onSubmit="return false;" style="margin:0px;padding:0px;">
	<table width="95%" cellspacing="2" cellpadding="2">
		<tr>
			<td align="right" valign="top" width="100%">
			<textarea id="lz_chat_text" onkeydown="if(event.keyCode==13){return top.lz_chat_message();}else{top.lz_chat_switch_extern_typing(true);return true;}"></textarea></td>
			<td align="left"><input type="button" id="lz_chat_submit" onclick="return top.lz_chat_message();" name="lz_send_button" value="" title="<!--lang_client_send-->"></td>
		</tr>
	</table>
	</form>
	</div>
	</div><br><br><br><br><br>
	<span id="sound_player"></span>
</body>
</html>
