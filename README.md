Select MySQL Class for PHP
========================

The official anime detour application.

Requirements
------------

This project requires PHP 5.5+ and MySQL

### Examples ###

To use this class to get all entries in the User table;

#### Simple extraction ####
    $table   = 'User';
    $select  = new select\Select($table);
    $results = $select->execute->getRows(); // returns a generator
    foreach ($results as $row) {
        // $row data
    }

#### With 1 condition ####
    $table   = 'User';
    $select  = new select\Select($table);
    $select->eq('FirstName', 'John'); // Only show users who's firstname is John
    $results = $select->execute->getRows(); // returns a generator
    foreach ($results as $row) {
        // $row data
    }

#### With condition chain ####
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
