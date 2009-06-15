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
        return 'Used to run database migrations';
    }
    
    /**
     *  Runs the actual command passing along the command line parameters.
     *
     *  @param $args The command line parameters
     */
    public function run($args) {
        $engine = new CDbMigrationEngine();
        $engine->run($args);
    }
    
}