# PHP Library RSS

RSS and Atom feed parsing library

```php
$rss = new \Rss('RSSURL', [
        'CACHE_FOLDER' => storage_path('framework/cache')
]);

$items = $rss->items();
```
