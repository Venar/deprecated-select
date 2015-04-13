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

#### With conditions ####
    $table   = 'User';
    $select  = new select\Select($table);
    $select->eq('FirstName', 'John'); // Only show users who's firstname is John
    $results = $select->execute->getRows(); // returns a generator
    foreach ($results as $row) {
        // $row data
    }
