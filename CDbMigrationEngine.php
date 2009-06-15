<?php

/**
 * CDbMigrationEngine class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  Import the adapters we are going to use for the migrations.
 */
Yii::import('application.extensions.yii-dbmigrations.adapters.*');

/**
 *  A database migration engine exception
 *
 *  @package extensions.yii-dbmigrations
 */
class CDbMigrationEngineException extends Exception {}

/**
 *  The CDbMigrationEngine class is the actual engine that can do all the
 *  migrations related functionality.
 *
 *  @package extensions.yii-dbmigrations
 */
class CDbMigrationEngine {
    
    // The migration adapter to use
    private $adapter;
    
    // The name of the table and column for the schema information
    const SCHEMA_TABLE   = 'schema_version';
    const SCHEMA_FIELD   = 'id';
    const SCHEMA_EXT     = 'php';
    const MIGRATIONS_DIR = 'migrations';
    
    // Run the specified command
    public function run($args) {
        
        // Catch errors
        try {
            
            // Initialize the engine
            $this->init();
            
            // Check if we need to create a migration
            if (isset($args[0]) && ($args[0] == 'create')) {
                $this->create($args[0]);
            } else {
                $this->applyMigrations();
            }
            
        } catch (Exception $e) {
            echo('ERROR: ' . $e->getMessage() . PHP_EOL);
        }
        
    }
    
    // Initialize the schema version table
    protected function init() {
        
        // Check if a database connection was configured
        try {
            Yii::app()->db;
        } catch (Exception $e) {
            throw new CDbMigrationEngineException(
                'Database configuration is missing in your configuration file.'
            );
        }
        
        // Load the migration adapter
        switch (Yii::app()->db->driverName) {
            case 'mysql':
                $this->adapter = new CDbMigrationAdapterMysql(Yii::app()->db);
                break;
            case 'sqlite':
                $this->adapter = new CDbMigrationAdapterSqlite(Yii::app()->db);
                break;
            default:
                throw new CDbMigrationEngineException(
                    'Database of type ' . Yii::app()->db->driverName
                    . ' does not support migrations (yet).'
                );
        }
        
        // Check if the schema version table exists
        if (Yii::app()->db->schema->getTable('schema_version') == null) {
            
            // Create the table
            echo('Creating initial schema_version table' . PHP_EOL);
            
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
        
        // Get the migrations for the default application
        $migrations = $this->getPossibleMigrationsForModule();
        
        // Get the migrations for each installed and enabled module
        foreach (Yii::app()->modules as $module => $moduleData) {
            $migrations = array_merge(
                $migrations, $this->getPossibleMigrationsForModule($module)
            );
        }
        
        // Sort them based on the file path (which is the key in the array)
        ksort($migrations);
        
        // Returh the list of migrations
        return $migrations;
        
    }
    
    // Get the list of migrations for a specific module
    protected function getPossibleMigrationsForModule($module=null) {
        
        // Get the path to the migrations dir
        $path = Yii::app()->basePath;
        if (!empty($module)) {
            $path .= '/modules/' . trim($module, '/');
        }
        $path .= '/' . self::MIGRATIONS_DIR;
        
        // Start with an empty list
        $migrations = array();
        
        // Construct the list of migrations
        $migrationFiles = CFileHelper::findFiles(
            $path, array('fileTypes' => array(self::SCHEMA_EXT), 'level' => 0)
        );
        foreach ($migrationFiles as $migration) {
            $migrations[$migration] = basename(
                $migration, '.' . self::SCHEMA_EXT
            );
        }
        
        // Return the list
        return $migrations;
        
    }
    
    // Apply the migrations
    protected function applyMigrations() {
        
        // Get the list of applied and possible migrations
        $applied = $this->getAppliedMigrations();
        $possible = $this->getPossibleMigrations();
        
        // Loop over all possible migrations
        foreach ($possible as $migrationFile => $migration) {
            
            // Include the migration file
            require($migrationFile);
            
            // Create the migration instance
            $migration = new $migration($this->adapter);
            
            // Check if it's already applied to the database
            if (!in_array($migration->getId(), $applied)) {
                
                // Apply the migration to the database
                $this->applyMigration($migration);
                
            } else {
                
                // Skip the already applied migration
                echo(
                    'Skipping applied migration: ' . get_class($migration) . PHP_EOL
                );
                
            }

        }

    }
    
    // Apply a specific migration
    protected function applyMigration($migration) {
        
        // Apply the migration
        echo('Applying migration: ' . get_class($migration) . PHP_EOL);
        
        // Create the migration instance
        $migration->up();
        
        // Commit the migration
        echo(
            'Marking migration as applied: ' . get_class($migration) . PHP_EOL
        );
        $cmd = Yii::app()->db->commandBuilder->createInsertCommand(
            self::SCHEMA_TABLE,
            array(self::SCHEMA_FIELD => $migration->getId())
        )->execute();
        
    }
    
}