<?php
	/**
	 * This file displays article to user.
	 * $Id$
	 */
	require("funcs.php");
function get_mimetype($name)
{
	$dot = strrchr($name, '.');
	if ($dot == $name)
		return "text/plain; charset=gb2312";
	if (strcasecmp($dot, ".html") == 0 || strcasecmp($dot, ".htm") == 0)
		return "text/html; charset=gb2312";
	if (strcasecmp($dot, ".jpg") == 0 || strcasecmp($dot, ".jpeg") == 0)
		return "image/jpeg";
	if (strcasecmp($dot, ".gif") == 0)
		return "image/gif";
	if (strcasecmp($dot, ".png") == 0)
		return "image/png";
	if (strcasecmp($dot, ".pcx") == 0)
		return "image/pcx";
	if (strcasecmp($dot, ".css") == 0)
		return "text/css";
	if (strcasecmp($dot, ".au") == 0)
		return "audio/basic";
	if (strcasecmp($dot, ".wav") == 0)
		return "audio/wav";
	if (strcasecmp($dot, ".avi") == 0)
		return "video/x-msvideo";
	if (strcasecmp($dot, ".mov") == 0 || strcasecmp($dot, ".qt") == 0)
		return "video/quicktime";
	if (strcasecmp($dot, ".mpeg") == 0 || strcasecmp($dot, ".mpe") == 0)
		return "video/mpeg";
	if (strcasecmp($dot, ".vrml") == 0 || strcasecmp($dot, ".wrl") == 0)
		return "model/vrml";
	if (strcasecmp($dot, ".midi") == 0 || strcasecmp($dot, ".mid") == 0)
		return "audio/midi";
	if (strcasecmp($dot, ".mp3") == 0)
		return "audio/mpeg";
	if (strcasecmp($dot, ".pac") == 0)
		return "application/x-ns-proxy-autoconfig";
	if (strcasecmp($dot, ".txt") == 0)
		return "text/plain; charset=gb2312";
	if (strcasecmp($dot, ".xht") == 0 || strcasecmp($dot, ".xhtml") == 0)
		return "application/xhtml+xml";
	if (strcasecmp($dot, ".xml") == 0)
		return "text/xml";
	return "application/octet-stream";
}

function display_navigation_bar($brdarr, $articles, $num)
{
	global $currentuser;

	$brd_encode = urlencode($brdarr["NAME"]);
	$PAGE_SIZE = 20;
	if ($articles[0]["ID"] != 0)
	{
?>
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&id=<?php echo $articles[0]["ID"]; ?>">上一篇</a>]
<?php
	}
	else
	{
?>
[上一篇]
<?php
	}
	if ($articles[2]["ID"] != 0)
	{
?>
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&id=<?php echo $articles[2]["ID"]; ?>">下一篇</a>]
<?php
	}
	else
	{
?>
[下一篇]
<?php
	}
	if( $articles[1]["ATTACHPOS"] == 0)
	{
?>
[<a href="/cgi-bin/bbs/bbsfwd?board=<?php echo $brd_encode; ?>&file=<?php echo $articles[1]["FILENAME"]; ?>">转寄</a>]
[<a href="/cgi-bin/bbs/bbsccc?board=<?php echo $brd_encode; ?>&file=<?php echo $articles[1]["FILENAME"]; ?>">转贴</a>]
<?php
	}
	if (bbs_can_delete_article($brdarr, $articles[1], $currentuser))
	{
?>
[<a onclick="return confirm('你真的要删除本文吗?')" href="bbsdel.php?board=<?php echo $brd_encode; ?>&file=<?php echo $articles[1]["FILENAME"]; ?>">删除文章</a>]
<?php
	}
	if (bbs_can_edit_article($brdarr, $articles[1], $currentuser))
	{
?>
[<a href="/cgi-bin/bbs/bbsedit?board=<?php echo $brd_encode; ?>&file=<?php echo $articles[1]["FILENAME"]; ?>">修改文章</a>]
<?php
	}
?>
[<a href="/bbsdoc.php?board=<?php echo $brd_encode; ?>&page=<?php echo intval(($num + $PAGE_SIZE - 1) / $PAGE_SIZE); ?>">本讨论区</a>]
[<a href="/bbspst.php?board=<?php echo $brd_encode; ?>&reid=<?php echo $articles[1]["ID"];?>">回文章</a>]
[<a href="/cgi-bin/bbs/bbstfind?board=<?php echo $brd_encode; ?>&title=<?php echo urlencode($articles[1]["TITLE"]); ?>&groupid=<?php echo $articles[1]["GROUPID"];?>">同主题阅读</a>]
[<a href="javascript:history.go(-1)">快速返回</a>]
<?php
}

	if ($loginok != 1)
		html_nologin();
	else
	{
		if (isset($_GET["board"]))
			$board = $_GET["board"];
		else {
			html_init("gb2312");
			html_error_quit("错误的讨论区");
		}
		// 检查用户能否阅读该版
		$brdarr = array();
		$brdnum = bbs_getboard($board, $brdarr);
		if ($brdnum == 0) {
			html_init("gb2312");
			html_error_quit("错误的讨论区");
		}
		$usernum = $currentuser["index"];
		if (bbs_checkreadperm($usernum, $brdnum) == 0) {
			html_init("gb2312");
			html_error_quit("错误的讨论区");
		}
		if (isset($_GET["ftype"])){
			$ftype = $_GET["ftype"];
			if($ftype != $dir_modes["ZHIDING"])
				$ftype = $dir_modes["NORMAL"];
		}
		else
			$ftype = $dir_modes["NORMAL"];
		$total = bbs_countarticles($brdnum, $ftype);
		if ($total <= 0) {
			html_init("gb2312");
			html_error_quit("本讨论区目前没有文章");
		}
		if (isset($_GET["id"]))
			$id = $_GET["id"];
		else {
			html_init("gb2312");
			html_error_quit("错误的文章号");
		}
		settype($id, "integer");
		$articles = array ();
		$num = bbs_get_records_from_id($brdarr["NAME"], $id, 
				$ftype, $articles);
		if ($num == 0)
		{
			html_init("gb2312");
			html_error_quit("错误的文章号.");
		}
		else
		{
			$filename=bbs_get_board_filename($brdarr["NAME"], $articles[1]["FILENAME"]);
			if (bbs_normalboard($board)) {
            			if (cache_header("public",filemtime($filename),300))
                			return;
                	}
//			Header("Cache-control: nocache");
			@$attachpos=$_GET["ap"];//pointer to the size after ATTACHMENT PAD
			if ($attachpos!=0) {
				$file = fopen($filename, "rb");
				fseek($file,$attachpos);
				$attachname='';
				while (1) {
					$char=fgetc($file);
					if (ord($char)==0) break;
					$attachname=$attachname . $char;
				}
				$str=fread($file,4);
				$array=unpack('Nsize',$str);
				$attachsize=$array["size"];
				Header("Content-type: " . get_mimetype($attachname));
				Header("Accept-Ranges: bytes");
				Header("Accept-Length: " . $attachsize);
				Header("Content-Disposition: filename=" . $attachname);
				echo fread($file,$attachsize);
				fclose($file);
				exit;
			} else
			{
				html_init("gb2312");
?>
<body>
<center><p><?php echo $BBS_FULL_NAME; ?> -- 文章阅读 [讨论区: <?php echo $brdarr["NAME"]; ?>]</a></p></center>
<?php
				display_navigation_bar($brdarr, $articles, $num);
?>
<hr class="default" />
<table width="610" border="0">
<tr><td>
<?php
				bbs_printansifile($filename,1,$_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
?>
</td></tr></table>
<hr class="default" />
<?php
				display_navigation_bar($brdarr, $articles, $num);
			}
		}
		if ($currentuser["userid"] != "guest")
			bbs_brcaddread($brdarr["NAME"], $articles[1]["ID"]);
		html_normal_quit();
	}
?>
