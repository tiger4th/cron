<?php
//フォルダの定義
define("OUTPUT_HATEBU_EX", "./hatebu_ex/");

$list = array('topics'        => 'http://b.hatena.ne.jp/entrylist?sort=hot&threshold=2&url=http%3A%2F%2Fheadlines.yahoo.co.jp%2F&mode=rss',
              'hotentry'      => 'http://b.hatena.ne.jp/entrylist?sort=hot&threshold=&mode=rss',
              'general'       => 'http://b.hatena.ne.jp/entrylist/general?sort=hot&threshold=&mode=rss',
              'social'        => 'http://b.hatena.ne.jp/entrylist/social?sort=hot&threshold=&mode=rss',
              'economics'     => 'http://b.hatena.ne.jp/entrylist/economics?sort=hot&threshold=&mode=rss',
              'life'          => 'http://b.hatena.ne.jp/entrylist/life?sort=hot&threshold=&mode=rss',
              'entertainment' => 'http://b.hatena.ne.jp/entrylist/entertainment?sort=hot&threshold=&mode=rss',
              'knowledge'     => 'http://b.hatena.ne.jp/entrylist/knowledge?sort=hot&threshold=&mode=rss',
              'it'            => 'http://b.hatena.ne.jp/entrylist/it?sort=hot&threshold=&mode=rss',
              'game'          => 'http://b.hatena.ne.jp/entrylist/game?sort=hot&threshold=&mode=rss',
              'fun'           => 'http://b.hatena.ne.jp/entrylist/fun?sort=hot&threshold=&mode=rss',
              'amazon'        => 'http://b.hatena.ne.jp/entrylist?sort=hot&threshold=2&url=http%3A%2F%2Fwww.amazon.co.jp%2F&mode=rss',
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
  file_put_contents(OUTPUT_HATEBU_EX.$category.'.xml', $xml);
}

echo "SUCCESS";
?>