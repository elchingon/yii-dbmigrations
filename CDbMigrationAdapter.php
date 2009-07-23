<?php

/**
 * CDbMigrationAdapter class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  @package extensions.yii-dbmigrations
 */
abstract class CDbMigrationAdapter {
    
    /**
     *  The database connection
     */
    public $db;
    
    /**
     *  Class Constructor
     */
    public function __construct(CDbConnection $db) {
        $this->db = $db;
    }
    
    /**
     *  Convert a type to a native database type
     */
    protected function convertToNativeType($theType) {
        if (isset($this->nativeDatabaseTypes[$theType])) {
            return $this->nativeDatabaseTypes[$theType];
        } else {
            return $theType;
        }
    }
    
    /**
     *  Convert the field information to native types
     */
    protected function convertFields($fields) {
        $result = array();
        foreach ($fields as $field) {
            if (is_array($field)) {
                if (isset($field[0])) {
                    $field[0] = $this->db->quoteColumnName($field[0]);
                }
                if (isset($field[1])) {
                    $field[1] = $this->convertToNativeType($field[1]);
                }
                $result[] = join(' ', $field);
            } else {
                $result[] = $this->db->quoteColumnName($field);
            }
        }
        return join(', ', $result);
    }
    
    /**
     *  With the execute function, you can execute a raw SQL query against the
     *  database. The SQL query should be one that doesn't return any data.
     *
     *  @param $query The SQL query to execute.
     *  @param $params The parameters to pass to the SQL query.
     *
     *  @returns The number of affected rows.
     */
    public function execute($query, $params=array()) {
        $cmd = $this->db->createCommand($query);
        foreach ($params as $key => $param) {
            $cmd->bindValue($key, $param);
        }
        return $cmd->execute();
    }
    
    /**
     *  With the execute function, you can execute a raw SQL query against the
     *  database. The SQL query should be one that returns data.
     *
     *  @param $query The SQL query to execute.
     *  @param $params The parameters to pass to the SQL query.
     *
     *  @returns The rows returned from the database.
     */
    public function query($query, $params=array()) {
        $cmd = $this->db->createCommand($query);
        foreach ($params as $key => $param) {
            $cmd->bindParam($key, $param);
        }
        return $cmd->queryAll();
    }
    
    /**
     *  Retrieve the type information from a database column.
     *
     *  @returns The current data type of the column.
     */
    public function columnInfo($table, $name) {
    }
    
    /**
     *  The createTable function allows you to create a new table in the
     *  database.
     *
     *  @param $name    The name of the table to create.
     *  @param $column  The column definition for the database table
     *  @param $options The extra options to pass to the database creation.
     */
    public function createTable($name, $columns=array(), $options=null) {
        $sql = 'CREATE TABLE ' . $this->db->quoteTableName($name) . ' ('
             . $this->convertFields($columns)
             . ') ' . $options;
        return $this->execute($sql);
    }
    
    /**
     *  Rename a table.
     *
     *  @param $name     The name of the table to rename.
     *  @param $new_name The new name for the table.
     */
    public function renameTable($name, $new_name) {
        $sql = 'RENAME TABLE ' . $this->db->quoteTableName($name) . ' TO '
             . $this->db->quoteTableName($new_name);
        return $this->execute($sql);
    }
    
    /**
     *  Remove a table from the database.
     *
     *  @param $name The name of the table to remove.
     */
    public function removeTable($name) {
        $sql = 'DROP TABLE ' . $this->db->quoteTableName($name);
        return $this->execute($sql);
    }
    
    /**
     *  Add a database column to an existing table.
     *
     *  @param $table   The table to add the column in.
     *  @param $column  The name of the column to add.
     *  @param $type    The data type for the new column.
     *  @param $options The extra options to pass to the column.
     */
    public function addColumn($table, $column, $type, $options=null) {
        $type = $this->convertToNativeType($type);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ADD '
             . $this->db->quoteColumnName($column) . ' ' . $type . ' '
             . $options;
        return $this->execute($sql);
    }
    
    /**
     *  Rename a database column in an existing table.
     *
     *  @param $table    The table to rename the column from.
     *  @param $name     The current name of the column.
     *  @param $new_name The new name of the column.
     */
    public function renameColumn($table, $name, $new_name) {
        $type = $this->columnInfo($table, $name);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' CHANGE '
             . $this->db->quoteColumnName($name) . ' '
             . $this->db->quoteColumnName($new_name) . ' ' . $type;
        return $this->execute($sql);
    }
    
    /**
     *  Change a database column in an existing table.
     *
     *  @param $table The name of the table to change the column from.
     *  @param $column The name of the column to change.
     *  @param $type   The new data type for the column.
     *  @param $options The extra options to pass to the column.
     */
    public function changeColumn($table, $column, $type, $options=null) {
        $type = $this->convertToNativeType($type);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' CHANGE '
             . $this->db->quoteColumnName($column) . ' '
             . $this->db->quoteColumnName($column) . ' ' . $type . ' '
             . $options;
        return $this->execute($sql);
    }
    
    /**
     *  Remove a table column from the database.
     *
     *  @param $table  The name of the table to remove the column from.
     *  @param $column The name of the table column to remove.
     */
    public function removeColumn($table, $column) {
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' DROP '
             . $this->db->quoteColumnName($column);
        return $this->execute($sql);
    }
    
    /**
     *  Add an index to the database or a specific table.
     *
     *  @param $table   The name of the table to add the index to.
     *  @param $name    The name of the index to create.
     *  @param $columns The name of the fields to include in the index.
     *  @param $unique  If set to true, a unique index will be created.
     */
    public function addIndex($table, $name, $columns, $unique=false) {
        $sql = 'CREATE ';
        $sql .= ($unique) ? 'UNIQUE ' : '';
        $sql .= 'INDEX ' . $this->db->quoteColumnName($name) . ' ON '
             .  $this->db->quoteTableName($table) . ' ('
             .  $this->convertFields($columns)
             . ')';
        return $this->execute($sql);
    }
    
    /**
     *  Remove a table index from the database.
     *
     *  @param $table  The name of the table to remove the index from.
     *  @param $column The name of the table index to remove.
     */
    public function removeIndex($table, $name) {
        $sql = 'DROP INDEX ' . $this->db->quoteTableName($name) . ' ON '
             . $this->db->quoteTableName($table);
        return $this->execute($sql);
    }
    
}
