<?php

namespace Recommendation\Tests;

use Recombee\RecommApi\Exceptions;
use Recombee\RecommApi\Requests\AddRating;

class AddRatingTest extends RecommendationsTestCase
{
    public function testAddRating()
    {

        //it does not fail with cascadeCreate
        $req = $this->createRequest('u_id', 'i_id', 1, ['cascadeCreate' => true]);
        $resp = $this->client->send($req);

        //it does not fail with existing item and user
        $req = $this->createRequest('entity_id', 'entity_id', 0);
        $resp = $this->client->send($req);

        //it fails with nonexisting item id
        $req = $this->createRequest('entity_id', 'nonex_id', -1);
        try {

            $this->client->send($req);
            throw new \Exception('Exception was not thrown');
        } catch (Exceptions\ResponseException $e) {
            $this->assertEquals(404, $e->status_code);
        }

        //it fails with nonexisting user id
        $req = $this->createRequest('nonex_id', 'entity_id', 0.5);
        try {

            $this->client->send($req);
            throw new \Exception('Exception was not thrown');
        } catch (Exceptions\ResponseException $e) {
            $this->assertEquals(404, $e->status_code);
        }

        //it fails with invalid time
        $req = $this->createRequest('entity_id', 'entity_id', 0, ['timestamp' => -15]);
        try {

            $this->client->send($req);
            throw new \Exception('Exception was not thrown');
        } catch (Exceptions\ResponseException $e) {
            $this->assertEquals(400, $e->status_code);
        }

        //it fails with invalid rating
        $req = $this->createRequest('entity_id', 'entity_id', -2);
        try {

            $this->client->send($req);
            throw new \Exception('Exception was not thrown');
        } catch (Exceptions\ResponseException $e) {
            $this->assertEquals(400, $e->status_code);
        }

        //it really stores interaction to the system
        $req = $this->createRequest('u_id', 'i_id', 0.3, ['cascadeCreate' => true, 'timestamp' => 5]);
        $resp = $this->client->send($req);
        try {

            $this->client->send($req);
            throw new \Exception('Exception was not thrown');
        } catch (Exceptions\ResponseException $e) {
            $this->assertEquals(409, $e->status_code);
        }

    }

    private function createRequest($userId, $itemId, $rating, $optional = [])
    {
        return new AddRating($userId, $itemId, $rating, $optional);
    }
}
