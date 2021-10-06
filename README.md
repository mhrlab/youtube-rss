# YouTube-Rss Feed


[![mhrlab - youtube-rss](https://img.shields.io/static/v1?label=mhrlab&message=youtube-rss&color=blue&logo=github)](https://github.com/mhrlab/youtube-rss)
[![stars - youtube-rss](https://img.shields.io/github/stars/mhrlab/youtube-rss?style=social)](https://github.com/mhrlab/youtube-rss)
[![forks - youtube-rss](https://img.shields.io/github/forks/mhrlab/youtube-rss?style=social)](https://github.com/mhrlab/youtube-rss)

[![GitHub release](https://img.shields.io/github/release/mhrlab/youtube-rss?include_prereleases=&sort=semver&color=blue)](https://github.com/mhrlab/youtube-rss/releases/)
[![License](https://img.shields.io/badge/License-GPL_v2-blue)](https://github.com/mhrlab/youtube-rss/blob/master/LICENSE)
[![issues - youtube-rss](https://img.shields.io/github/issues/mhrlab/youtube-rss)](https://github.com/mhrlab/youtube-rss/issues)
[![](https://img.shields.io/packagist/dt/mhrlab/youtube-rss.svg)](https://github.com/mhrlab/youtube-rss/releases/)

Small and easy-to-use library for consuming YouTube feeds

---
- [Installation](#installation)
- [Requirements](#requirements)
- [Quick Start and Examples](#quick-start-and-examples)
- [Contribute](#contribute)
---

### Installation

To install **YouTube-Rss**, simply:

    composer require mhrlab/youtube-rss

For latest commit version:

    composer require mhrlab/youtube-rss:dev-master

### Requirements

**YouTube-Rss** works with PHP 7.0, 7.1, 7.2, 7.3, 7.4, and 8.0.

### Quick Start and Examples

More examples are available under [/examples](https://github.com/mhrlab/youtube-rss/tree/master/examples).
```php
require 'vendor/autoload.php';

$rss = new Feed('https://www.youtube.com/feeds/videos.xml?channel_id=CHANNEL_ID');

if ($rss->error){
  /* print error message  */
  echo $rss->errorMessage;
}else{
  $array = $rss->toArray();

  /*pass true to print json with header('content-type:application/json')*/
  $rss->toJSON(true);
}

```

```php
require 'vendor/autoload.php';

$rss = new Feed();

/*set time_zone to receive all time&date in own timezone. */
$rss->time_zone = 'asia/kolkata';

$rss->loadRss('https://www.youtube.com/feeds/videos.xml?channel_id=CHANNEL_ID');

if ($rss->error){
  /* print error message  */
  echo $rss->errorMessage;

}else{
  $array = $rss->toArray();
  /*pass true to print json with header('content-type:application/json')*/
  $rss->toJSON(true);
}

```
### Contribute

1. Check for open issues or open a new issue to start a discussion around a bug or feature.
1. Fork the repository on GitHub to start making your changes.
1. Write one or more tests for the new feature or that expose the bug.
1. Make code changes to implement the feature or fix the bug.
1. Send a pull request to get your changes merged and published.
