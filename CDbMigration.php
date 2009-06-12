<?php

class CDbMigrationException extends Exception {}

// Class implementing a migration
abstract class CDbMigration {
    
    // The adapter to use
    private $adapter;
    
    // Constructor
    public function __construct(CDbMigrationAdapter $adapter) {
        $this->adapter = $adapter;
    }
    
    // Migrate up
    public function up() {
    }
    
    // Migrate down
    public function down() {
    }
    
    // Helper for logging a message
    protected function log($msg) {
        echo(strftime('%Y-%m-%d %H:%M:%S') . ' ' . $msg . PHP_EOL);
    }
    
    // Get the id of the migration
    public function getId() {
        $id = split('_', get_class($this));
        return substr($id[0], 1);
    }
    
    // Get the name of the migration
    public function getName() {
        $name = split('_', get_class($this));
        return $name[1];
    }
    
    // Execute a raw SQL statement
    protected function execute($query, $params=array()) {
        return $this->adapter->execute($query, $params);
    }
    
    // Execute a raw SQL statement
    protected function query($query, $params=array()) {
        return $this->adapter->query($query, $params);
    }
    
    // Create a table
    protected function createTable($name, $columns=array(), $options=null) {
        $this->log('    >> Creating table: ' . $name);
        return $this->adapter->createTable($name, $columns, $options);
    }
    
    // Rename a table
    protected function renameTable($name, $new_name) {
        $this->log('    >> Renaming table: ' . $name . ' to: ' . $new_name);
        return $this->adapter->renameTable($name, $new_name);
    }
    
    // Drop a table
    protected function removeTable($name) {
        $this->log('    >> Removing table: ' . $name);
        return $this->adapter->removeTable($name);
    }
    
    // Add a database column
    protected function addColumn($table, $column, $type, $options=null) {
        $this->log('    >> Adding column ' . $column . ' to table: ' . $table);
        return $this->adapter->addColumn($table, $column, $type, $options);
    }
    
    // Rename a database column
    protected function renameColumn($table, $name, $new_name) {
        $this->log(
            '    >> Renaming column ' . $name . ' to: ' . $new_name
            . ' in table: ' . $table
        );
        return $this->adapter->renameColumn($table, $name, $new_name);
    }
    
    // Change a database column
    protected function changeColumn($table, $column, $type, $options=null) {
        $this->log(
            '    >> Chaning column ' . $name . ' to: ' . $type
            . ' in table: ' . $table
        );
        return $this->adapter->changeColumn($table, $column, $type, $options);
    }
    
    // Remove a column
    protected function removeColumn($table, $column) {
        $this->log(
            '    >> Removing column ' . $column . ' from table: ' . $table
        );
        return $this->adapter->removeColumn($table, $column);
    }
    
    // Add an index
    public function addIndex($table, $name, $columns, $unique=false) {
        $this->log('    >> Adding index ' . $name . ' to table: ' . $table);
        return $this->adapter->addIndex($table, $name, $columns, $unique);
    }
    
    // Remove an index
    protected function removeIndex($table, $column) {
        $this->log('    >> Removing index ' . $name . ' from table: ' . $table);
        return $this->adapter->removeIndex($table, $column);
    }
    
}