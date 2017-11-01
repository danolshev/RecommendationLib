###**Examples**
```php
   $recommendation = new RecommendationLib('recombee', 'sdfdsagsd8ay7ohgeao7rg8oag68reg', 'db-name');
   
   $recommendation->addItem('first-1');
   
   $listItems = $recommendation->getListItems();
   
   $recommendation->addDetailView('first-1', 'new-item-1', ['timestamp' => "2014-07-20T02:49:45+02:00", 'cascadeCreate' => true]);
   
   $recommendation->AddPurchase("2c169e575644d840838e", "xyz", ['timestamp' => "2015-09-10T04:29:55+02:00", 'cascadeCreate' => true]);
   
```