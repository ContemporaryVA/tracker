#Tracker
This handler lets you put logs into Elasticsearch in the Logstash format, which makes visualization with Kibana very easy.

## Installation
If you're using [composer](http://getcomposer.org/) (and I hope you are), add the `contemporaryva/tracker` package to your project. Or just run:
```bash
$ composer require contemporaryva/tracker
```
In your project root directory.

## Basic Usage

```php
<?php

require 'vendor/autoload.php';

$tracker = new \ContemporaryVA\Tracker\Tracker('timer');

$tracker
    ->setProtocol('http')
    ->setHost('example.com')
    ->setSlug('default')
    ->setPort(8080);

$tracker->log('metric.foo', ['value' => 123456]);
```
