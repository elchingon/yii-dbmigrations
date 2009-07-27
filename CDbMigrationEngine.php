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
     *  The command line arguments passed to the system.
     */
    private $args;
    
    /**
     *  The full path to the migrations
     */
    private $migrationsDir;
    
    /**
     *  Run the database migration engine, passing on the command-line
     *  arguments.
     *
     *  @param $args The command line parameters.
     */
    public function run($args) {
        
        // Catch errors
        try {
        
            // Remember the arguments
            $this->args = $args;
            
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
     *  - Constructs the full path to the migrations
     *  - The system checks if a database connection was configured.
     *  - The system checks if the database driver supports migrations.
     *  - If the schema_version table doesn't exist yet, it gets created.
     */
    protected function init() {
        
        // Construct the path to the migrations dir
        $this->migrationsDir = Yii::app()->basePath;
        if (!empty($module)) {
            $this->migrationsDir .= '/modules/' . trim($module, '/');
        }
        $this->migrationsDir .= '/' . self::MIGRATIONS_DIR;
        
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
        
        // Start with an empty list
        $migrations = array();
        
        // Check if the migrations directory actually exists
        if (is_dir($this->migrationsDir)) {
            
            // Construct the list of migrations
            $migrationFiles = CFileHelper::findFiles(
                $this->migrationsDir,
                array('fileTypes' => array(self::SCHEMA_EXT), 'level' => 0)
            );
            foreach ($migrationFiles as $migration) {
                
                // Check if it's valid
                if (substr(basename($migration), 0, 1) != 'm') {
                    continue;
                }
                
                // Include the file
                include_once($migration);
                
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
        $applied  = $this->getAppliedMigrations();
        $possible = $this->getPossibleMigrations();
        
        // Check what which action need to happen
        if ($version == 'list') {
            
            // List the status of all migrations
            foreach ($possible as $id => $specs) {
                $status = in_array($id, $applied) ? 'Applied:     ' : 'Not Applied: ';
                $name   = substr(basename($specs['file'], '.php'), 1);
                $name   = str_replace('_', ' ', $name);
                echo("${status} ${name}" . PHP_EOL);
            }
            
        } elseif ($version == 'create') {
            
            // Get the name from the migration
            $name = 'UntitledMigration';
            if (isset($this->args[1]) && !empty($this->args[1])) {
                $name = trim($this->args[1]);
            }
            $name = strftime('m%Y%m%d%H%M%S_' . $name);
            
            // Read the template file
            $data = file_get_contents(
                dirname(__FILE__) . '/templates/migration.php'
            );
            $data = str_replace('${name}', $name, $data);
            
            // Save the file
            if (!is_dir($this->migrationsDir)) {
                mkdir($this->migrationsDir);
            }
            file_put_contents(
                $this->migrationsDir . '/' . $name . '.php', $data
            );
            echo('Created migration: ' . $name . '.php' . PHP_EOL);
            
        } elseif ($version == 'down') {
            
            // Get the last migration
            $migration = array_pop($applied);
            
            // Apply the migration
            if ($migration) {
                $this->applyMigration(
                    $possible[$migration]['class'],
                    $possible[$migration]['file'],
                    'down'
                );
            }

        } elseif ($version == 'redo') {

            // Redo the last migration
            $this->applyMigrations('down');
            $this->applyMigrations('up');
            
        } elseif ($version == 'up') {
            
            // Check if there are still versions to apply
            foreach ($possible as $id => $specs) {
                
                // Check if it's applied or not
                if (!in_array($id, $applied)) {
                    
                    // Apply it
                    $this->applyMigration(
                        $specs['class'], $specs['file'], 'up'
                    );
                    
                    // Exit the loop
                    break;
                    
                }
                
            }
            
        } elseif (!empty($version)) {
            
            // Check if it's a valid version number
            if (!isset($possible[$version])) {
                throw new CDbMigrationEngineException(
                    'Invalid migration: ' . $version
                );
            }
            
            // Check if we need to go up or down
            if (in_array($version, $applied)) {
                
                // Reverse loop over the possible migrations
                foreach (array_reverse($possible, true) as $id => $specs) {
                    
                    // If we reached the correct version, exit the loop
                    if ($id == $version) {
                        break;
                    }
                    
                    // Check if it's applied or not
                    if (in_array($id, $applied)) {
                        
                        // Remove the migration
                        $this->applyMigration(
                            $specs['class'], $specs['file'], 'down'
                        );
                    
                    }
                    
                }
                
            } else {
                
                // Loop over all possible migrations
                foreach ($possible as $id => $specs) {
                    
                    // Check if it's applied or not
                    if (!in_array($id, $applied)) {
                        
                        // Apply it
                        $this->applyMigration(
                            $specs['class'], $specs['file'], 'up'
                        );
                        
                        // If we applied the requested migration, exit the loop
                        if ($id == $version) {
                            break;
                        }
                    
                    }
            
                }
                
            }
            
        } else {
            
            // Loop over all possible migrations
            foreach ($possible as $id => $specs) {
                
                // Check if it's applied or not
                if (!in_array($id, $applied)) {
                    
                    // Apply it
                    $this->applyMigration(
                        $specs['class'], $specs['file'], 'up'
                    );
                    
                }
            
            }
            
        }
        
    }
    
    /**
     *  Apply a specific migration based on the migration name.
     *
     *  @param $class     The class name of the migration to apply.
     *  @param $file      The file in which you can find the migration.
     *  @param $direction The direction in which the migration needs to be
     *                    applied. Needs to be "up" or "down".
     */
    protected function applyMigration($class, $file, $direction='up') {
        
        // Include the migration file
        require_once($file);
        
        // Create the migration
        $migration = new $class($this->adapter);
        
        // Apply the migration
        $msg = ($direction == 'up') ? 'Applying' : 'Removing';
        $msg = str_pad('=== ' . $msg . ': ' . $class . ' ', 80, '=') . PHP_EOL;
        echo($msg);
        
        // Perform the migration function transactional
        $migration->performTransactional($direction);
        
        // Commit the migration
        if ($direction == 'up') {
            $cmd = Yii::app()->db->commandBuilder->createInsertCommand(
                self::SCHEMA_TABLE,
                array(self::SCHEMA_FIELD => $migration->getId())
            )->execute();
        $msg = 'applied';
        } else {
            $sql = 'DELETE FROM '
                 . $this->adapter->db->quoteTableName(self::SCHEMA_TABLE)
                 . ' WHERE '
                 . $this->adapter->db->quoteColumnName(self::SCHEMA_FIELD)
                 . ' = '
                 . $this->adapter->db->quoteValue($migration->getId());
            $this->adapter->execute($sql);
            $msg = 'removed';
        }
        $msg = str_pad('=== Marked as ' . $msg . ': ' . $class . ' ', 80, '=') . PHP_EOL . PHP_EOL;
        echo($msg);
        
    }
    
}