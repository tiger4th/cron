<?php
//フォルダの定義
define("OUTPUT_HATEBU", "./hatebu/");

$list = array('topics'        => 'http://b.hatena.ne.jp/entrylist?sort=hot&threshold=3&url=http%3A%2F%2Fheadlines.yahoo.co.jp%2F&mode=rss',
              'hotentry'      => 'http://b.hatena.ne.jp/hotentry.rss',
              'general'       => 'http://b.hatena.ne.jp/hotentry.rss?mode=general',
              'social'        => 'http://b.hatena.ne.jp/hotentry/social.rss',
              'economics'     => 'http://b.hatena.ne.jp/hotentry/economics.rss',
              'life'          => 'http://b.hatena.ne.jp/hotentry/life.rss',
              'entertainment' => 'http://b.hatena.ne.jp/hotentry/entertainment.rss',
              'knowledge'     => 'http://b.hatena.ne.jp/hotentry/knowledge.rss',
              'it'            => 'http://b.hatena.ne.jp/hotentry/it.rss',
              'game'          => 'http://b.hatena.ne.jp/hotentry/game.rss',
              'fun'           => 'http://b.hatena.ne.jp/hotentry/fun.rss',
              'amazon'        => 'http://b.hatena.ne.jp/entrylist?sort=hot&threshold=3&url=http%3A%2F%2Fwww.amazon.co.jp%2F&mode=rss',
             );

$options = array(
  'http' => array(
    'method' => 'GET',
    'header' => 'User-Agent: Mozilla/5.0 AppleWebKit/537.36 Chrome/46.0.2490.86 Safari/537.36',
  ),
);
$context = stream_context_create($options);

foreach ($list as $category => $url) {
  $xml = file_get_contents($url, false, $context);
  file_put_contents(OUTPUT_HATEBU.$category.'.xml', $xml);
}

echo "SUCCESS";
?>