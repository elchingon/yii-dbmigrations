<?php

class CDbMigrationAdapterMysql extends CDbMigrationAdapter {
    
    // Return a map of the native database types
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
