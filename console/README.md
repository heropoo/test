# console
console

## install
via composer
```
composer require heropoo/console
```

## usage
```php
$console = new \Moon\Console\Console();

$console->add('hello', function (){
    return 'Hello world!';
});
$console->add('ping', 'PingCommand::ping');

$app = new \Moon\Console\ConsoleApplication();
$status = $app->handCommand($console);
exit($status);
```