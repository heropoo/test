# config
A configuration component

###Usage:

Install it via Composer:
```
composer require heropoo/config
```

```php
<?php
require_once './Config.php';
require_once './Exception.php';

use Moon\Config\Config;

$config = new Config('/path/to/config/dir');

//if get charset in app.php return 
$charset = $config->get('app.charset');

// the second parameter is if throw a exception
// when this config is not defined
$charset = $config->get('app.charset', true);
```




