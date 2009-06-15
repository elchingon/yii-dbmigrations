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
    
    // The database connection
    public $db;
    
    // Constructor
    public function __construct(CDbConnection $db) {
        $this->db = $db;
    }
    
    // Convert a type to a native database type
    protected function convertToNativeType($theType) {
        if (isset($this->nativeDatabaseTypes[$theType])) {
            return $this->nativeDatabaseTypes[$theType];
        } else {
            return $theType;
        }
    }
    
    // Convert the field information to native types
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
    
    // Execute a raw SQL statement
    // @todo We need to be able to bind parameters
    public function execute($query, $params=array()) {
        return $this->db->createCommand($query)->execute();
    }
    
    // Execute a raw SQL statement
    // @todo We need to be able to bind parameters
    public function query($query, $params=array()) {
        return $this->db->createCommand($query)->queryAll();
    }
    
    // Get the column info
    public function columnInfo($name) {
    }
    
    // Create a table
    public function createTable($name, $columns=array(), $options=null) {
        $sql = 'CREATE TABLE ' . $this->db->quoteTableName($name) . ' ('
             . $this->convertFields($columns)
             . ') ' . $options;
        return $this->execute($sql);
    }
    
    // Rename a table
    public function renameTable($name, $new_name) {
        $sql = 'RENAME TABLE ' . $this->db->quoteTableName($name) . ' TO '
             . $this->db->quoteTableName($new_name);
        return $this->execute($sql);
    }
    
    // Drop a table
    public function removeTable($name) {
        $sql = 'DROP TABLE ' . $this->db->quoteTableName($name);
        return $this->execute($sql);
    }
    
    // Add a database column
    public function addColumn($table, $column, $type, $options=null) {
        $type = $this->convertToNativeType($type);
        if (empty($options)) {
            $options = 'NOT NULL';
        }
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ADD '
             . $this->db->quoteColumnName($column) . ' ' . $type . ' '
             . $options;
        return $this->execute($sql);
    }
    
    // Change a database column
    public function changeColumn($table, $column, $type, $options=null) {
        $type = $this->convertToNativeType($type);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' CHANGE '
             . $this->db->quoteColumnName($column) . ' '
             . $this->db->quoteColumnName($column) . ' ' . $type . ' '
             . $options;
        return $this->execute($sql);
    }
    
    // Rename a database column
    // @todo We need to retain the column definition
    public function renameColumn($table, $name, $new_name) {
        $type = $this->columnInfo($name);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' CHANGE '
             . $this->db->quoteColumnName($name) . ' '
             . $this->db->quoteColumnName($new_name) . ' ' . $type;
        return $this->execute($sql);
    }
    
    // Remove a column
    public function removeColumn($table, $column) {
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' DROP '
             . $this->db->quoteColumnName($column);
        return $this->execute($sql);
    }
    
    // Add an index
    public function addIndex($table, $name, $columns, $unique=false) {
        $sql = 'CREATE ';
        $sql .= ($unique) ? 'UNIQUE ' : '';
        $sql .= 'INDEX ' . $this->db->quoteColumnName($name) . ' ON '
             .  $this->db->quoteTableName($table) . ' ('
             .  $this->convertFields($columns)
             . ')';
        return $this->execute($sql);
    }
    
    // Remove an index
    public function removeIndex($table, $name) {
        $sql = 'DROP INDEX ' . $this->db->quoteTableName($name) . ' ON '
             . $this->db->quoteTableName($table);
        return $this->execute($sql);
    }
    
}
