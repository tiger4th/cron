<?php
set_time_limit(500);
mb_language("Japanese");

require_once('./phpQuery.php');

//フォルダの定義
define("OUTPUT_SNEWS", "./snews/");
define("TOPSY_KEY", $app_id_topsy);

// XML
$list = array('naver_hot'     => 'http://matome.naver.jp/feed/hot',
              'togetter'      => 'http://togetter.com/rss/index',
              // 'togetter_full' => 'http://fullrss.net/a/http/togetter.com/rss/index',
              'nico_all'      => 'http://www.nicovideo.jp/ranking/fav/hourly/all?rss=2.0&lang=ja-jp',
              'trend'         => 'http://searchranking.yahoo.co.jp/rss/burst_ranking-rss.xml',
             );

foreach ($list as $category => $url) {
	$xml = file_get_contents($url);
  if ($xml != '') {
    file_put_contents(OUTPUT_SNEWS.$category.'.xml', $xml);
  }
}

// topsy search
/*
$list = array('news'             => 'headlines.yahoo.co.jp',
              'blog'             => 'blog.livedoor.jp',
              '2ch'              => '2ch.net',
              'naver_twitter'    => 'matome.naver.jp',
              'togetter_twitter' => 'togetter.com',
              'nico_twitter'     => 'nicovideo.jp+-site%3Anews.nicovideo.jp+-site%3Aseiga.nicovideo.jp+-site%3Alive.nicovideo.jp+-site%3Ablog.nicovideo.jp+-site%3Adic.nicovideo.jp+-site%3Asecure.nicovideo.jp+-site%3Ach.nicovideo.jp+-site%3Acom.nicovideo.jp',
              'amazon'           => 'amazon.co.jp',
              'wikipedia'        => 'ja.wikipedia.org',
              'booklog'          => 'booklog.jp/item',
              'cookpad'          => 'cookpad.com',
              'chiebukuro'       => 'detail.chiebukuro.yahoo.co.jp',
              'bokete'           => 'bokete.jp',
             );

$window = array('news'             => 'h',
                'blog'             => 'h',
                '2ch'              => 'h',
                'naver_twitter'    => 'h',
                'togetter_twitter' => 'h',
                'nico_twitter'     => 'h',
                'amazon'           => 'h',
                'wikipedia'        => 'd',
                'booklog'          => 'd',
                'cookpad'          => 'd',
                'chiebukuro'       => 'd',
                'bokete'           => 'd',
               );

foreach ($list as $key => $value) {
  $json = array();

  for ($i = 0; $i < 3; $i++) { 
    $html = phpQuery::newDocumentFile('http://topsy.com/s?allow_lang=ja&type=link&q=site%3A'.$value.'&window='.$window[$key].'&page='.($i+1));

    for ($j = 0; $j < 10; $j++) { 
      if ($key == 'amazon') {
        $json['response']['list'][($j+$i*10)]['title'] = getPageTitle($html['a.x-result-link-title.idx-'.(($j+$i*10)+1)]->attr('href'));
      } else {
        $json['response']['list'][($j+$i*10)]['title'] = $html['a.x-result-link-title.idx-'.(($j+$i*10)+1)]->html();
      }
      $json['response']['list'][($j+$i*10)]['url'] = $html['a.x-result-link-title.idx-'.(($j+$i*10)+1)]->attr('href');
      $json['response']['list'][($j+$i*10)]['trackback_permalink'] = $html['a.x-result-link-date.idx-'.(($j+$i*10)+1)]->attr('href');
      //$json['response']['list'][($j+$i*10)]['trackback_date'] = $html['a.x-result-link-date.idx-'.(($j+$i*10)+1)]->html();
      $trackback_total = $html['a.x-result-link-trackback-total.idx-'.(($j+$i*10)+1)]->html();
      $trackback_total = str_replace(',', '', explode(' ', $trackback_total));
      $trackback_total = str_replace('K', '000', $trackback_total);
      $trackback_total = str_replace('M', '000000', $trackback_total);
      if ($trackback_total[0] != '') {
        $trackback_total = $trackback_total[0];
      } else {
        $trackback_total = 1;
      }
      $json['response']['list'][($j+$i*10)]['trackback_total'] = $trackback_total;
    }
  }

  if ($json['response']['list'][0]['title']!='') {
    file_put_contents(OUTPUT_SNEWS.$key.'.json', json_encode($json));
  }
}

function getPageTitle( $url ) {
    $html = file_get_contents($url);
    $html = mb_convert_encoding($html, "UTF-8", "auto" );
    if ( preg_match( "/<title>(.*?)<\/title>/i", $html, $matches) ) {
        return $matches[1];
    } else {
        return false;
    }
}

// topsy top
$list = array('tweet' => '15',
              'image' => '20',
              'video' => '15',
              );

foreach ($list as $key => $value) {
  $json = array();

  for ($i = 0; $i < 2; $i++) { 
    $html = file_get_contents('http://topsy.com/top100/ja?locale=en&thresh=top100&type='.$key.'&page='.($i+1));
    $html = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $html);
    $html = phpQuery::newDocument($html);

    for ($j = 0; $j < $value; $j++) { 
      $json['response']['list'][($j+$i*$value)]['target']['mytype'] = $key;

      if ($key == 'image') {
        $json['response']['list'][($j+$i*$value)]['target']['url'] = $html['a.x-result-image-thumbnail.idx-'.$j]->attr('href');
        $title = $html['a.x-result-image-thumbnail.idx-'.$j]->attr('title');
        $title = explode('http', $title);
        $title = trim($title[0]);
        $json['response']['list'][($j+$i*$value)]['target']['title'] = $title;
        //$json['response']['list'][($j+$i*$value)]['date'] = $html['a.x-result-image-date.idx-'.$j]->html();
        $trackback_total = $html['a.x-result-image-trackback-total.idx-'.$j]->html();
      } elseif ($key == 'video') {
        $json['response']['list'][($j+$i*$value)]['target']['url'] = $html['a.x-result-video-title.idx-'.$j]->attr('href');
        $title = $html['a.x-result-video-title.idx-'.$j]->html();
        $title = trim($title);
        $json['response']['list'][($j+$i*$value)]['target']['title'] = $title;
        //$json['response']['list'][($j+$i*$value)]['date'] = $html['div.list-video-v2.idx-'.$j.' .date']->html();
        $trackback_total = $html['a.x-result-video-trackback-total.idx-'.$j]->html();
      } else {
        $json['response']['list'][($j+$i*$value)]['target']['url'] = $html['a.x-result-link-date.idx-'.$j]->attr('href');
        $json['response']['list'][($j+$i*$value)]['target']['title'] = '';
        //$json['response']['list'][($j+$i*$value)]['date'] = $html['a.x-result-link-date.idx-'.$j]->html();
        $trackback_total = $html['a.x-result-link-trackback-total.idx-'.$j]->html();
      }
      $trackback_total = str_replace(',', '', explode(' ', $trackback_total));
      $trackback_total = str_replace('K', '000', $trackback_total);
      $trackback_total = str_replace('M', '000000', $trackback_total);
      if ($trackback_total[0] != '') {
        $trackback_total = $trackback_total[0];
      } else {
        $trackback_total = 1;
      }
      $json['response']['list'][($j+$i*$value)]['target']['trackback_total'] = $trackback_total;
    }
  }

  if ($json['response']['list'][0]['target']['url']!='') {
    file_put_contents(OUTPUT_SNEWS.'twitter_'.$key.'.json', json_encode($json));
  }
}
*/

// topsy api
/*
$list = array('news'             => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:headlines.yahoo.co.jp',
              // 'blog'             => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:blog.livedoor.jp',
              'blog'             => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=',
              '2ch'              => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:2ch.net',
              'twitter_tweet'    => 'http://otter.topsy.com/top.json?thresh=top100&locale=ja&type=tweet&perpage=100',
              'twitter_image'    => 'http://otter.topsy.com/top.json?thresh=top100&locale=ja&type=image&perpage=100',
              'twitter_video'    => 'http://otter.topsy.com/top.json?thresh=top100&locale=ja&type=video&perpage=100',
              'naver_twitter'    => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:matome.naver.jp',
              'togetter_twitter' => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:togetter.com',
              'nico_twitter'     => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:nicovideo.jp%20-site:news.nicovideo.jp%20-site:seiga.nicovideo.jp%20-site:live.nicovideo.jp%20-site:blog.nicovideo.jp%20-site:dic.nicovideo.jp%20-site:secure.nicovideo.jp%20-site:ch.nicovideo.jp',
              'amazon'           => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:amazon.co.jp',
              'wikipedia'        => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=d&q=site:ja.wikipedia.org',
              'chiebukuro'       => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=d&q=site:detail.chiebukuro.yahoo.co.jp',
              'cookpad'          => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=d&q=site:cookpad.com',
              'bokete'           => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=d&q=site:bokete.jp',
              'ustream'          => 'http://otter.topsy.com/search.json?type=link&perpage=100&window=h&q=site:ustream.tv&allow_lang=ja',
             );
$blog_list = array('2chblog.jp',
                   'ameblo.jp',
                   'blog.excite.co.jp',
                   'blog.fc2.com',
                   'blog.goo.ne.jp',
                   'blog.livedoor.jp',
                   'blog.so-net.ne.jp',
                   'blogs.yahoo.co.jp',
                   'blogspot.jp',
                   'blogzine.jp',
                   'blomaga.jp',
                   'cocolog-nifty.com',
                   'd.hatena.ne.jp',
                   'doorblog.jp',
                   'exblog.jp',
                   'hatenablog.com',
                   'jugem.jp',
                   'ldblog.jp',
                   'livedoor.biz',
                   'plaza.rakuten.co.jp',
                   'seesaa.net',
                   'webry.info',
                   'yaplog.jp',
                  );
foreach ($blog_list as $value) {
  $list['blog'] .= 'site:'.$value.'%20||%20';
}

foreach ($list as $category => $url) {
	$json = file_get_contents($url.'&apikey='.TOPSY_KEY);
	file_put_contents(OUTPUT_SNEWS.$category.'.json', $json);
}
*/

// realtime keyword
$html = file_get_contents('http://realtime.search.yahoo.co.jp/search');

$html = explode('<table', $html);
$html = explode('table>', $html[1]);
$html = $html[0];

preg_match_all('/<a[^>]*>([^<]*)<\/a>/', $html, $matches);

$i = 0;
$j = 10;
$json = array();
foreach ($matches[1] as $key => $value) {
  if (($key % 2) == 0) {
      $json[$i] = $value;
      $i++;
  } else {
      $json[$j] = $value;
      $j++;
  }
}

if ($json[1] != '') {
  ksort($json);
  file_put_contents(OUTPUT_SNEWS.'keyword.json', json_encode($json));
}

echo "SUCCESS";
?>