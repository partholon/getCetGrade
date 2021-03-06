<?php
/**
 *@Filename: cet.php
 *@Function: 获取cet成绩
 *@Version: 1.1
 *@Created by: Partholon	
 *@Created time: 2015年8月19日
 */

$id = $_GET['id'];
$name = $_GET['xm'];

//构造HTTP请求头
$n2 = rand(202, 239);
$n3 = rand(1, 254);
$n4 = rand(1, 254);
$ip = "42." . $n2 . "." . $n3 . "." . $n4;
$header = array(
    "Host:www.chsi.com.cn",
    "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
    "Accept-Encoding:gzip, deflate, sdch",
    "Accept-Language:zh-CN,zh;q=0.8,en;q=0.6",
    "Connection:keep-alive",
    "User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36",
    "CLIENT-IP:$ip",
    "X-FORWARDED-FOR:$ip");

//获取Cookie
$cookie = tempnam("./temp", "cookie");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.chsi.com.cn");
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
$content = curl_exec($ch);
curl_close($ch);

//header增加referer
$header[8] = "Referer:http://www.chsi.com.cn/cet/";

//get提交
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.chsi.com.cn/cet/query?zkzh=$id&xm=$name");
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_ENCODING ,'gzip'); //加入gzip解析
$out = curl_exec($ch);
curl_close($ch);

unlink($cookie);
// echo $out;

preg_match_all("/(<table border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"6\" class=\"cetTable\">[\s\S]*?<\/table>)/", $out, $matches);
$matches = $matches[0][0];
$matches = strip_tags($matches);
$matches = preg_replace("/总分：/", "总分：@", $matches, -1);
$matches = preg_replace("/\t/", "", $matches, -1);
$matches = preg_replace("/\v/", " ", $matches, -1);
$matches = preg_replace("/\s{4,}/", "\n", $matches, -1);
$matches = preg_replace("/&nbsp;&nbsp;/", "\n", $matches, -1);
$matches = preg_replace("/@\n/", "", $matches, -1);
preg_match_all("/总分：[0-9]{1,}/", $matches, $fen);
$fen = $fen[0][0];
$fen = preg_replace("/总分：/", "", $fen, -1);
$matches = preg_replace("/总分：/", "总分：\n", $matches, -1);
if (empty($matches)) {
       $contentStr="啊噢！没有查到！有可能因为：1格式错误。2准考证号错误。3姓名不准确。";
} else if ($fen >= 425) {
    $matches = "恭喜你CET通过了~\n-----------------------\n" . $matches;
    $contentStr = $matches;
} else {
    $matches = "很遗憾的告诉你CET没有通过,再接再厉\n-----------------------\n" . $matches;
    $contentStr = $matches;
}

echo $contentStr;
?>
