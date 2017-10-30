<?php

namespace Recommendations\Tests;

use Recombee\RecommApi\Exceptions;
use Recombee\RecommApi\Requests\AddItem;

class AddItemTest extends RecommendationsTestCase
{
    public function testAddEntity() {

        //it does not fail with valid entity id
        $request = $this->createRequest('valid_id');
        $response = $this->client->send($request);

        //it fails with invalid entity id
        $request = $this->createRequest('...not_valid...');
        try {

            $this->client->send($request);
            throw new \Exception('Exception was not thrown');
        }
        catch(Exceptions\ResponseException $e)
        {
            $this->assertEquals(400, $e->status_code);
        }

        //it really stores entity to the system
        $request = $this->createRequest('valid_id2');
        $response = $this->client->send($request);
        try {

            $this->client->send($request);
            throw new \Exception('Exception was not thrown');
        }
        catch(Exceptions\ResponseException $e)
        {
            $this->assertEquals(409, $e->status_code);
        }

    }

    private function createRequest($itemId)
    {
        return new AddItem($itemId);
    }
}