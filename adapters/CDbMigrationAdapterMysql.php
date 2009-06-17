<?php

/**
 * CDbMigrationAdapterMysql class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  The SQLite specific version of the database migration adapter.
 *
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
    
    /**
     *  Retrieve the type information from a database column.
     *
     *  @returns The current data type of the column.
     */
    public function columnInfo($table, $name) {
        
        // Get the column info from the database
        $sql = 'SHOW COLUMNS FROM ' . $this->db->quoteTableName($table)
             . ' LIKE ' . $this->db->quoteValue($name);
        $columnInfo = $this->db->createCommand($sql)->queryRow();
        
        // Check if we have column info
        if ($columnInfo === false) {
            throw new CDbMigrationException(
                'Column: ' . $name . ' not found in table: ' . $table
            );
        }
        
        // Construct the column type as text
        $type = $columnInfo['Type'];
        if ($columnInfo['Null'] !== 'YES') {
            $type .= ' NOT NULL';
        }
        if (!empty($columnInfo['Default'])) {
            $type .= ' DEFAULT ' . $this->db->quoteValue($columnInfo['Default']);
        }
        if (!empty($columnInfo['Extra'])) {
            $type .= ' ' . $columnInfo['Extra'];
        }
        
        // Return the column type
        return $type;
        
    }
    
}
