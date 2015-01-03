<?php
if(isset($_GET["user"]) && isset($_GET["file"]) && isset($_GET["type"]) && file_exists("./uploads/" . $_GET["type"] . "/" . $_GET["user"] . "/" . $_GET["file"]) && strpos($_GET["file"],"..") === false && strpos($_GET["user"],"..") === false && strpos($_GET["type"],"..") === false)
{
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Length: ' . filesize("./uploads/" . $_GET["type"] . "/" . $_GET["user"] . "/" . $_GET["file"]));
	header('Content-Disposition: attachment; filename=' . urlencode(($_GET["type"] == "internal") ? $_GET["file"] : base64_decode($_GET["file"])));
	readfile("./uploads/" . $_GET["type"] . "/" . $_GET["user"] . "/" . $_GET["file"]);
}
else
	header("HTTP/1.0 404 Not Found");
?>