<?php

namespace Recommendations;

use \Recombee\RecommApi\Client;
use \Recombee\RecommApi\Requests;
use yii\base\Component;
use Recombee\RecommApi\Exceptions;

class Recombee extends Component implements Recommendations
{
    public $apiKey;
    public $dbName;
    protected $client;

    public function init()
    {
        parent::init();
        $this->client = new Client($this->dbName, $this->apiKey);
    }

    /**
     * Adds new item of given itemId to the items catalog.
     * All the item properties for the newly created items are set null
     * @param $itemId
     * @return mixed
     */
    public function addItem($itemId)
    {
        $response = $this->send(new Requests\AddItem($itemId));
        return $response;
    }

    /**
     * Deletes an item of given itemId from the catalog. If there are any purchases, ratings, bookmarks,
     * cart additions or detail views of the item present in the database, they will be deleted in cascade as well.
     * Also, if the item is present in some series, it will be removed from all the series where present.
     * If an item becomes obsolete/no longer available, it is often meaningful to keep it in the catalog (along with all the interaction data, which are very useful),
     * and only exclude the item from recommendations. In such a case, use ReQL filter instead of deleting the item completely.
     * @param $itemId
     * @return mixed
     */
    public function deleteItem($itemId)
    {
        $response = $this->send(new Requests\DeleteItem($itemId));
        return $response;
    }

    /**
     * The following methods allow you to maintain the set of items in the catalog.
     * @param $optional
     * Example,
     * 'filter' => <string>,
     * 'count' => <integer>,
     * 'offset' => <integer>,
     * 'returnProperties' => <boolean>,
     * 'includedProperties' => <array>
     * @return array
     */
    public function getListItems($optional = [])
    {
        $response = $this->send(new Requests\ListItems($optional));
        return $response;
    }

    /**
     * Setting an item property is somehow equivalent to adding a column to the table of items.
     * The default value of the created properties is null.
     * The properties may be of various types:
     * - string
     * - double
     * - int
     * - boolean
     * - timestamp (UTC timestamp)
     * - set (a set of strings. Ecample, ["Electronics", "Televisions"])
     * @param $name - Name of the item property to be created. Currently, the following names are reserved:id, itemid, case insensitively.
     * Also, the length of the property name must not exceed 63 characters
     * @param $type - Value type of the item property to be created. One of: int, double, string, boolean, timestamp, set
     */
    public function addItemProperty($name, $type)
    {
        $response = $this->send(new Requests\AddItemProperty($name, $type));
        return $response;
    }

    /**
     * Deleting an item property is roughly equivalent to removing a column from the table of items
     * @param $propertyName
     */
    public function deleteItemProperty($propertyName)
    {
        $response = $this->send(new Requests\DeleteItemProperty($propertyName));
        return $response;
    }

    /**
     * Gets information about specified item property
     * @param $propertyName
     * @return mixed
     */
    public function getItemPropertyInfo($propertyName)
    {
        $response = $this->send(new Requests\GetItemPropertyInfo($propertyName));
        return $response;
    }

    /**
     * Gets the list of all the item properties in your database
     * @return mixed
     */
    public function getListItemProperties()
    {
        $response = $this->send(new Requests\ListItemProperties());
        return $response;
    }

    /**
     * The following methods allow assigning property values for items in the catalog
     *
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $values - Example,
     * [
     * "product_description" => "4K TV with 3D feature",
     * "categories" =>   ["Electronics", "Televisions"],
     * "price_usd" => 342,
     * "in_stock_from" => "2016-11-16T08:00Z"
     * ]
     * @param $cascadeCreate - The cascadeCreate indicates that the item of the given itemId should be created if it does not exist in the database
     */
    public function setItemPropertyValues($itemId, $values, $cascadeCreate = true)
    {
        $response = $this->send(new Requests\SetItemValues($itemId, $values, ["cascadeCreate" => $cascadeCreate]));
        return $response;
    }

    /**
     * Get all the current property values of a given item
     * @param $itemId
     * @return mixed
     */
    public function getItemValues($itemId)
    {
        $response = $this->send(new Requests\GetItemValues($itemId));
        return $response;
    }

    /**
     * Adds a new user to the database
     * @param $userId
     * @return mixed
     */
    public function addUser($userId)
    {
        $response = $this->send(new Requests\AddUser($userId));
        return $response;
    }

    /**
     * Deletes a user of given userId from the database.
     * If there are any purchases, ratings, bookmarks, cart additions or detail views made by the user present in the database, they will be deleted in cascade as well.
     * @param $userId
     * @return mixed
     */
    public function deleteUser($userId)
    {
        $response = $this->send(new Requests\DeleteUser($userId));
        return $response;
    }

    /**
     * Merges interactions (purchases, ratings, bookmarks, detail views ...) of two different users under a single user ID.
     * This is especially useful for online e-commerce applications working with anonymous users identified by unique tokens such as the session ID.
     * In such applications, it may often happen that a user owns a persistent account, yet accesses the system anonymously while, e.g., putting items into a shopping cart.
     * At some point in time, such as when the user wishes to confirm the purchase, (s)he logs into the system using his/her username and password.
     * The interactions made under anonymous session ID then become connected with the persistent account, and merging these two together becomes desirable.
     *
     * Merging happens between two users referred to as the target and the source.
     * After the merge, all the interactions of the source user are attributed to the target user,
     * and the source user is deleted unless special parameter keepSourceUser is set true.
     * @param $targetUserId
     * @param $sourceUserId
     * @param bool $cascadeCreate
     * @return mixed
     */
    public function mergeUsers($targetUserId, $sourceUserId, $cascadeCreate = true)
    {
        $response = $this->send(new Requests\MergeUsers($targetUserId, $sourceUserId, ['cascadeCreate' => $cascadeCreate]));
        return $response;
    }

    /**
     * @param array $optional
     *  'filter' => <string>, Boolean-returning ReQL expression, which allows you to filter users to be listed. Only the users for which the expression is true will be returned.
     *  'count' => <integer>, The number of users to be listed.
     *  'offset' => <integer>, Specifies the number of users to skip (ordered by userId).
     *  'returnProperties' => <boolean>, With returnProperties=true, property values of the listed users are returned along with their IDs in a JSON dictionary.
     *  'includedProperties' => <array> Allows to specify, which properties should be returned when returnProperties=true is set. The properties are given as a comma-separated list.
     * @return mixed
     */
    public function getListUsers($optional = [])
    {
        $response = $this->send(new Requests\ListUsers($optional));
        return $response;
    }

    /**
     * Adding an user property is somehow equivalent to adding a column to the table of users. The users may be characterized by various properties of different types
     * @param $propertyName
     * @param $type
     * @return mixed
     */
    public function addUserProperty($propertyName, $type)
    {
        $response = $this->send(new Requests\AddUserProperty($propertyName, $type));
        return $response;
    }

    /**
     * Deleting an user property is roughly equivalent to removing a column from the table of users
     * @param $propertyName
     * @return mixed
     */
    public function deleteUserProperty($propertyName)
    {
        $response = $this->send(new Requests\DeleteUserProperty($propertyName));
        return $response;
    }

    /**
     * Gets information about specified user property
     * @param $propertyName
     * @return mixed
     */
    public function getUserPropertyInfo($propertyName)
    {
        $response = $this->send(new Requests\GetUserPropertyInfo($propertyName));
        return $response;
    }

    /**
     * Gets the list of all the user properties in your database
     * @return mixed
     */
    public function getListUserProperties()
    {
        $response = $this->send(new Requests\ListUserProperties());
        return $response;
    }

    /**
     * Set/update (some) property values of a given user. The properties (columns) must be previously created by Add user property.
     * @param $userId
     * @param array $values
     * @param array $optional
     * @return mixed
     */
    public function setUserValues($userId, $values, $optional = [])
    {
        $response = $this->send(new Requests\SetUserValues($userId, $values, $optional));
        return $response;
    }

    /**
     * Get all the current property values of a given user
     * @param $userId
     * @return mixed
     */
    public function getUserProperties($userId)
    {
        $response = $this->send(new Requests\GetUserValues($userId));
        return $response;
    }

    /**
     * detail-view - it will be sent to the system every time a user views a detail of an item
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $optional -
     * 'timestamp' => <string / number>, UTC timestamp of the view as ISO8601-1 pattern or UTC epoch time. The default value is the current time.
     * 'duration' => <integer>, Duration of the view
     * 'cascadeCreate' => <boolean> tells the system that it should create the item or the user if it doesn’t exist in the system yet.
     * We don’t have to explicitly manage the user and item database in the system for now thanks to this parameter.
     */
    public function addDetailView($userId, $itemId, $optional = [])
    {
        $response = $this->send(new Requests\AddDetailView($userId, $itemId, $optional));
        return $response;
    }

    /**
     * Deletes an existing detail view uniquely specified by (userId, itemId, and timestamp) or all the detail views with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $timestamp - Unix timestamp of the detail view. If the timestamp is omitted, then all the detail views with given userId and itemId are deleted
     * @return mixed]
     */
    public function deleteDetailView($userId, $itemId, $timestamp)
    {
        $response = $this->send(new Requests\DeleteDetailView($userId, $itemId, ['timestamp' => $timestamp]));
        return $response;
    }

    /**
     * List all the detail views of a given item ever made by different users
     * @param $itemId
     * @return mixed
     */
    public function getListItemDetailView($itemId)
    {
        $response = $this->send(new Requests\ListItemDetailViews($itemId));
        return $response;
    }

    /**
     * Lists all the detail views of different items ever made by a given user
     * @param $userId
     * @return mixed
     */
    public function getListUserDetailView($userId)
    {
        $response = $this->send(new Requests\ListUserDetailViews($userId));
        return $response;
    }

    /**
     * Sending a purchase
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $timestamp
     * @param $cascadeCreate - tells the system that it should create the item or the user if it doesn’t exist in the system yet.
     */
    public function addPurchase($userId, $itemId, $timestamp, $cascadeCreate = true)
    {
        $response = $this->send(new Requests\AddPurchase($userId, $itemId, ['timestamp' => $timestamp, 'cascadeCreate' => $cascadeCreate]));
        return $response;
    }

    /**
     * Deletes an existing purchase uniquely specified by userId, itemId, and timestamp or all the purchases with given userId and itemId if timestamp is omitted.
     * @param $userId
     * @param $itemId
     * @param $optional ['timestamp' => <number>]
     * @return mixed
     */
    public function deletePurchase($userId, $itemId, $optional = [])
    {
        $response = $this->send(new Requests\DeletePurchase($userId, $itemId, $optional));
        return $response;
    }

    /**
     * Adds a rating of given item made by a given user
     * @param $userId
     * @param $itemId
     * @param $rating - Rating rescaled to interval [-1.0,1.0], where -1.0 means the worst rating possible, 0.0 means neutral, and 1.0 means absolutely positive rating.
     *                  For example, in the case of 5-star evaluations, rating = (numStars-3)/2 formula may be used for the conversion.
     * @param array $optional
     * @return mixed
     */
    public function addRating($userId, $itemId, $rating, $optional = [])
    {
        $response = $this->send(new Requests\AddRating($userId, $itemId, $rating, $optional));
        return $response;
    }

    /**
     * Deletes an existing rating specified by (userId, itemId, timestamp) from the database or all the ratings with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $optional - ['timestamp' => <number>]
     * @return mixed
     */
    public function deleteRating($userId, $itemId, $optional)
    {
        $response = $this->send(new Requests\DeleteRating($userId, $itemId, $optional));
        return $response;
    }

    /**
     * Adds a cart addition of a given item made by a given user
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $timestamp
     * @param $cascadeCreate - tells the system that it should create the item or the user if it doesn’t exist in the system yet.
     */
    public function addCartAddition($userId, $itemId, $timestamp, $cascadeCreate = true)
    {
        $response = $this->send(new Requests\AddCartAddition($userId, $itemId, ['timestamp' => $timestamp, 'cascadeCreate' => $cascadeCreate]));
        return $response;
    }

    /**
     * Deletes an existing cart addition uniquely specified by userId, itemId, and timestamp or all the cart additions with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $optional ['timestamp' => <number>]
     * @return mixed
     */
    public function deleteCartAddition($userId, $itemId, $optional = [])
    {
        $response = $this->send(new Requests\DeleteCartAddition($userId, $itemId, $optional));
        return $response;
    }

    /**
     * Adds a bookmark of a given item made by a given user
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $timestamp
     * @param $cascadeCreate - tells the system that it should create the item or the user if it doesn’t exist in the system yet.
     */
    public function addBookmark($userId, $itemId, $timestamp, $cascadeCreate = true)
    {
        $response = $this->send(new Requests\AddBookmark($userId, $itemId, ['timestamp' => $timestamp, 'cascadeCreate' => $cascadeCreate]));
        return $response;
    }

    /**
     * Deletes a bookmark uniquely specified by userId, itemId, and timestamp or all the bookmarks with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $optional ['timestamp' => <number>]
     * @return mixed
     */
    public function deleteBookmark($userId, $itemId, $optional = [])
    {
        $response = $this->send(new Requests\DeleteBookmark($userId, $itemId, $optional));
        return $response;
    }

    /**
     * user-based recommendation - System recommends items to a given user depending on his/her personal taste.
     * This case can be used for example on your homepage
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $countRecommendation
     * @param $filters = [
     *    'filter' => <string>(Example, "not 'deleted' and \"Electronics\" in 'categories'"),
     *    'booster' => <string>, Number-returning ReQL expression which allows you to boost recommendation rate of some items based on the values of their attributes.
     *    'cascadeCreate' => <boolean>, If the user does not exist in the database, returns a list of non-personalized recommendations and creates the user in the database.
     *                                  This allows for example rotations in the following recommendations for that user, as the user will be already known to the system
     *    'scenario' => <string>, Scenario defines a particular application of recommendations. It can be for example “homepage”, “cart” or “emailing”.
     *                            You can see each scenario in the UI separately, so you can check how well each application performs.
     *                            The AI which optimizes models in order to get the best results may optimize different scenarios separately, or even use different models in each of the scenarios.
     *    'returnProperties' => <boolean>, With returnProperties=true, property values of the recommended items are returned along with their IDs in a JSON dictionary.
     *                                     The acquired property values can be used for easy displaying of the recommended items to the user.
     *    'includedProperties' => <array>, Allows to specify, which properties should be returned when returnProperties=true is set. The properties are given as a comma-separated list
     *                                     Example, includedProperties=description,price
     *    'diversity' => <number>, Expert option
     *    'minRelevance' => <string>, Expert option
     *    'rotationRate' => <number>, Expert option
     *    'rotationTime' => <number> Expert option
     * ]
     * @return array (Example, ["item-865", "item-460", "item-121", "item-1555", "item-683"])
     */
    public function getUserBasedRecommendation($userId, $countRecommendation, $filters = [])
    {
        $recommended = $this->send(new Requests\UserBasedRecommendation($userId, $countRecommendation, $filters));
        return $recommended;
    }

    /**
     * item-based recommendation - System recommends items that are somehow related to a given item.
     * System can take into account also a target user, so this case is useful for example in a detail page of a product,
     * because item-based will give the user a list of related items that he/she might be also interested in.
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $countRecommendation
     * @param $filters = [
     *    'targetUserId' => <string>, ID of the user who will see the recommendations
     *    'userImpact' => <number>, If targetUserId parameter is present, the recommendations are biased towards the user given. Using userImpact, you may control this bias.
     *                              For an extreme case of userImpact=0.0, the interactions made by the user are not taken into account at all (with the exception of history-based blacklisting),
     *                              for userImpact=1.0, you’ll get user-based recommendation. The default value is 0
     *    'filter' => <string>, Boolean-returning ReQL expression which allows you to filter recommended items based on the values of their attributes.
     *    'booster' => <string>, Number-returning ReQL expression which allows you to boost recommendation rate of some items based on the values of their attributes.
     *    'cascadeCreate' => <boolean>, If item of given itemId or user of given targetUserId doesn’t exist in the database, it creates the missing enity/entities and returns some (non-personalized) recommendations.
     *                                      This allows for example rotations in the following recommendations for the user of given targetUserId, as the user will be already known to the system.
     *    'scenario' => <string>, Scenario defines a particular application of recommendations. It can be for example “homepage”, “cart” or “emailing”.
     *                            You can see each scenario in the UI separately, so you can check how well each application performs.
     *                            The AI which optimizes models in order to get the best results may optimize different scenarios separately, or even use different models in each of the scenarios.
     *    'returnProperties' => <boolean>, With returnProperties=true, property values of the recommended items are returned along with their IDs in a JSON dictionary.
     *                                     The acquired property values can be used for easy displaying of the recommended items to the user
     *    'includedProperties' => <array>, Allows to specify, which properties should be returned when returnProperties=true is set.
     *                                     The properties are given as a comma-separated list. Example, includedProperties=description,price
     *    'diversity' => <number>, Expert option
     *    'minRelevance' => <string>, Expert option
     *    'rotationRate' => <number>, Expert option
     *    'rotationTime' => <number> Expert option
     * ]
     * @return array (Example, ["item-865", "item-460", "item-121", "item-1555", "item-683"])
     */
    public function getItemBasedRecommendation($itemId, $countRecommendation, $filters = [])
    {
        $recommended = $this->send(new Requests\ItemBasedRecommendation($itemId, $countRecommendation, $filters));
        return $recommended;
    }

    /**
     * List all the ever-made purchases of a given item
     * @param $itemId
     * @return mixed
     */
    public function getListPurchaseByItemId($itemId)
    {
        $response = $this->send(new Requests\ListItemPurchases($itemId));
        return $response;
    }

    /**
     * List all the purchases ever made by a given user
     * @param $userId
     * @return mixed
     */
    public function getListPurchaseByUserId($userId)
    {
        $response = $this->send(new Requests\ListUserPurchases($userId));
        return $response;
    }



    /**
     * Export purchase from csv
     * @param $filePath
     */
    public function exportPurchaseFromCsv($filePath)
    {
        $purchases = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($row = fgetcsv($handle)) !== FALSE) {
                $userId = $row[0];
                $itemId = $row[1];
                $time = $row[2];

                $r = new Requests\AddPurchase($userId, $itemId, ['timestamp' => $time, 'cascadeCreate' => true]);
                array_push($purchases, $r);
            }
            fclose($handle);
        }

        $br = new Requests\Batch($purchases);
        $response = $this->send($br);

        return $response;
    }

    /**
     * Delete purchase by csv from recombee
     * @param $filePath
     */
    public function deletePurchaseByCsvFromRecombee($filePath)
    {
        $purchases = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($row = fgetcsv($handle)) !== FALSE) {
                $userId = $row[0];
                $itemId = $row[1];
                $time = $row[2];

                $r = new Requests\DeletePurchase($userId, $itemId, ['timestamp' => $time]);
                array_push($purchases, $r);
            }
            fclose($handle);
        }

        $br = new Requests\Batch($purchases);
        $response = $this->send($br);

        return $response;
    }

    private function send($object)
    {
        try {
            $response = $this->client->send($object);
            return $response;
        } catch(Exceptions\ApiTimeoutException $e)
        {
            //Handle timeout => use fallback
        }
        catch(Exceptions\ResponseException $e)
        {
            echo "Код ошибки: " . $e->status_code . '.' . PHP_EOL;
            $description = json_decode($e->description);
            echo "Сообщение: " . $description->message . PHP_EOL;
        }
        catch(Exceptions\ApiException $e)
        {
            //ApiException is parent of both ResponseException and ApiTimeoutException
        }
    }

}
