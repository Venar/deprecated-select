Getting Started
========================

This is a guide to help you have your first Select class

#### Simple extraction ####
```php
$table   = 'User';
$select  = new select\Select($table);
$results = $select->execute->getRows(); // returns a generator
foreach ($results as $row) {
	// $row data
}
```

#### With 1 condition ####
```php
$table   = 'User';
$select  = new select\Select($table);
$select->eq('FirstName', 'John'); // Only show users who's firstname is John
$results = $select->execute->getRows(); // returns a generator
foreach ($results as $row) {
    // $row data
}
```

#### With condition chain ####
```php
$table   = 'User';
$select  = (new select\Select($table))
              ->eq('FirstName', 'John') 
              ->startOr()
                  ->eq('LastName', 'Koniges')
                  ->eq('LastName', 'Smith')
              ->endOr()
              ->eq('Disabled', 'false');
$results = $select->execute->getRows(); // returns a generator
foreach ($results as $row) {
    // $row data
}
```
