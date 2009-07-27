<?php

/**
 * CDbMigrationCommand class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  Import the different extension components.
 */
Yii::import('application.extensions.yii-dbmigrations.*');
Yii::import('application.extensions.yii-dbmigrations.adapters.*');

/**
 *  This class creates the migrate console command so that you can use it with
 *  the yiic tool inside your project.
 *
 *  @package extensions.yii-dbmigrations
 */
class CDbMigrationCommand extends CConsoleCommand {
    
    /**
     *  Return the help for the migrate command.
     */
    public function getHelp() {
        return <<<EOD
USAGE
  migrate [create|version|up|down|list]

DESCRIPTION
  This command applies the database migrations which can be found in the
  migrations directory in your project folder.

PARAMETERS
 * create: creates a new migration in the migrations directory.
 
 * version: options, the ID of the migration to migrate the database to.

 * up: optional, apply the first migration that is not applied to the database
   yet.

 * down: remove the last applied migration from the database.

 * redo: redo the last migration (removes and installs it again)
 
 * list: list all the migrations available in the application and show if they
   are applied or not.

EXAMPLES
 * Apply all migrations that are not applied yet:
        migrate

 * Migrate up to version 20090612163144 if it's not applied yet:
        migrate 20090612163144

 * Migrate down to version 20090612163144 if it's applied already:
        migrate 20090612163144

 * Apply the first migration that is not applied to the database yet:
        migrate up
 
 * Remove the last applied migration:
        migrate down
        
 * Re-apply the last applied migration:
        migrate redo
        
 * List all the migrations found in the application and their status:
        migrate list
        
 * Create a new migration
        migrate create
        
 * Create a new name migration
        migrate create MyMigrationName

EOD;

    }
    
    /**
     *  Runs the actual command passing along the command line parameters.
     *
     *  @param $args The command line parameters
     */
    public function run($args) {
        try {
            $engine = new CDbMigrationEngine();
            $engine->run($args);
        } catch (Exception $e) {
            echo('FATAL ERROR: ' . $e->getMessage());
        }
    }
    
}