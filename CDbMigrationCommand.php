<?php

// Import the main classes
Yii::import('application.extensions.DbMigrations.*');

// The migration command
class CDbMigrationCommand extends CConsoleCommand {
    
    // The migration adapter to use
    private $adapter;
    
    // The name of the table and column for the schema information
    const SCHEMA_TABLE = 'schema_version';
    const SCHEMA_FIELD = 'id';
    const SCHEMA_EXT   = 'php';
    
    // Get the command help
    public function getHelp() {
        return 'Used to run database migrations';
    }
    
    // Run the command
    public function run($args) {
        
        // Catch errors
        try {
            
            // Initialize
            $this->init();
            
            // Check if we need to create a migration
            if (isset($args[0]) && ($args[0] == 'create')) {
                $this->create($args[0]);
            } else {
                $this->applyMigrations();
            }
            
        } catch (Exception $e) {
            
            // Log the error
            $this->log('-----');
            $this->log('SOMETHING WENT TERRIBLY WRONG:');
            $this->log($e->getMessage());
            
        }
        
    }
    
    // Create a migration
    protected function create($migration) {
    }
    
    // Initialize the schema version table
    protected function init() {
        
        // Add the migrations directory to the search path
        Yii::import('application.migrations.*');
        
        // Load the migration adapter
        switch (Yii::app()->db->driverName) {
            case 'mysql':
                $this->adapter = new CDbMigrationAdapterMysql(Yii::app()->db);
                break;
            case 'sqlite':
                $this->adapter = new CDbMigrationAdapterSqlite(Yii::app()->db);
                break;
            default:
                throw new CDbMigrationException(
                    'Database of type ' . Yii::app()->db->driverName
                    . ' does not support migrations (yet).'
                );
        }
        
        // Check if the schema version table exists
        if (Yii::app()->db->schema->getTable('schema_version') == null) {
            
            // Create the table
            $this->log('Creating initial schema_version table');
            
            // Use the adapter to create the table
            $this->adapter->createTable(
                self::SCHEMA_TABLE,
                array(
                    array(self::SCHEMA_FIELD, 'string'),
                )
            );
            
            // Create an index on the column
            $this->adapter->addIndex(
                self::SCHEMA_TABLE,
                'idx_' . self::SCHEMA_TABLE . '_' . self::SCHEMA_FIELD,
                array(self::SCHEMA_FIELD),
                true
            );
            
        }
        
    }
    
    // Get the list of migrations that are applied to the database
    protected function getAppliedMigrations() {
        
        // Get the field and table name
        $field = Yii::app()->db->quoteColumnName(self::SCHEMA_FIELD);
        $table = Yii::app()->db->quoteTableName(self::SCHEMA_TABLE);
        
        // Construct the SQL statement
        $sql = 'SELECT ' . $field . ' FROM ' . $table . ' ORDER BY ' . $field;
                
        // Get the list
        return Yii::app()->db->createCommand($sql)->queryColumn(
            self::SCHEMA_FIELD
        );
                
    }
    
    // Get the list of possible migrations
    protected function getPossibleMigrations() {
        $migrations = CFileHelper::findFiles(
            Yii::app()->basePath . '/migrations',
            array('fileTypes' => array(self::SCHEMA_EXT), 'level' => 0)
        );
        foreach ($migrations as $key=>$migration) {
            $migrations[$key] = basename($migration, '.' . self::SCHEMA_EXT);
        }
        return $migrations;
    }
    
    // Apply the migrations
    protected function applyMigrations() {
        $applied = $this->getAppliedMigrations();
        $possible = $this->getPossibleMigrations();
        
        foreach ($possible as $migration) {
            $migration = new $migration($this->adapter);
            if (!in_array($migration->getId(), $applied)) {
                $this->applyMigration($migration);
            } else {
                $this->log(
                    'Skipping applied migration: ' . get_class($migration)
                );
            }
        }
    }
    
    // Apply a specific migration
    protected function applyMigration($migration) {
        
        // Apply the migration
        $this->log('Applying migration: ' . get_class($migration));
        
        // Create the migration instance
        $migration->up();
        
        // Commit the migration
        $this->log('Marking migration as applied: ' . get_class($migration));
        $cmd = Yii::app()->db->commandBuilder->createInsertCommand(
            self::SCHEMA_TABLE,
            array(self::SCHEMA_FIELD => $migration->getId())
        )->execute();
        
    }
    
    // Helper for logging a message
    protected function log($msg) {
        echo(strftime('%Y-%m-%d %H:%M:%S') . ' ' . $msg . PHP_EOL);
    }
    
}