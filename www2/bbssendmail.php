<?php
require("www2-funcs.php");
login_init();
assert_login();
mailbox_header("�����ż�");

$mailfile = $_POST["file"];
$maildir = "mail/".strtoupper($currentuser["userid"]{0})."/".$currentuser["userid"]."/.DIR";

if($mailfile == "")		// if to send a new mail
{
	if (! bbs_can_send_mail() )
		html_error_quit("�����ܷ����ż�");
	$incept = trim(ltrim(@$_POST['userid']));
}
else		// if to reply a mail
{
	$incept = bbs_getmailowner($maildir, $mailfile);
}

if (!$incept)
	html_error_quit("�������ռ���ID");
$lookupuser = array();
if (!bbs_getuser($incept,$lookupuser))
	html_error_quit("������ռ���ID");
$incept = $lookupuser['userid'];

if (!strcasecmp($incept,'guest'))
	html_error_quit("���ܷ��Ÿ�guest");

$title = trim(@$_POST["title"]);
if (!$title) $title = '������';

$sig = intval(@$_POST['signature']); //ǩ����
$backup = isset($_POST['backup'])?strlen($_POST['backup']):0; //����

$ret = bbs_postmail($incept,$title,@$_POST["text"],$sig,$backup);

if($mailfile != "")
{
	bbs_setmailreplied($maildir, $mailfile);
}


if ($ret < 0)  {
	switch($ret) {
		case -1:
		case -2:
			html_error_quit("�޷������ļ�");
			break;
		case -3:
			html_error_quit($incept." ���������ʼ�");
			break;
		case -4:
			html_error_quit($incept." ����������");
			break;
		case -6:
			html_error_quit("�����ʼ��б�����");
			break;
		case -7:
			html_error_quit("�ʼ����ͳɹ�����δ�ܱ��浽������");
			break;
		default:
			html_error_quit("ϵͳ��������ϵ����Ա");
	}
}
html_success_quit("�ż����ͳɹ�<br/>[<a href=\"bbsmail.php\">�����ҵ�����</a>]");
?>