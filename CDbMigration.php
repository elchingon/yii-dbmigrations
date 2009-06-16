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
 *  This class abstracts a database migration. The main functions that you will
 *  need to implement for creating a migration are the up and down methods.
 *
 *  The up method will be applied when the migration gets installed into the
 *  system, the down method when the migration gets removed from the system.
 *
 *  @package extensions.yii-dbmigrations
 */
abstract class CDbMigration {
    
    /**
     *  The CDbMigrationAdapater that is used to perform the actual migrations
     *  on the database.
     */
    private $adapter;
    
    /**
     *  Class constructor for the CDbMigration class.
     *
     *  @param $adapter The CDbMigrationAdapater that is used to perform the 
     *                  actual migrations on the database.
     */
    public function __construct(CDbMigrationAdapter $adapter) {
        $this->adapter = $adapter;
    }
    
    /**
     *  This method will execute the given class method inside a database
     *  transaction. It will raise an exception if the class method doesn't
     *  exist.
     *
     *  For the transaction handling, we rely on the Yii DB functionality. If
     *  the database doesn't support transactions, the SQL statements will be
     *  executed one after another without using transactions.
     *
     *  If the command succeeds, the transaction will get committed, if not, a
     *  rollback of the transaction will happen.
     *
     *  @param $command The name of the class method to execute.
     */
    public function performTransactional($command) {
        
        // Check if the class method exists
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
    
    /**
     *  The up class method contains all the statements that will be executed
     *  when the migration is applied to the database.
     */
    public function up() {
    }
    
    /**
     *  The up class method contains all the statements that will be executed
     *  when the migration is removed the database.
     */
    public function down() {
    }
    
    /**
     *  This function returns the ID of the migration.
     *
     *  Given the following migration class name:
     *
     *  <code>m20090611153243_CreateTables</code>
     *
     *  The id for this migration will be:
     *
     *  <code>20090611153243</code>
     *
     *  @returns The ID of the migration.
     */
    public function getId() {
        $id = split('_', get_class($this));
        return substr($id[0], 1);
    }
    
    /**
     *  This function returns the name of the migration.
     *
     *  Given the following migration class name:
     *
     *  <code>m20090611153243_CreateTables</code>
     *
     *  The name for this migration will be:
     *
     *  <code>CreateTables</code>
     *
     *  @returns The name of the migration.
     */
    public function getName() {
        $name = split('_', get_class($this));
        return join('_', array_slice($name, 1, sizeof($name) - 1));
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
    protected function execute($query, $params=array()) {
        return $this->adapter->execute($query, $params);
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
    protected function query($query, $params=array()) {
        return $this->adapter->query($query, $params);
    }
    
    /**
     *  The createTable function allows you to create a new table in the
     *  database.
     *
     *  @param $name    The name of the table to create.
     *  @param $column  The column definition for the database table
     *  @param $options The extra options to pass to the database creation.
     */
    protected function createTable($name, $columns=array(), $options=null) {
        echo('    >> Creating table: ' . $name . PHP_EOL);
        return $this->adapter->createTable($name, $columns, $options);
    }
    
    /**
     *  Rename a table.
     *
     *  @param $name     The name of the table to rename.
     *  @param $new_name The new name for the table.
     */
    protected function renameTable($name, $new_name) {
        echo('    >> Renaming table: ' . $name . ' to: ' . $new_name . PHP_EOL);
        return $this->adapter->renameTable($name, $new_name);
    }
    
    /**
     *  Remove a table from the database.
     *
     *  @param $name The name of the table to remove.
     */
    protected function removeTable($name) {
        echo('    >> Removing table: ' . $name . PHP_EOL);
        return $this->adapter->removeTable($name);
    }
    
    // Add a database column
    protected function addColumn($table, $column, $type, $options=null) {
        echo('    >> Adding column ' . $column . ' to table: ' . $table . PHP_EOL);
        return $this->adapter->addColumn($table, $column, $type, $options);
    }
    
    /**
     *  Rename a database column
     */
    protected function renameColumn($table, $name, $new_name) {
        echo(
            '    >> Renaming column ' . $name . ' to: ' . $new_name
            . ' in table: ' . $table . PHP_EOL
        );
        return $this->adapter->renameColumn($table, $name, $new_name);
    }
    
    /**
     *  Change a database column
     */
    protected function changeColumn($table, $column, $type, $options=null) {
        echo(
            '    >> Chaning column ' . $name . ' to: ' . $type
            . ' in table: ' . $table . PHP_EOL
        );
        return $this->adapter->changeColumn($table, $column, $type, $options);
    }
    
    /**
     *  Remove a table column from the database.
     *
     *  @param $table  The name of the table to remove the column from.
     *  @param $column The name of the table column to remove.
     */
    protected function removeColumn($table, $column) {
        echo(
            '    >> Removing column ' . $column . ' from table: ' . $table . PHP_EOL
        );
        return $this->adapter->removeColumn($table, $column);
    }
    
    /**
     *  Add an index
     */
    public function addIndex($table, $name, $columns, $unique=false) {
        echo('    >> Adding index ' . $name . ' to table: ' . $table . PHP_EOL);
        return $this->adapter->addIndex($table, $name, $columns, $unique);
    }
    
    /**
     *  Remove a table index from the database.
     *
     *  @param $table  The name of the table to remove the index from.
     *  @param $column The name of the table index to remove.
     */
    protected function removeIndex($table, $name) {
        echo('    >> Removing index ' . $name . ' from table: ' . $table . PHP_EOL);
        return $this->adapter->removeIndex($table, $name);
    }
    
}