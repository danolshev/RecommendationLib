<?php

namespace Recommendations\Tests;

use Recombee\RecommApi\Exceptions;
use Recombee\RecommApi\Requests\AddDetailView;

class AddDetailViewTest extends RecommendationsTestCase
{
    public function testAddInteraction() {

        //it does not fail with cascadeCreate
        $request = $this->createRequest('u_id','i_id',['cascadeCreate' => true]);
        $response = $this->client->send($request);

        //it does not fail with existing item and user
        $request = $this->createRequest('entity_id','entity_id');
        $response = $this->client->send($request);

        //it does not fail with valid timestamp
        $request = $this->createRequest('entity_id','entity_id',['timestamp' => '2013-10-29T09:38:41.341Z']);
        $response = $this->client->send($request);

        //it fails with nonexisting item id
        $request = $this->createRequest('entity_id','nonex_id');
        try {

            $this->client->send($request);
            throw new \Exception('Exception was not thrown');
        }
        catch(Exceptions\ResponseException $e)
        {
            $this->assertEquals(404, $e->status_code);
        }

        //it fails with nonexisting user id
        $request = $this->createRequest('nonex_id','entity_id');
        try {

            $this->client->send($request);
            throw new \Exception('Exception was not thrown');
        }
        catch(Exceptions\ResponseException $e)
        {
            $this->assertEquals(404, $e->status_code);
        }

        //it fails with invalid time
        $request = $this->createRequest('entity_id','entity_id',['timestamp' => -15]);
        try {

            $this->client->send($request);
            throw new \Exception('Exception was not thrown');
        }
        catch(Exceptions\ResponseException $e)
        {
            $this->assertEquals(400, $e->status_code);
        }

        //it really stores interaction to the system
        $request = $this->createRequest('u_id2','i_id2',['cascadeCreate' => true,'timestamp' => 5]);
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

    private function createRequest($userId, $itemId, $optional = [])
    {
        return new AddDetailView($userId, $itemId, $optional);
    }
}