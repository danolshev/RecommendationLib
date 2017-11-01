<?php

namespace Recommendation;

interface IRecommendation
{
    /**
     * Adds new item of given itemId to the items catalog.
     * All the item properties for the newly created items are set null
     * @param $itemId
     */
    public function addItem($itemId);

    /**
     * Deletes an item of given itemId from the catalog. If there are any purchases, ratings, bookmarks,
     * cart additions or detail views of the item present in the database, they will be deleted in cascade as well.
     * Also, if the item is present in some series, it will be removed from all the series where present.
     * If an item becomes obsolete/no longer available, it is often meaningful to keep it in the catalog (along with all the interaction data, which are very useful),
     * and only exclude the item from recommendations. In such a case, use ReQL filter instead of deleting the item completely.
     * @param $itemId
     */
    public function deleteItem($itemId);

    /**
     * The following methods allow you to maintain the set of items in the catalog.
     * @param $optional
     * Example,
     * 'filter' => <string>,
     * 'count' => <integer>,
     * 'offset' => <integer>,
     * 'returnProperties' => <boolean>,
     * 'includedProperties' => <array>
     */
    public function getListItems($optional = []);

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
    public function addItemProperty($name, $type);

    /**
     * Deleting an item property is roughly equivalent to removing a column from the table of items
     * @param $propertyName
     */
    public function deleteItemProperty($propertyName);

    /**
     * Gets information about specified item property
     * @param $propertyName
     */
    public function getItemPropertyInfo($propertyName);
    /**
     * Gets the list of all the item properties in your database
     */
    public function getListItemProperties();

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
    public function setItemPropertyValues($itemId, $values, $cascadeCreate = true);
    /**
     * Get all the current property values of a given item
     * @param $itemId
     */
    public function getItemValues($itemId);

    /**
     * Adds a new user to the database
     * @param $userId
     */
    public function addUser($userId);

    /**
     * Deletes a user of given userId from the database.
     * If there are any purchases, ratings, bookmarks, cart additions or detail views made by the user present in the database, they will be deleted in cascade as well.
     * @param $userId
     */
    public function deleteUser($userId);

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
     */
    public function mergeUsers($targetUserId, $sourceUserId, $cascadeCreate = true);

    /**
     * @param array $optional
     *  'filter' => <string>, Boolean-returning ReQL expression, which allows you to filter users to be listed. Only the users for which the expression is true will be returned.
     *  'count' => <integer>, The number of users to be listed.
     *  'offset' => <integer>, Specifies the number of users to skip (ordered by userId).
     *  'returnProperties' => <boolean>, With returnProperties=true, property values of the listed users are returned along with their IDs in a JSON dictionary.
     *  'includedProperties' => <array> Allows to specify, which properties should be returned when returnProperties=true is set. The properties are given as a comma-separated list.
     */
    public function getListUsers($optional = []);
    /**
     * Adding an user property is somehow equivalent to adding a column to the table of users. The users may be characterized by various properties of different types
     * @param $propertyName
     * @param $type
     */
    public function addUserProperty($propertyName, $type);

    /**
     * Deleting an user property is roughly equivalent to removing a column from the table of users
     * @param $propertyName
     */
    public function deleteUserProperty($propertyName);

    /**
     * Gets information about specified user property
     * @param $propertyName
     */
    public function getUserPropertyInfo($propertyName);

    /**
     * Gets the list of all the user properties in your database
     */
    public function getListUserProperties();

    /**
     * Set/update (some) property values of a given user. The properties (columns) must be previously created by Add user property.
     * @param $userId
     * @param array $values
     * @param array $optional
     */
    public function setUserValues($userId, $values, $optional = []);

    /**
     * Get all the current property values of a given user
     * @param $userId
     */
    public function getUserProperties($userId);

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
    public function addDetailView($userId, $itemId, $optional = []);

    /**
     * Deletes an existing detail view uniquely specified by (userId, itemId, and timestamp) or all the detail views with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $timestamp - Unix timestamp of the detail view. If the timestamp is omitted, then all the detail views with given userId and itemId are deleted
     */
    public function deleteDetailView($userId, $itemId, $timestamp);

    /**
     * List all the detail views of a given item ever made by different users
     * @param $itemId
     */
    public function getListItemDetailView($itemId);

    /**
     * Lists all the detail views of different items ever made by a given user
     * @param $userId
     */
    public function getListUserDetailView($userId);

    /**
     * Sending a purchase
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $timestamp
     * @param $cascadeCreate - tells the system that it should create the item or the user if it doesn’t exist in the system yet.
     */
    public function addPurchase($userId, $itemId, $timestamp, $cascadeCreate = true);

    /**
     * Deletes an existing purchase uniquely specified by userId, itemId, and timestamp or all the purchases with given userId and itemId if timestamp is omitted.
     * @param $userId
     * @param $itemId
     * @param $optional ['timestamp' => <number>]
     */
    public function deletePurchase($userId, $itemId, $optional = []);

    /**
     * Adds a rating of given item made by a given user
     * @param $userId
     * @param $itemId
     * @param $rating - Rating rescaled to interval [-1.0,1.0], where -1.0 means the worst rating possible, 0.0 means neutral, and 1.0 means absolutely positive rating.
     *                  For example, in the case of 5-star evaluations, rating = (numStars-3)/2 formula may be used for the conversion.
     * @param array $optional
     */
    public function addRating($userId, $itemId, $rating, $optional = []);

    /**
     * Deletes an existing rating specified by (userId, itemId, timestamp) from the database or all the ratings with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $optional - ['timestamp' => <number>]
     */
    public function deleteRating($userId, $itemId, $optional);

    /**
     * Adds a cart addition of a given item made by a given user
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $timestamp
     * @param $cascadeCreate - tells the system that it should create the item or the user if it doesn’t exist in the system yet.
     */
    public function addCartAddition($userId, $itemId, $timestamp, $cascadeCreate = true);

    /**
     * Deletes an existing cart addition uniquely specified by userId, itemId, and timestamp or all the cart additions with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $optional ['timestamp' => <number>]
     */
    public function deleteCartAddition($userId, $itemId, $optional = []);

    /**
     * Adds a bookmark of a given item made by a given user
     * @param $userId - is an unique identifier of the user. It might be for example a session ID for anonymous users
     * @param $itemId - is an unique identifier of the item, it may consist of digits, Latin letters, underscores, colons and minus signs
     * @param $timestamp
     * @param $cascadeCreate - tells the system that it should create the item or the user if it doesn’t exist in the system yet.
     */
    public function addBookmark($userId, $itemId, $timestamp, $cascadeCreate = true);

    /**
     * Deletes a bookmark uniquely specified by userId, itemId, and timestamp or all the bookmarks with given userId and itemId if timestamp is omitted
     * @param $userId
     * @param $itemId
     * @param $optional ['timestamp' => <number>]
     */
    public function deleteBookmark($userId, $itemId, $optional = []);

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
     */
    public function getUserBasedRecommendation($userId, $countRecommendation, $filters = []);

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
     */
    public function getItemBasedRecommendation($itemId, $countRecommendation, $filters = []);

    /**
     * List all the ever-made purchases of a given item
     * @param $itemId
     */
    public function getListPurchaseByItemId($itemId);

    /**
     * List all the purchases ever made by a given user
     * @param $userId
     */
    public function getListPurchaseByUserId($userId);



    /**
     * Export purchase from csv
     * @param $filePath
     */
    public function exportPurchaseFromCsv($filePath);

    /**
     * Delete purchase by csv from recombee
     * @param $filePath
     */
    public function deletePurchaseByCsvFromRecombee($filePath);

}

