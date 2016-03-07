<?php

namespace ContemporaryVA\Tracker;

use Elasticsearch\Client;
use ContemporaryVA\Monolog\ElasticLogstashHandler;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Logger;

class Tracker
{

    /**
     * @var ElasticLogstashHandler
     */
    protected $handler;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var string
     */
    protected $protocol = 'http';

    /**
     * @var string
     */
    protected $host = 8080;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $slug = 'default';

    /**
     * @var \Monolog\Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * @var Client
     */
    protected $client;

    /**
     * While many levels of severity are supported by Monolog, we are only sending info
     * to Kibana, info is sufficient for our purposes at this point.
     *
     * @var array
     */
    protected $severity = [
        200 => 'INFO'
    ];

    protected $category;

    public function __construct($category, $host = null, $port = 8080, $protocol = 'http')
    {
        $this->category = $category;

        $this->setHost($host);
        $this->setPort($port);
        $this->setProtocol($protocol);
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        if(empty($this->handler)) {
            $this->handler = new ElasticLogstashHandler($this->getClient(), [
                'type' => join('-', [$this->category, 'logs'])
            ]);
            $this->handler->setFormatter($this->getFormatter());
        }

        return $this->handler;
    }

    /**
     * @param \Monolog\Handler\AbstractProcessingHandler $handler
     * @return $this
     */
    public function setHandler(\Monolog\Handler\AbstractProcessingHandler $handler)
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {

        if(empty($this->log)) {
            $this->log = new Logger($this->category);
            $this->log->pushHandler($this->getHandler());
        }

        return $this->log;
    }

    /**
     * @param \Psr\Log\LoggerInterface $log
     * @return $this
     */
    public function setLog(\Psr\Log\LoggerInterface $log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     * @return $this
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return array
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param array $severity
     * @return $this
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return \Monolog\Formatter\FormatterInterface
     */
    public function getFormatter()
    {
        if(empty($this->formatter)) {
            $this->formatter = new LogstashFormatter($this->getSlug(), null, null, '', 1);
        }

        return $this->formatter;
    }

    /**
     * @param \Monolog\Formatter\FormatterInterface $formatter
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if(empty($this->client)) {
            $this->client = new Client([
                'hosts' => [
                    $this->parseUrl()
                ]
            ]);
        }

        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    public function getSeverityValue($severity = 200)
    {
        if( ! array_key_exists($severity, $this->severity)) {
            throw new \InvalidArgumentException('The specified severity of ' . $severity . ' is invalid');
        }

        return $this->severity[$severity];
    }

    /**
     * @return string
     */
    protected function parseUrl()
    {

        $protocol = $this->getProtocol();
        $host = $this->getHost();
        $port = $this->getPort();

        if(empty($protocol)
            || empty($host)
            || empty($port)) {
            throw new \InvalidArgumentException('You must set the protocol, host and port');
        }

        return sprintf('%1$s://%2$s:%3$s', $protocol, $host, $port);
    }

    public function log($event, $data = [], $severity = 200)
    {
        $this->getLog()->log($this->getSeverityValue($severity), $event, $data);
    }

}
