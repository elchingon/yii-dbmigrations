<?php

/**
 * CDbMigration class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  @package extensions.yii-dbmigrations
 */
class CDbMigrationException extends Exception {}

/**
 *  @package extensions.yii-dbmigrations
 */
abstract class CDbMigration {
    
    // The adapter to use
    private $adapter;
    
    // Constructor
    public function __construct(CDbMigrationAdapter $adapter) {
        $this->adapter = $adapter;
    }
    
    // Perform a command transactional
    public function performTransactional($command) {
        
        // Check if the command exists
        if (!method_exists($this, $command)) {
            throw new CDbMigrationException(
                'Invalid migration command: ' . $command
            );
        }
        
        // Run the command inside a transaction
        $transaction = $this->adapter->db->beginTransaction();
        try {
            $this->$command();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
        }
        
        
    }
    
    // Migrate up
    public function up() {
    }
    
    // Migrate down
    public function down() {
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
        echo('    >> Creating table: ' . $name . PHP_EOL);
        return $this->adapter->createTable($name, $columns, $options);
    }
    
    // Rename a table
    protected function renameTable($name, $new_name) {
        echo('    >> Renaming table: ' . $name . ' to: ' . $new_name . PHP_EOL);
        return $this->adapter->renameTable($name, $new_name);
    }
    
    // Drop a table
    protected function removeTable($name) {
        echo('    >> Removing table: ' . $name . PHP_EOL);
        return $this->adapter->removeTable($name);
    }
    
    // Add a database column
    protected function addColumn($table, $column, $type, $options=null) {
        echo('    >> Adding column ' . $column . ' to table: ' . $table . PHP_EOL);
        return $this->adapter->addColumn($table, $column, $type, $options);
    }
    
    // Rename a database column
    protected function renameColumn($table, $name, $new_name) {
        echo(
            '    >> Renaming column ' . $name . ' to: ' . $new_name
            . ' in table: ' . $table . PHP_EOL
        );
        return $this->adapter->renameColumn($table, $name, $new_name);
    }
    
    // Change a database column
    protected function changeColumn($table, $column, $type, $options=null) {
        echo(
            '    >> Chaning column ' . $name . ' to: ' . $type
            . ' in table: ' . $table . PHP_EOL
        );
        return $this->adapter->changeColumn($table, $column, $type, $options);
    }
    
    // Remove a column
    protected function removeColumn($table, $column) {
        echo(
            '    >> Removing column ' . $column . ' from table: ' . $table . PHP_EOL
        );
        return $this->adapter->removeColumn($table, $column);
    }
    
    // Add an index
    public function addIndex($table, $name, $columns, $unique=false) {
        echo('    >> Adding index ' . $name . ' to table: ' . $table . PHP_EOL);
        return $this->adapter->addIndex($table, $name, $columns, $unique);
    }
    
    // Remove an index
    protected function removeIndex($table, $column) {
        echo('    >> Removing index ' . $name . ' from table: ' . $table . PHP_EOL);
        return $this->adapter->removeIndex($table, $column);
    }
    
}