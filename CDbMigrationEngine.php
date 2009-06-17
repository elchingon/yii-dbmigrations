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
    
    /**
     *  The migration adapter to use
     */
    private $adapter;
    
    /**
     *  The name of the table that contains the schema information.
     */
    const SCHEMA_TABLE   = 'schema_version';
    
    /**
     *  The field in the schema_version table that contains the id of the 
     *  installed migrations.
     */
    const SCHEMA_FIELD   = 'id';
    
    /**
     *  The extension used for the migration files.
     */
    const SCHEMA_EXT     = 'php';
    
    /**
     *  The directory in which the migrations can be found.
     */
    const MIGRATIONS_DIR = 'migrations';
    
    /**
     *  Run the database migration engine, passing on the command-line
     *  arguments.
     *
     *  @param $args The command line parameters.
     */
    public function run($args) {
        
        // Catch errors
        try {
            
            // Initialize the engine
            $this->init();
            
            // Check if we need to create a migration
            if (isset($args[0]) && !empty($args[0])) {
                $this->applyMigrations($args[0]);
            } else {
                $this->applyMigrations();
            }
            
        } catch (Exception $e) {
            echo('ERROR: ' . $e->getMessage() . PHP_EOL);
        }
        
    }
    
    /**
     *  Initialize the database migration engine. Several things happen during
     *  this initialization:
     *  - The system checks if a database connection was configured.
     *  - The system checks if the database driver supports migrations.
     *  - If the schema_version table doesn't exist yet, it gets created.
     */
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
    
    /**
     *  Get the list of migrations that are applied to the database. This
     *  basically reads out the schema_version table from the database.
     *
     *  @returns An array with the IDs of the already applied database
     *           migrations as found in the database.
     */
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
    
    /**
     *  Get the list of possible migrations from the file system. This will read
     *  the contents of the migrations directory and the migrations directory
     *  inside each installed and enabled module.
     *
     *  @returns An array with the IDs of the possible database migrations as
     *           found in the database.
     */
    protected function getPossibleMigrations() {
        
        // Get the migrations for the default application
        $migrations = $this->getPossibleMigrationsForModule();
        
        /*
        // Get the migrations for each installed and enabled module
        foreach (Yii::app()->modules as $module => $moduleData) {
            $migrations = array_merge(
                $migrations, $this->getPossibleMigrationsForModule($module)
            );
        }
        */
        
        // Sort them based on the file path (which is the key in the array)
        ksort($migrations);
        
        // Returh the list of migrations
        return $migrations;
        
    }
    
    /**
     *  A helper function to get the list of migrations for a specific module.
     *  If no module is specified, it will return the list of modules from the
     *  "protected/migrations" directory.
     *
     *  @param $module The name of the module to get the migrations for.
     */
    protected function getPossibleMigrationsForModule($module=null) {
        
        // Get the path to the migrations dir
        $path = Yii::app()->basePath;
        if (!empty($module)) {
            $path .= '/modules/' . trim($module, '/');
        }
        $path .= '/' . self::MIGRATIONS_DIR;
        
        // Start with an empty list
        $migrations = array();
        
        // Check if the migrations directory actually exists
        if (is_dir($path)) {
            
            // Construct the list of migrations
            $migrationFiles = CFileHelper::findFiles(
                $path,
                array('fileTypes' => array(self::SCHEMA_EXT), 'level' => 0)
            );
            foreach ($migrationFiles as $migration) {
                
                // Check if it's valid
                if (substr(basename($migration), 0, 1) != 'm') {
                    continue;
                }
                
                // Include the file
                include($migration);
                
                // Get the class name
                $className = basename($migration, '.' . self::SCHEMA_EXT);
                
                // Check if the class exists
                if (!class_exists($className) || strlen($className) < 16) {
                    continue;
                }
                
                // Check if the class is a valid migration
                $migrationReflection = new ReflectionClass($className);
                $baseReflection = new ReflectionClass('CDbMigration');
                if (!$migrationReflection->isSubclassOf($baseReflection)) {
                    continue;
                }
                
                // Add them to the list
                $id = substr($className, 1, 14);
                $migrations[$id] = array(
                    'file'  => $migration,
                    'class' => $className,
                );
                
            }
            
        }
        
        // Return the list
        return $migrations;
        
    }
    
    /**
     *  Apply the migrations to the database. This will apply any migration that
     *  has not been applied to the database yet. It does this in a 
     *  chronological order based on the IDs of the migrations.
     *
     *  @param $version The version to migrate to. If you specify the special
     *                  cases "up" or "down", it will go one migration "up" or
     *                  "down". If it's a number, if will migrate "up" or "down"
     *                  to that specific version.
     */
    protected function applyMigrations($version='') {
        
        // Get the list of applied and possible migrations
        $applied = $this->getAppliedMigrations();
        $possible = $this->getPossibleMigrations();
        
        // Check what needs to happen
        if ($version == 'down') {
            
            // Check if there are any applied migrations
            if (sizeof($applied) == 0) {
                throw new CDbMigrationEngineException(
                    'No migrations are applied to the database yet.'
                );
            }
            
            // Get the last applied migration
            $lastMigration = array_pop($applied);
            
            // Get the details
            $migration = $this->createMigration(
                $possible[$lastMigration]['class'],
                $possible[$lastMigration]['file']
            );
            
            // Apply the migration
            $this->applyMigration($migration, 'down');
            
            // Return
            return;
            
        }
        
        // We are updating one or more revisions
        if (empty($version) || $version == 'up') {
            
            // Loop over all possible migrations
            foreach ($possible as $migrationId => $migrationSpecs) {
                
                // Include the migration file
                require_once($migrationSpecs['file']);
                
                // Create the migration instance
                $migration = $this->createMigration(
                    $migrationSpecs['class'], $migrationSpecs['file']
                );
                
                // Check if it's already applied to the database
                if (!in_array($migration->getId(), $applied)) {
                
                    // Apply the migration to the database
                    $this->applyMigration($migration);
                    
                    // If we do up, we stop after the first one
                    if ($version == 'up') {
                        break;
                    }
                
                }
            
            }
            
        }
        
    }
    
    /**
     *  This function creates a migration instance.
     *
     *  @param $id   The ID of the migration.
     *  @param $file The file in which the migration exists
     */
    protected function createMigration($class, $file) {
        
        // Include the migration file
        require_once($file);
        
        // Create the migration instance
        return new $class($this->adapter);

    }
    
    /**
     *  Apply a specific migration based on the migration name.
     *
     *  @param $migration The name of the migration to apply.
     *  @param $direction The direction in which the migration needs to be
     *                    applied. Needs to be "up" or "down".
     */
    protected function applyMigration($migration, $direction='up') {
        
        // Apply the migration
        if ($direction == 'up') {
            echo('Applying migration: ' . get_class($migration) . PHP_EOL);
        } else {
            echo('Removing migration: ' . get_class($migration) . PHP_EOL);
        }
        
        // Perform the migration function transactional
        $migration->performTransactional($direction);
        
        // Commit the migration
        if ($direction == 'up') {
            echo(
                'Marking migration as applied: ' . get_class($migration) . PHP_EOL
            );
            $cmd = Yii::app()->db->commandBuilder->createInsertCommand(
                self::SCHEMA_TABLE,
                array(self::SCHEMA_FIELD => $migration->getId())
            )->execute();
        } else {
            echo(
                'Marking migration as removed: ' . get_class($migration) . PHP_EOL
            );
            $sql = 'DELETE FROM '
                 . $this->adapter->db->quoteTableName(self::SCHEMA_TABLE)
                 . ' WHERE '
                 . $this->adapter->db->quoteColumnName(self::SCHEMA_FIELD)
                 . ' = '
                 . $this->adapter->db->quoteValue($migration->getId());
            $this->adapter->execute($sql);
        }
        
    }
    
}