<?php

namespace Recommendations\Tests;

use Recombee\RecommApi\Exceptions;
use Recombee\RecommApi\Requests\DeleteItem;

class DeleteItemTest extends RecommendationsTestCase
{

    public function testDeleteEntity() {

        //it does not fail with existing entity id
        $request = $this->createRequest('entity_id');
        $response = $this->client->send($request);
        try {

            $this->client->send($request);
            throw new \Exception('Exception was not thrown');
        }
        catch(Exceptions\ResponseException $e)
        {
            $this->assertEquals(404, $e->status_code);
        }

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

        //it fails with non-existing entity
        $request = $this->createRequest('valid_id');
        try {

            $this->client->send($request);
            throw new \Exception('Exception was not thrown');
        }
        catch(Exceptions\ResponseException $e)
        {
            $this->assertEquals(404, $e->status_code);
        }
    }

    private function createRequest($itemId) {
        return new DeleteItem($itemId);
    }
}