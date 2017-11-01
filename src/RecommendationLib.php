<?php

namespace Recommendation;

use \Recombee\RecommApi\Client;

class RecommendationLib
{
    protected $recommendation;

    public function __construct($name, $apiKey, $dbName)
    {
        if ($name == 'recombee') {
            $this->recommendation = new Recombee(new Client($dbName, $apiKey));
        }
    }

    public function __call($method, $params)
    {
        call_user_func_array([$this->recommendation, $method], $params);
    }
}