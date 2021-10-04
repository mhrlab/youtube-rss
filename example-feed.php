<?php
/**
 *   Project:    youtube-rss
 *   File:       example-feed.php
 *   Date:       04-10-2021 07:29 PM
 *
 * @author:    Hemant
 * @copyright  Copyright (c) 2021 Hemant
 * @license    GPL V2
 * @version    1.0
 */

require_once __DIR__.'/vendor/autoload.php';


/*******/

$rss = new Feed('https://www.youtube.com/feeds/videos.xml?channel_id=UCOZ1J_3vdvBvdbJNXuJBNCQ');
//$rss->loadRss('https://www.youtube.com/feeds/videos.xml?channel_id=UCOZ1J_3vdvBvdbJNXuJBNCQ');
if ($rss->error){
  echo $rss->errorMessage;
}else{
  $array = $rss->toArray();
  $rss->toJSON(true);
}
