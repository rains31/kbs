<?php
	/**
	 * This file lists articles to user.
	 * $Id$
	 */
	require("funcs.php");
	if ($loginok != 1)
		html_nologin();
	else
	{
		html_init("gb2312");
		if (isset($_GET["board"]))
			$board = $_GET["board"];
		else
			html_error_quit("错误的讨论区");
		// 检查用户能否阅读该版
		$brdarr = array();
		$brdnum = bbs_getboard($board, $brdarr);
		if ($brdnum == 0)
			html_error_quit("错误的讨论区");
		$usernum = $currentuser["index"];
		if (!bbs_is_bm($brdnum, $usernum))
		{
			html_error_quit("你不是版主");
		}
		$total = bbs_countarticles($brdnum, $dir_modes["NORMAL"]);
		if ($total <= 0)
			html_error_quit("本讨论区目前没有文章");
		if (isset($_GET["start"]))
			$start = $_GET["start"];
		elseif (isset($_POST["start"]))
			$start = $_POST["start"];
		else
			$start = 0;
		settype($start, "integer");
		$artcnt = 20;
		/*
		 * 这里存在一个时间差的问题，可能会导致序号变乱。
		 * 原因在于两次调用 bbs_countarticles() 和 bbs_getarticles()。
		 */
		if ($start == 0 || $start > ($total - $artcnt + 1))
			$start = ($total - $artcnt + 1);
		if ($start < 0)
			$start = 1;
		$articles = bbs_getarticles($board, $start, $artcnt, $dir_modes["NORMAL"]);
		if ($articles == FALSE)
			html_error_quit("读取文章列表失败");
		$bms = explode(" ", trim($brdarr["BM"]));
		$bm_url = "";
		if (strlen($bms[0]) == 0 || $bms[0][0] <= chr(32))
			$bm_url = "诚征版主中";
		else
		{
			if (!ctype_alpha($bms[0][0]))
				$bm_url = $bms[0];
			else
			{
				foreach ($bms as $bm)
				{
					$bm_url .= sprintf("<a href=\"/cgi-bin/bbs/bbsqry?userid=%s\">%s</a> ", $bm, $bm);
				}
				$bm_url = trim($bm_url);
			}
		}
?>
<body>
<nobr/>
<center><?php echo $BBS_FULL_NAME; ?> -- [讨论区: <?php echo $brdarr["NAME"]; ?>] 版主[<?php echo $bm_url; ?>] 文章数[<?php echo $total; ?>] <a href="/cgi-bin/bbs/bbsbrdadd?board=<?php echo $brdarr["NAME"]; ?>">预定本版</a>
<hr class="default"/>
<form name="form1" method="post" action="bbsman">
<table width="613">
<tr><td>序号</td><td>管理</td><td>标记</td><td>作者</td><td>日期</td><td>标题</td></tr>
<?php
		$brd_encode = urlencode($brdarr["NAME"]);
		$i = 0;
		foreach ($articles as $article)
		{
			$title = $article["TITLE"];
			if (strncmp($title, "Re: ", 4) != 0)
				$title = "● " . $title;
			$flags = $article["FLAGS"];
?>
<tr>
<td><?php echo $start + $i; ?></td><td><input style="height:18px" name="box<?php echo $article["FILENAME"]; ?>" type="checkbox"></td>
<td>
<?php
			if ($flags[1] == 'y')
			{
				if ($flags[0] == ' ')
				{
?>
<font face="Webdings" color="#008000">&lt;</font>
<?php
				}
				else
				{
?>
<font color="#008000"><?php echo $flags[0]; ?></font>
<?php
				}
			}
			elseif ($flags[0] == 'N' || $flags[0] == '*')
			{
?>
<font color="#909090"><?php echo $flags[0]; ?></font>
<?php
			}
			else
				echo $flags[0];
?>
</td>
<td><a href="/cgi-bin/bbs/bbsqry?userid=<?php echo $article["OWNER"]; ?>"><?php echo $article["OWNER"]; ?></a></td>
<td><?php echo strftime("%b&nbsp;%e", $article["POSTTIME"]); ?></td>
<td>
<a href="/cgi-bin/bbs/bbscon?board=<?php echo $brd_encode; ?>&file=<?php echo $article["FILENAME"]; ?>&num=<?php echo $start + $i; ?>"><?php echo htmlspecialchars($title); ?></a>
</td>
</tr>
<?php
			$i++;
		}
?>
</table>
<input type="hidden" name="mode" value="">
<input type="hidden" name="board" value="KCNclub">
<input type="button" value="删除" onclick="document.form1.mode.value=1; document.form1.submit();">
<input type="button" value="加M" onclick="document.form1.mode.value=2; document.form1.submit();">
<input type="button" value="加G" onclick="document.form1.mode.value=3; document.form1.submit();">
<input type="button" value="不可Re" onclick="document.form1.mode.value=4; document.form1.submit();">
<input type="button" value="清除MG" onclick="document.form1.mode.value=5; document.form1.submit();">
</form>
<?php
		if ($start > 0)
		{
?>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=<?php echo $start - $artcnt; ?>">上一页</a>
<?php
		}
		if ($start < $total - 20)
		{
?>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=<?php echo $start + $artcnt; ?>">下一页</a>
<a href=bbsdoc.php?board=<?php echo $brd_encode; ?>>一般模式</a> <a href=/cgi-bin/bbs/bbsdenyall?board=<?php echo $brd_encode; ?>>封人名单</a> <a href=bbsmnote.php?board=<?php echo $brd_encode; ?>>编辑进版画面</a>
<form action=bbsmdoc.php?board=<?php echo $brd_encode; ?> method=post>
<input type=submit value=跳转到> 第 <input type=text name=start size=4> 篇</form>
<?php
		html_normal_quit();
	}
?>
