# php-mysql-db

A PHP interface for MySQL

## Install

```bash
composer require websvc/php-mysql-db 1.0.0
```

## Usage

```php
use PhpMysqlDb/Db;

$dbConn = new Db('host', 'username', 'password', 'database');

$sql = "SELECT * FROM table_name";
$exec = $dbConn->query($sql);

while ($row = $dbConn->fetch_assoc()) {
    echo "<br/>" . print_r($row, true);
}
```


```php
use PhpMysqlDb/Db;

$dbConn = new Db('host', 'username', 'password', 'database');

$sql = "SELECT * FROM table_name";
$exec = $dbConn->select($sql);

if ($exec) {
    echo "<br><pre>";
    print_r($exec);
    echo "</pre><br>";
}
```

Setting a logger Using `websvc/php-monolog-wrapper` wrapper

Every query will be logged if in DEBUG mode

```bash
composer require websvc/php-monolog-wrapper 1.0.0
```

```php
use PhpMysqlDb/Db;
use websvc/PhpMonologWrapper;

$log = new PhpMonologWrapper('logger-name', [
            'logFile' => '/tmp/mylog.log',
            'loggerLevel'=> 'DEBUG',    // Set logging level
            'toStderr'=> true           // Log output to stderr
        ]);

$dbConn = new Db('host', 'username', 'password', 'database');
$dbConn->setLogger($log);

$sql = "SELECT * FROM table_name";
$exec = $dbConn->query($sql);

while ($row = $dbConn->fetch_assoc()) {
    echo "<br/>" . print_r($row, true);
}
```
