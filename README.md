Select MySQL Class for PHP
========================

PHP Class to build queries for MySQL. Allows you to build chains of conditions. The goal of this class is to 
encapsulate PHP's interactions with MySQL to reduce errors in writing queries and make creating stored procedures simple.

Requirements
------------

This project requires PHP 5.5+ and MySQL

### Documentation ###

[Getting Started: Examples and Documentation](docs/GETTING_STARTED.md)

### functions ###
#### ->getInsertId() ####
Gets the Last Insert ID after you insert a row

### Comparison List ###
#### ->eq(field, value) ####
Compare the field to the value

#### ->notEq(field, value) ####
Compare the field to the value and make sure it does not match

#### ->eqConditional(field, value) ####
Compare the value to the value. If the value is an empty string, then this comparison will be dropped from the query silently

#### ->eqNull(field) ####
Compare the field to NULL

#### ->eqNotNull(field) ####
Compare the field to NOT NULL

#### ->eqRequired(field, value) ####
Compare the field to the value. If the value is an empty string, then this will force an 1 = 0 in the query and cause the query to return no rows

#### ->gt(field, value) ####
If the value greater than the field

#### ->gte(field, value) ####
If the value greater than the field or equal to the field

#### ->in(field, value) ####
Performs an IN clause, value can be String[], Subquery SQL, or another Select Class object

#### ->notIn(field, value) ####
Inverse of in(). Performs an NOT IN clause, value can be String[], Subquery SQL, or another Select Class object

#### ->like(field, value) ####
Wildcard search to see if the value is in the string. Defaults to adding left and right wildcard, only if % is not present in the value 

#### ->lt(field, value) ####
If the value less than the field

#### ->lte(field, value) ####
If the value less than the field or equal to the field

### Conjunction Groups ###
#### ->startOr() ####
Begins an Or() condition grouping. All comparison after this are joined using OR rather than AND. All wrapped in parenthese

#### ->endOr() ####
Ends a startOr() condition grouping

#### ->startAnd() ####
Default Grouping, only needed if inside of an Or() grouping. Begins an And() condition grouping. All comparison after this are joined using OR rather than AND. All wrapped in parenthese

#### ->endAnd() ####
Ends a startAnd() condition grouping. Cannot end the outer most grouping.

### Sorting & Grouping ###
#### ->group(<fields>) ####
The GROUP BY value e.g. 'Date DESC' or 'LastName ASC, FirstName ASC' 

#### ->order(value) ####
The ORDER BY value e.g. 'LastName' or 'LastName, FirstName'

#### ->offset(value) ####
The Integer to offset the results by

#### ->limit(value) ####
The number of results to limit the return to
