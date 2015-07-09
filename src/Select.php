<?php
/*
 * This file is part of John Koniges' Select class
 * https://github.com/Venar/select
 *
 * Copyright (c) 2015 John J. Koniges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace select;

class Select
{
    /* @var $pdo \PDO */
    protected $pdo;
    protected $booleanTrue = 1;
    protected $booleanFalse = 0;
    private $statement = '';
    private $whereClause = '';
    private $setFields = array();
    private $setValues = array();
    private $setTmp = array();
    private $offset = null;
    private $limit = null;
    private $groupBy = null;
    private $orderBy = null;
    private $params = array();
    private $type = null;
    private $uuid = null;
    private $currentPlaceholder = 0;

    // If your database uses enum for true false, or other values overwrite them here in your class
    private $where_mode = array();
    /* @var $result \PDOStatement */
    private $result;

    /**
     * Example:
     * $select = new Select('SELECT * FROM Test');
     *
     * @param string $sql You can pass in a whole SQL command, or the start of one
     * @param string $type The kind of QUERY this is: SELECT, UPDATE, INSERT, DELETE
     * @param \PDO $pdo Your PDO class object of a valid connection to the database
     */
    public function __construct($sql, $type = 'SELECT', $pdo = null)
    {
        $this->uuid = uniqid('param_');
        $this->where_mode[] = 'AND';
        $this->type = $type;

        switch ($type) {
            case 'UPDATE':
                $this->statement = 'UPDATE ' . $sql . '';
                break;
            case 'INSERT':
                $this->statement = 'INSERT INTO ' . $sql . '';
                break;
            case 'DELETE':
                $this->statement = 'DELETE FROM ' . $sql . '';
                break;
            case 'SELECT':
            default:
                $this->statement = $sql;
                if (strpos($sql, ' ') === false) {
                    $this->statement = 'SELECT * FROM ' . $sql . '';
                }
                break;
        }

        if ($pdo instanceof \PDO) {
            $this->pdo = $pdo;
        }
    }

    /**
     * Closes an and control group
     *
     * @return \select\Select
     */
    public function endAnd()
    {
        $conjunctionType = end($this->where_mode);
        reset($this->where_mode);
        if ($conjunctionType != 'AND') {
            return $this;
        }

        $this->endConjunction(false);

        return $this;
    }

    /**
     * This end the current control group conjunction. Removing the starting ( if no data inside it.
     *
     * @param $finalRemoval bool Allow this to be the final time that we are removing a conjunction
     */
    private function endConjunction($finalRemoval = false)
    {
        if (!$finalRemoval && count($this->where_mode) == 1) {
            // We cannot end the final conjunction
            return;
        }

        // MySQL will error if it sees a (), this is to remove those cases
        if (substr($this->whereClause, -1) == "(") {
            // We remove the last two characters since when we added it, it was a space then (
            $this->whereClause = substr($this->whereClause, 0, -2);
        } else {
            $this->whereClause .= ")";
        }
        array_pop($this->where_mode);
    }

    /**
     * Closes an or control group
     *
     * @return \select\Select
     */
    public function endOr()
    {
        $conjunctionType = end($this->where_mode);
        reset($this->where_mode);
        if ($conjunctionType != 'OR') {
            return $this;
        }

        $this->endConjunction(false);

        return $this;
    }

    /**
     * Is the value greater than the field
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return Select
     */
    public function gt($field, $value, $type = \PDO::PARAM_INT)
    {
        if (trim($value) != "" || is_bool($value)) {
            $this->addConjunction();
            $this->whereClause .= ' ' . $field . ' > ' . $this->addParam($value, $type);
        }

        return $this;
    }

    /**
     * This adds an AND or OR between fields based on what is the current type.
     */
    private function addConjunction()
    {
        if ($this->whereClause != '' && substr($this->whereClause, -1) != '(') {
            $this->whereClause = rtrim($this->whereClause) . " \n " . end($this->where_mode) . " ";
        }
    }

    /**
     * This creates a param object and generates a palceholder for it
     *
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return string The Placeholder string for the prepared statement
     */
    private function addParam($value, $type = \PDO::PARAM_STR)
    {
        $param = new Param($value, $type);
        $param->placeholder = $this->generatePlaceholder();
        $this->params[] = $param;

        return ':' . $param->placeholder;
    }

    /**
     * This is the placeholder string for each variable
     * @return string
     */
    private function generatePlaceholder()
    {
        return $this->uuid . $this->currentPlaceholder++;
    }

    /**
     * Is the value greater than or equal to the field
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return Select
     */
    public function gte($field, $value, $type = \PDO::PARAM_INT)
    {
        if (trim($value) != "" || is_bool($value)) {
            $this->addConjunction();
            $this->whereClause .= ' ' . $field . ' >= ' . $this->addParam($value, $type);
        }

        return $this;
    }

    /**
     * If value is an empty string this field is not added to the query.
     *
     * This performs a comparison, however does not always add to the query. Use this if you have a large quantity
     *   of form fields and want to check them all in your query but don't want to see if they are empty values
     *   every time before you use them.
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return \select\Select
     */
    public function eqConditional($field, $value, $type = \PDO::PARAM_STR)
    {
        if (trim($value) != "" || is_bool($value)) {
            // Convert a bool into the literal strings used by enum in the db layer
            if (is_bool($value)) {
                $value = $value ? $this->$booleanTrue : $this->$booleanFalse;
            }
            $this->SetConnection();
            $this->whereClause .= ' ' . $field . ' = ' . $this->addParam($value, $type);
        }

        return $this;
    }

    /**
     * This class will let a class extending this to set their own PDO object automatically without passing it in
     *   the constructor.
     * Classes that extend this class should overwrite this method with their default way to get a db object.
     */
    protected function setConnection()
    {
        //
        return;
    }

    /**
     * Is this field null?
     *
     * @param string $field What is the Table field
     * @return \select\Select
     */
    public function eqNull($field)
    {
        $this->SetConnection();
        $this->whereClause .= ' ' . $field . ' IS NULL ';

        return $this;
    }

    /**
     * Is this field null?
     *
     * @param string $field What is the Table field
     * @return \select\Select
     */
    public function eqNotNull($field)
    {
        $this->SetConnection();
        $this->whereClause .= ' ' . $field . ' IS NOT NULL ';

        return $this;
    }

    /**
     * similar to eq() however a blank value will cause the query to fail. This is used for required fields
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return \select\Select
     */
    public function eqRequired($field, $value, $type = \PDO::PARAM_STR)
    {
        if (trim($value) != "" || is_bool($value)) {
            $this->eq($field, $value, $type);
        } else {
            $this->SetConnection();
            $this->whereClause .= ' 1 = 0';
        }

        return $this;
    }

    /**
     * Compare the two values.
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return \select\Select
     */
    public function eq($field, $value, $type = \PDO::PARAM_STR)
    {
        // Convert a bool into the literal strings used by enum in the db layer
        if (is_bool($value)) {
            $value = $value ? $this->$booleanTrue : $this->$booleanFalse;
        }
        $this->SetConnection();
        $this->whereClause .= ' ' . $field . ' = ' . $this->addParam($value, $type);

        return $this;
    }

    /**
     * Is the value less than the field
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return Select
     */
    public function lt($field, $value, $type = \PDO::PARAM_INT)
    {
        if (trim($value) != "" || is_bool($value)) {
            $this->addConjunction();
            $this->whereClause .= ' ' . $field . ' < ' . $this->addParam($value, $type);
        }

        return $this;
    }

    /**
     * Is the value less than or equal to the field
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return Select
     */
    public function lte($field, $value, $type = \PDO::PARAM_INT)
    {
        if (trim($value) != "" || is_bool($value)) {
            $this->addConjunction();
            $this->whereClause .= ' ' . $field . ' <= ' . $this->addParam($value, $type);
        }

        return $this;
    }

    /**
     * This executes the query and does the PDO Statements.
     *
     * @throws \select\SelectException
     * @return \select\Select
     */
    public function execute()
    {
        if (!$this->connect()) {
            throw new SelectException('Could not connect to database.');
        }
        //var_dump($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)); echo '<br><br><br>';

        // If someone didn't close all control groups, we close them for them
        // 1 is always left in the stack as the base, so we don't pop that off
        while (count($this->where_mode) > 1) {
            $this->endConjunction(true);
        }

        $query = $this->getQuery();

        // We run all SQL as a prepared statement
        $stmt = $this->pdo->prepare($query);
        foreach ($this->params as $param) {
            /* @var $param Param */
            // We bind the parameters which replaces their placeholders in the string.
            $stmt->bindParam(':' . $param->placeholder, $param->value, $param->type);
        }

        // $stmt->execute() returns a bool if it worked or not... we will extend use later
        $result = $stmt->execute();
        if (!$result) {
            $errorCode = $stmt->errorCode();
            $errorString = $stmt->errorInfo()[2];
            throw new SelectException($errorString, $errorCode);
        }
        // The $stmt now has the results and we stash those
        $this->result = $stmt;

        return $this;
    }

    /**
     * Creates a PDO connection to the database if one is not already defined
     *
     * @return bool
     */
    private function connect()
    {
        if (!$this->pdo instanceof \PDO) {
            $this->SetConnection();
        }

        if ($this->pdo instanceof \PDO) {
            return true;
        }

        return false;
    }

    /**
     * Generates the query that will be used
     *
     * @return string
     */
    public function getQuery()
    {
        // If the where clause has data, then we include the WHERE, otherwise we leave it off
        $query = $this->statement;

        if (count($this->setFields) > 0) {
            if (count($this->setTmp) > 0) {
                // This will throw an exception if they didn't properly set all the values to have the same quantity
                $this->setNext();
            }
            $query .= '(' . implode(', ', array_keys($this->setFields)) . ') VALUES ';
            $values = array();
            foreach ($this->setValues as $row) {
                $values[] = '(' . implode(', ', $row) . ')';
            }
            $query .= implode(', ', $values);
        }

        if ($this->whereClause != '') {
            $query .= ' WHERE ' . $this->whereClause;
        }

        if (!is_null($this->groupBy)) {
            $query .= ' GROUP BY ' . $this->groupBy;
        }

        if (!is_null($this->orderBy)) {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        if (!is_null($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
        }

        if (!is_null($this->offset)) {
            $query .= ' OFFSET ' . $this->offset;
        }

        return $query;
    }

    /**
     * The current values set by set, are confirmed
     *
     * @throws \select\SelectException
     * @return \select\Select
     */
    public function setNext()
    {
        $setTmpCount = count($this->setTmp);
        if (count($this->setValues) > 0 && count($this->setValues[0]) != $setTmpCount) {
            throw new SelectException('All rows being added or updated must have the same number of values.');
        }

        if (count($this->setValues) > 0 && count(array_diff_key($this->setFields, $this->setValues[0])) > 0) {
            throw new SelectException('All rows must be using the same fields for inserting.');
        }

        $this->setValues[] = $this->setTmp;
        $this->setTmp = array();

        return $this;
    }

    /**
     * This will last insert ID.
     *
     * @return array
     */
    public function getInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * This will return an MD Array of ALL of the results. Does not use a generator, PHP 5.5 safe.
     *
     * @param int $fetchType
     * @return array
     */
    public function fetchAllRows($fetchType = \PDO::FETCH_ASSOC)
    {
        $results = array();
        if (!$this->result instanceof \PDOStatement) {
            return $results;
        }

        try {
            $results = $this->result->FetchAll($fetchType);
        } finally {
            $this->result->closeCursor();
        }

        return $results;
    }

    /**
     * This will return an MD Array of ALL of the results.
     *
     * @param int $fetchType
     * @return \Generator
     */
    public function getRows($fetchType = \PDO::FETCH_ASSOC)
    {
        try {
            while ($resultSet = $this->result->fetch($fetchType)) {
                yield $resultSet;
            }
        } finally {
            $this->result->closeCursor();
        }
    }

    /**
     * This will return the count of all the rows founds
     *
     * @return int
     */
    public function getRowCount()
    {
        if (!$this->result instanceof \PDOStatement) {
            return 0;
        }

        return $this->result->rowCount();
    }

    /**
     * This will only return the value of the first row in the first record
     * Useful for getting one item returns from SQL
     *
     * @return array
     */
    public function getSingleItem()
    {
        $result = null;
        if ($this->result instanceof \PDOStatement) {
            $results = $this->result->FetchAll();
            if (array_key_exists(0, $results)) {
                // Get the first value of the first record
                $value = current($results[0]);
                if ($value !== false) {
                    $result = $value;
                }
            }
        }

        return $result;
    }

    /**
     * This will returns the next row
     *
     * @param int $fetchType
     * @return false|array
     */
    public function getRow($fetchType = \PDO::FETCH_ASSOC)
    {
        if (!$this->result instanceof \PDOStatement) {
            return false;
        }

        return $this->result->fetch($fetchType);
    }

    /**
     * Sets the grouping of the Query
     *
     * @param string $group_by What fields should be grouped by?
     * @return \select\Select
     */
    public function group($group_by)
    {
        $this->groupBy = $group_by;

        return $this;
    }

    /**
     * Takes in multiple types
     * 1) array() - An array of all values to look for, this is slower the larger the array
     * 2) String  - This is a query to run, you should not use any variables to protect against injection
     * 3) Select object (NOT YET IMPLIMENTED)
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return \select\Select
     */
    public function notIn($field, $value, $type = \PDO::PARAM_STR)
    {
        return $this->in($field, $value, $type, true);
    }

    /**
     * Takes in multiple types
     * 1) array() - An array of all values to look for, this is slower the larger the array
     * 2) String  - This is a query to run, you should not use any variables to protect against injection
     * 3) Select object (NOT YET IMPLIMENTED)
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @param boolean $not What variable type is this?
     * @return \select\Select
     */
    public function in($field, $value, $type = \PDO::PARAM_STR, $not = false)
    {
        $not_string = '';
        if (!$not) {
            $not_string = ' NOT ';
        }

        if (is_array($value)) {
            $subselect = '';
            foreach ($value as $subvalue) {
                if ($subselect != '') {
                    $subselect .= ', ';
                }
                $subselect .= $this->addParam($subvalue, $type);
            }
            $this->SetConnection();
            $this->whereClause .= ' ' . $field . $not_string . ' IN (' . $subselect . ')';
        } else {
            if ($value instanceof Select) {
                $sql = $value->getQuery();
                $this->params = array_merge($this->params, $value->GetParams());
                $this->SetConnection();
                $this->whereClause .= ' ' . $field . $not_string . ' IN (' . $sql . ')';
            } else {
                $this->SetConnection();
                $this->whereClause .= ' ' . $field . $not_string . ' IN (' . $value . ')';
            }
        }

        return $this;
    }

    /**
     * Returns an array for all parameters tracked by this object.
     *
     * This is used by this class when you pass another Select class in as part of an in clause. This allows it to
     *   only do one abstraction.
     *
     * @return Param[] returns an Array of the Params for this object.
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * If value is an empty string this field is not added to the query
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return \select\Select
     */
    public function like($field, $value, $type = \PDO::PARAM_STR)
    {
        if (trim($value) != "") {
            if (strpos($value, '%') === false) {
                $value = '%' . $value . '%';
            }

            // Convert a bool into the literal strings used by enum in the db layer
            $this->SetConnection();
            $this->whereClause .= ' ' . $field . ' LIKE ' . $this->addParam($value, $type);
        }

        return $this;
    }

    /**
     * Sets the limit of the Query
     *
     * @param int $limit What is limit of rows that should be returned
     * @return \select\Select
     */
    public function limit($limit)
    {
        if ($limit) {
            $this->limit = $limit;
        }

        return $this;
    }

    /**
     * Compare the $field to the $value and make sure it does not match.
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return \select\Select
     */
    public function notEq($field, $value, $type = \PDO::PARAM_STR)
    {
        // Convert a bool into the literal strings used by enum in the db layer
        if (is_bool($value)) {
            $value = $value ? $this->$booleanTrue : $this->$booleanFalse;
        }
        $this->SetConnection();
        $this->whereClause .= ' ' . $field . ' != ' . $this->addParam($value, $type);

        return $this;
    }

    /**
     * Sets the limit of the Query
     *
     * @param int $offset What is offset of rows that should be returned
     * @return \select\Select
     */
    public function offset($offset)
    {
        if ($offset) {
            $this->offset = $offset;
        }

        return $this;
    }

    /**
     * Sets the order of the Query
     *
     * @param string $order_by What should this order by
     * @return \select\Select
     */
    public function order($order_by)
    {
        $this->orderBy = $order_by;

        return $this;
    }

    /**
     * Sets values for inserting or updating a single row
     *
     * @param string $field What is the Table field
     * @param mixed $value What value are you comparing
     * @param int $type What variable type is this?
     * @return \select\Select
     */
    public function set($field, $value, $type = \PDO::PARAM_STR)
    {
        if (!array_key_exists($field, $this->setFields)) {
            $this->setFields[$field] = $type;
        }

        $this->setTmp[$field] = $this->addParam($value, $type);

        return $this;
    }

    /**
     * This starts a new control group. All added items will be separated by AND
     *
     * @return \select\Select
     */
    public function startAnd()
    {
        $this->SetConnection();
        $this->whereClause .= " (";
        $this->where_mode[] = "AND";

        return $this;
    }

    /**
     * This starts a new control group. All added items will be separated by OR
     *
     * @return \select\Select
     */
    public function startOr()
    {
        $this->SetConnection();
        $this->whereClause .= " (";
        $this->where_mode[] = "OR";

        return $this;
    }

    /**
     * Starts a transaction
     *
     * If you use $ignoreAlreadyExisting it will let you call this function even if a transaction is already started
     *   without throwing an exception.
     *
     * @return $this
     * @throws SelectException
     */
    public function transactionStart($ignoreAlreadyExisting = false)
    {
        if (!$this->connect()) {
            throw new SelectException('Could not connect to database.');
        }

        if (!$this->pdo->inTransaction()) {
            if ($ignoreAlreadyExisting) {
                return $this;
            }
            throw new SelectException('Already inside transaction.');
        }

        $this->pdo->beginTransaction();

        return $this;
    }

    /**
     * Rolls back the active transaction
     *
     * @return $this
     * @throws SelectException
     */
    public function transactionRollback()
    {
        if (!$this->connect()) {
            throw new SelectException('Could not connect to database.');
        }

        $this->pdo->rollBack();

        return $this;
    }

    /**
     * Commits the active transaction
     *
     * @return $this
     * @throws SelectException
     */
    public function transactionCommit()
    {
        if (!$this->connect()) {
            throw new SelectException('Could not connect to database.');
        }

        $this->pdo->commit();

        return $this;
    }
}
