<?php

/**
 * CDbMigrationAdapterSqlite class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  @package extensions.yii-dbmigrations
 */
class CDbMigrationAdapterSqlite extends CDbMigrationAdapter {
    
    // Return a map of the native database types
    protected $nativeDatabaseTypes = array(
        'primary_key' => 'INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL',
        'string' => 'varchar(255)',
        'text' => 'text',
        'integer' => 'integer',
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
    
    // Rename a table
    public function renameTable($name, $new_name) {
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($name) . ' RENAME TO '
             . $this->db->quoteTableName($new_name);
        return $this->execute($sql);
    }
    
    // Change a database column
    public function changeColumn($table, $column, $type, $options=null) {
        throw new CDbMigrationException(
            'changeColumn is not supported for SQLite'
        );
    }
    
    // Change a database column
    public function renameColumn($table, $name, $new_name) {
        throw new CDbMigrationException(
            'renameColumn is not supported for SQLite'
        );
    }
    
    // Remove a column
    public function removeColumn($table, $column) {
        throw new CDbMigrationException(
            'removeColumn is not supported for SQLite'
        );
    }
    
}
