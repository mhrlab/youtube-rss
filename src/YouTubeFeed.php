<?php
/**
 *   Project:    youtube-rss
 *   File:       YouTubeFeed.php
 *   Date:       04-10-2021 07:27 PM
 *
 * @author:    Hemant
 * @copyright  Copyright (c) 2021 Hemant
 * @license    GPL V2
 * @version    1.0
 */

class Feed
{

  /** @var int */
  public $cacheExpire = '5 hour';

  /** @var string */
  public $cacheDir ;

  /** @var string */
  public static $userAgent = 'FeedFetcher-Google';

  /** @var SimpleXMLElement */
  protected $xml;
  private $feedArray;

  public $time_zone = 'asia/kolkata';
  public $datetime_format = 'Y-m-d\TH:i:sP';

  public  $error = false;
  public  $errorMessage = null;



  function __construct($url = null){
    if (!is_null($url)) $this->loadRss($url);
  }
  /**
   * Loads RSS feed.
   * @param string  RSS feed URL
   * @param string  optional username
   * @param string  optional password
   * @return bool
   */
  public function loadRss($url, $user = null, $pass = null): bool
  {
    if (empty($url)){
      return $this->genError("Sorry Please provide valid url.");
    }

    try {
      return $this->fromRss($this->loadXml($url, $user, $pass));
    } catch (FeedException $e) {
      return $this->genError("Error: FeedException - ".$e->getMessage());
    } catch (Exception $e) {
      return $this->genError("Error: Exception - ".$e->getMessage());
    }
  }


  /**
   * @param SimpleXMLElement $xml
   * @return bool
   */
  private  function fromRss(SimpleXMLElement $xml): bool
  {
    if (!$xml->entry) {
      return $this->genError("Error: Invalid feed.");
    }
    try {
      return $this->generateFeedArray($xml);
    } catch (FeedException $e) {
      return $this->genError("Error while generating xml array");
    } catch (Exception $e) {
      return $this->genError("Error while generating xml array");
    }
  }



  /**
   * Returns property value. Do not call directly.
   * @param  string  tag name
   * @return SimpleXMLElement
   */
  public function __get($name)
  {
    return $this->xml->{$name};
  }


  /**
   * Sets value of a property. Do not call directly.
   * @param string  property name
   * @param mixed   property value
   * @return void
   * @throws Exception
   */
  public function __set($name, $value)
  {
    throw new Exception("Cannot assign to a read-only property '$name'.");
  }


  /**
   * Return Feed array.
   * @return array
   */
  public function toArray(): array
  {
    return $this->feedArray;
  }

  /**
   * Return Feed into json.
   * @param bool $print
   * @return false|string
   */
  public function toJSON(bool $print)
  {
    if ($print){
      header('content-type: application/json');
      echo json_encode($this->feedArray,JSON_PRETTY_PRINT);
      return null;
    }else{
      return json_encode($this->feedArray,JSON_PRETTY_PRINT);
    }
  }


  /**
   * Load XML from cache or HTTP.
   * @param string
   * @param string
   * @param string
   * @return SimpleXMLElement
   * @throws Exception
   */
  private function loadXml($url, $user, $pass): SimpleXMLElement
  {
    $e = $this->cacheExpire;
    $cacheFile =$this->cacheDir . '/feed.' . md5(serialize(func_get_args())) . '.xml';

    if ($this->cacheDir
      && (time() - @filemtime($cacheFile) <= (is_string($e) ? strtotime($e) - time() : $e))
      && $data = @file_get_contents($cacheFile)
    ) {
      $this->genStaticConfig(true,@filemtime($cacheFile));

      // ok
    } elseif ($data = trim(self::httpRequest($url, $user, $pass))) {

     $this->genStaticConfig(false,time());

      if ($this->cacheDir) {
        $this->genCacheDir();
        file_put_contents($cacheFile, $data);
      }
    } elseif ($this->cacheDir && $data = @file_get_contents($cacheFile)) {
      // ok
    } else {
      $this->genError('Cannot load feed.');
    }

    return new SimpleXMLElement($data, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA);
  }


  /**
   * Process HTTP request.
   * @param  string
   * @param  string
   * @param  string
   * @return string|false
   */
  private static function httpRequest($url, $user, $pass)
  {
    if (extension_loaded('curl')) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      if ($user !== null || $pass !== null) {
        curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
      }
      curl_setopt($curl, CURLOPT_USERAGENT, self::$userAgent); // some feeds require a user agent

      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_TIMEOUT, 20);
      curl_setopt($curl, CURLOPT_ENCODING, '');

      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // no echo, just return result

      if (!ini_get('open_basedir')) {
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // sometime is useful :)
      }
      $result = curl_exec($curl);
      return curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200
        ? $result
        : false;

    } else {
      $context = null;
      if ($user !== null && $pass !== null) {
        $options = [
          'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Basic ' . base64_encode($user . ':' . $pass) . "\r\n",
          ],
        ];
        $context = stream_context_create($options);
      }

      return file_get_contents($url, false, $context);
    }
  }

  /**
   * Generates better accessible namespaced tags.
   * @param SimpleXMLElement
   * @return bool
   * @throws Exception
   */
  private function generateFeedArray($el): bool
  {



    $this->feedArray['id']      = str_replace('yt:channel:','',(string)$el->id);
    $this->feedArray['title']   = (string)$el->title;
    $this->feedArray['link']    = (string)$el->link[1]->attributes()->href;
    $this->feedArray['pubDate'] = $this->UTCtoTimeZone((string)$el->published);



    $feed_items = [];
    foreach ($el->entry as $entry){
      $yt_child     = $entry->children('yt',true);
      $media_child  = $entry->children('media',true)->group;
      $community    = $media_child->community;

      $pubDate = $this->UTCtoTimeZone((string)$entry->published);
      $upDate = $this->UTCtoTimeZone((string)$entry->updated);

      $video_id = (string)$yt_child->videoId;
      $feed_items[] = array(
        "guid"        => (string)$entry->id,
        'id'          => $video_id,
        'title'       => (string)$entry->title,
        'description' => (string)$media_child->description,
        'link'        => "https://www.youtube.com/watch?v=$video_id",
        'img'         => 'https://img.youtube.com/vi/'.$video_id.'/maxresdefault.jpg',
        'thumb'       => 'https://img.youtube.com/vi/'.$video_id.'/hqdefault.jpg',
        "author"      =>  (string)$entry->author->name,
        "author_uri"  =>  (string)$entry->author->uri,
        "channel_title" => (string)$el->title,
        "channel_id"    => (string)$yt_child->channelId,
        'pubDate'     => $pubDate,
        'upDate'      => $upDate,
        'views'       => (int)$community->statistics->attributes()['views'],
        'ratings_count' => (int)$community->starRating->attributes()['count'],
        'ratings_avg' => (float)$community->starRating->attributes()['average'],

      );

    }
    $this->feedArray['items'] = $feed_items;

    return !empty($this->feedArray);

  }

  /**
   * @param $date_time
   * @return string
   * @throws Exception
   */
  private function UTCtoTimeZone($date_time): string
  {
    $date_time = new DateTime($date_time, new DateTimeZone('UTC'));
    $date_time->setTimezone(new DateTimeZone($this->time_zone));
    return $date_time->format($this->datetime_format);
  }


  /**
   * @throws Exception
   */
  private function genStaticConfig($isCached = false, $mtime=''){
    $this->feedArray['config']['cache'] = $isCached;
    $this->feedArray['config']['mtime'] = $this->UTCtoTimeZone(date($this->datetime_format,$mtime));
  }

  /**
   * @param string $errorMessage
   * @return bool
   */
  private function genError(string $errorMessage): bool
  {
    $this->error = true;
    $this->errorMessage = $errorMessage;

//    return reverse value then error for confirmation;
    return !$this->error;
  }

  private function genCacheDir(){
    if (!file_exists($this->cacheDir)) {
      mkdir($this->cacheDir, 0777, true);
    }
  }
}






/**
 * An exception generated by Feed.
 */
class FeedException extends Exception
{
}
