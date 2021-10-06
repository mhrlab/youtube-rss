<?php
/**
 *   Project:    youtube-rss
 *   File:       example-2.php
 *   Date:       04-10-2021 11:45 PM
 *
 * @author:    Hemant
 * @copyright  Copyright (c) 2021 Hemant
 * @license    GPL V2
 * @version    1.0
 */

require_once __DIR__.'/vendor/autoload.php';

$rss = new Feed();
$rss->time_zone = 'asia/kolkata';
$rss->datetime_format = 'Y-m-d\TH:i:sP';

$rss->cacheDir = __DIR__.'/cache/';
$rss->cacheExpire = '30 minute';



$rss->loadRss('https://www.youtube.com/feeds/videos.xml?channel_id=UCOZ1J_3vdvBvdbJNXuJBNCQ');
if ($rss->error){
  echo $rss->errorMessage;
}else{
  $array = $rss->toArray();
  $rss->toJSON(true);
}
