<?php
set_time_limit(500);

//モード設定
if(isset($_GET['mode'])){
	$mode = $_GET['mode'];
}else{
	$rand = mt_rand(1, 2);
	if($rand == 1){
		$mode = "a";
	}else{
		$mode = "d";
	}
}
//trace mode
echo "mode:".$mode."<br>";

//フォルダの定義
if($mode == "a"){
	define("INPUT", "./inputA/");
	define("OUTPUT", "./outputA/");
}elseif($mode == "d"){
	define("INPUT", "./inputD/");
	define("OUTPUT", "./outputD/");
}

if(isset($_GET['cour']) && ctype_digit($_GET['cour'])){
	$cour = $_GET['cour'].".txt";
}else{
	//存在するデータ
	if ($handle = opendir(INPUT)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$exist[] = $file;
			}
	    }
	    closedir($handle);
	}
	$rand = mt_rand(0, count($exist)-1);
	$cour = $exist[$rand];
}

$fno = fopen(INPUT.$cour, 'r');
$load = fread($fno, filesize(INPUT.$cour));
fclose($fno);

$load = explode("\\", $load);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

//trace
echo $cour."<br>";

foreach($load as $key => $value){
	//trace
	echo ($key+1)."<br>";

	$value = explode(",", $value);
	$url_t = str_replace("http://", "", $value[1]);

	//アニメのみ
	if($mode == "a"){
		//ニコニコ
		$url = "http://www.nicovideo.jp/tag/".rawurlencode($value[2])."?rss=2.0";
		curl_setopt($ch, CURLOPT_URL, $url);
		$res = curl_exec($ch);
		$xml = new SimpleXMLElement($res);
		$res = (string)$xml->channel[0]->description;
		$res = explode("(全 ", $res);
		$res = explode("件)", $res[1]);
		$res = str_replace(",", "", $res[0]);
		$data[$cour][$key]["n"]=$res;

		//pixiv
		$url = "http://www.pixiv.net/search.php?s_mode=s_tag&lang=ja&word=".rawurlencode($value[2]);
		curl_setopt($ch, CURLOPT_URL, $url);
		$res = curl_exec($ch);
		$res = explode("に関する作品は: ", $res);
		$res = explode("件 投稿されています", $res[1]);
		$data[$cour][$key]["p"] = $res[0];
	}

	if($value[1] == ''){
		$data[$cour][$key]["h"] = 0;
		$data[$cour][$key]["t"] = 0;
		$data[$cour][$key]["f"] = 0;
		$data[$cour][$key]["b"] = 0;
		$data[$cour][$key]["2"] = 0;
		continue;
	}
	
	//はてな
	$url = "http://api.b.st-hatena.com/entry.count?url=".$value[1];
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);
	if($res==""){$res=0;}
	$data[$cour][$key]["h"]=$res;
	if(!ctype_digit((string)$data[$cour][$key]["h"])){exit;}
	if($data[$cour][$key]["h"]==""){$data[$cour][$key]["h"]=0;}

	//twitter
	$url = "http://urls.api.twitter.com/1/urls/count.json?url=".$url_t;
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);
	$res = json_decode($res, true);
	$data[$cour][$key]["t"] = $res["count"];
	
	if($data[$cour][$key]["t"]==0){
		file_get_contents("http://tools.tweetbuzz.jp/imgcount?url=".$value[1]);
		foreach($http_response_header as $header){
			if(preg_match("#^Location: (.*?)([0-9]+?)(\.gif|\.png)?$#", $header, $match)){
				$data[$cour][$key]["t"] = intval($match[2]);
				break;
	    	}
		}
	}
	
	//facebook
	$url = "http://api.facebook.com/method/fql.query?query=select%20total_count%20from%20link_stat%20where%20url=%22".$value[1]."%22";
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);
	$xml = new SimpleXMLElement($res);
	$data[$cour][$key]["f"] = (string)$xml->link_stat[0]->total_count;
	//if($data[$cour][$key]["f"]==""){/*$data[$cour][$key]["f"]=0;*/exit;}

	//blog
	$req = "http://blog.search.yahoo.co.jp/search?p=".$url_t;
	curl_setopt($ch, CURLOPT_URL, $req);
	$res = curl_exec($ch);
	$res = explode("件目 / 約<span class=\"bo\">", $res);
	if(isset($res[1])){
		$res = explode("</span>件", $res[1]);
		$data[$cour][$key]["b"] = str_replace(",", "", $res[0]);
	}else{
		$data[$cour][$key]["b"] = 0;
	}
	/*
	$url = "http://search.yahooapis.jp/BlogSearchService/V1/blogSearch?appid=_Mv4G1Wxg66wh_I8XLpEu0UyZmE9NnahCauBOTvIDyLrDGdar8DCCmQ3O5EUOYYT&output=php&results=1&query=%22".str_replace("http://", "", rtrim($value[1], "/"))."%22";
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);
	$res = unserialize($res);
	$data[$cour][$key]["b"] = $res["Resultset"]["totalResultsAvailable"];
	if($data[$cour][$key]["b"]==""){$data[$cour][$key]["b"]=0;//exit;
	}
	*/

	//2ch
	$req = "http://search.yahoo.co.jp/search?p=site%3A%222ch.net%22+%22".$url_t."%22";
	curl_setopt($ch, CURLOPT_URL, $req);
	$res = curl_exec($ch);
	$res = explode("件目 / 約", $res);
	if(isset($res[1])){
		$res = explode("件 - ", $res[1]);
		$data[$cour][$key]["2"] = str_replace(",", "", $res[0]);
	}else{
		$data[$cour][$key]["2"] = 0;
	}
	/*
	$url = "http://search.yahooapis.jp/PremiumWebSearchService/V1/webSearch?appid=TZQugnyxg67UulsVyNgDymfw7B5MjL5AH4TwZkWvSZpXEEjPYfrLjWUa3euOlaaO&results=1&site=2ch.net&query=%22".str_replace("http://", "", rtrim($value[1], "/"))."%22";
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);
	$xml = new SimpleXMLElement($res);
	$data[$cour][$key]["2"] = (string)$xml->attributes()->totalResultsAvailable;
	if($data[$cour][$key]["2"]==""){$data[$cour][$key]["2"]=0;//exit;
	}
	*/

	foreach($data[$cour][$key] as $value){
		if((string)$value==""){exit;}
	}

	sleep(1);
}

curl_close($ch);

$serial = serialize($data[$cour]);

if(strlen($serial) < 100){
	echo "too small!";
	exit;
}

$fno = fopen(OUTPUT.$cour, 'w');
fwrite($fno, $serial);
fwrite($fno, "\r\n");
fwrite($fno, time());
//fwrite($fno, time()+25200);
fclose($fno); 

echo "SUCCESS";
?>