<?php

namespace ContemporaryVA\Monolog;

use Monolog\ElasticLogstashHandler as BaseElasticLogstashHandler;

/**
 * Class ElasticLogstashHandler
 * @package ContemporaryVA\Monolog
 */
class ElasticLogstashHandler extends BaseElasticLogstashHandler
{

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        try {
            $this->client->index([
                'index' => $this->options['index'],
                'type' => $this->options['type'],
                'timeout' => '50ms',
                'body' => json_decode($record['formatted'], true)
            ]);
        } catch (\Exception $e) {
            // Well that didn't pan out...
            if (!$this->options['ignore_error']) {
                throw $e;
            }
        }
    }

}