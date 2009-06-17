<?php

/**
 * CDbMigrationAdapterMysql class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  @package extensions.yii-dbmigrations
 */
class CDbMigrationAdapterMysql extends CDbMigrationAdapter {
    
    /**
     *  The mapping of the database type definitions to the native database
     *  types of the database backend.
     */
    protected $nativeDatabaseTypes = array(
        'primary_key' => 'int(11) DEFAULT NULL auto_increment PRIMARY KEY',
        'string' => 'varchar(255)',
        'text' => 'text',
        'integer' => 'int(4)',
        'float' => 'float',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
        'timestamp' => 'datetime',
        'time' => 'time',
        'date' => 'date',
        'binary' => 'blob',
        'boolean' => 'tinyint(1)',
        'bool' => 'tinyint(1)',
    );
    
}
