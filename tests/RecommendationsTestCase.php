<?php

namespace Recommendation\Tests;

use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests;

class RecommendationsTestCase extends \PHPUnit_Framework_TestCase
{
    protected $client;

    protected function setUp() {

        $this->client = new Client('client-test', 'jGGQ6ZKa8rQ1zTAyxTc0EMn55YPF7FJLUtaMLhbsGxmvwxgTwXYqmUk5xVZFw98L');
        $requests = new Requests\Batch([
            new Requests\AddItem('entity_id'),

        ]);

        $this->client->send($requests);
    }
}