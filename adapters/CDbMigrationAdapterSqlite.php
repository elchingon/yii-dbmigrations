<?php

/**
 * CDbMigrationAdapterSqlite class file.
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
class CDbMigrationAdapterSqlite extends CDbMigrationAdapter {
    
    /**
     *  The mapping of the database type definitions to the native database
     *  types of the database backend.
     */
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
    
    /**
     *  Retrieve the type information from a database column.
     *
     *  @returns The current data type of the column.
     */
    public function columnInfo($table, $name) {
        throw new CDbMigrationException(
            'columnInfo is not supported for SQLite'
        );
    }
    
    /**
     *  Rename a table.
     *
     *  @param $name     The name of the table to rename.
     *  @param $new_name The new name for the table.
     */
    public function renameTable($name, $new_name) {
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($name) . ' RENAME TO '
             . $this->db->quoteTableName($new_name);
        return $this->execute($sql);
    }
    
    /**
     *  Rename a database column in an existing table.
     *
     *  @param $table    The table to rename the column from.
     *  @param $name     The current name of the column.
     *  @param $new_name The new name of the column.
     */
    public function renameColumn($table, $name, $new_name) {
        throw new CDbMigrationException(
            'renameColumn is not supported for SQLite'
        );
    }
    
    /**
     *  Change a database column in an existing table.
     *
     *  @param $table The name of the table to change the column from.
     *  @param $column The name of the column to change.
     *  @param $type   The new data type for the column.
     *  @param $options The extra options to pass to the column.
     */
    public function changeColumn($table, $column, $type, $options=null) {
        throw new CDbMigrationException(
            'changeColumn is not supported for SQLite'
        );
    }
    
    /**
     *  Remove a table column from the database.
     *
     *  @param $table  The name of the table to remove the column from.
     *  @param $column The name of the table column to remove.
     */
    public function removeColumn($table, $column) {
        throw new CDbMigrationException(
            'removeColumn is not supported for SQLite'
        );
    }
    
}
