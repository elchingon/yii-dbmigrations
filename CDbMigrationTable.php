<?php

/**
 * CDbTable class file.
 *
 * @author Pieter Claerhout <pieter@yellowduck.be>
 * @link http://github.com/pieterclaerhout/yii-dbmigrations/
 * @copyright Copyright &copy; 2009 Pieter Claerhout
 */

/**
 *  This class abstracts a database class. You can use it to easily create
 *  tables and fields.
 *
 *  @package extensions.yii-dbmigrations
 */
class CDbMigrationTable {
    
    /**
     *  The name of the table.
     */
    public $name;
    
    /**
     *  The options for the table.
     */
    public $options;
    
    /**
     *  The list of columns for the table.
     */
    public $columns = array();
    
    /**
     *  The list of indexes for the table.
     */
    public $indexes = array();
    
    /**
     * Constructor
     *
     *  @param $name    The name of the table to create.
     *  @param $options The extra options to pass to the database creation.
     */
    public function __construct($name, $options=null) {
        $this->name    = $name;
        $this->options = $options;
    }
    
    /**
     *  Add a primary key to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function primary_key($name='id', $options=null) {
        $this->addField($name, 'primary_key', $options);
    }
    
    /**
     *  Add a string field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function string($name, $options=null) {
        $this->addField($name, 'string', $options);
    }
    
    /**
     *  Add a text field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function text($name, $options=null) {
        $this->addField($name, 'text', $options);
    }
    
    /**
     *  Add an integer field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function integer($name, $options=null) {
        $this->addField($name, 'integer', $options);
    }
    
    /**
     *  Add a float field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function float($name, $options=null) {
        $this->addField($name, 'float', $options);
    }
    
    /**
     *  Add a decimal field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function decimal($name, $options=null) {
        $this->addField($name, 'decimal', $options);
    }
    
    /**
     *  Add a datetime field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function datetime($name, $options=null) {
        $this->addField($name, 'datetime', $options);
    }
    
    /**
     *  Add a timestamp field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function timestamp($name, $options=null) {
        $this->addField($name, 'timestamp', $options);
    }
    
    /**
     *  Add a time field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function time($name, $options=null) {
        $this->addField($name, 'time', $options);
    }
    
    /**
     *  Add a date field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function date($name, $options=null) {
        $this->addField($name, 'date', $options);
    }
    
    /**
     *  Add a binary field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function binary($name, $options=null) {
        $this->addField($name, 'binary', $options);
    }
    
    /**
     *  Add a boolean field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function boolean($name, $options=null) {
        $this->addField($name, 'boolean', $options);
    }
    
    /**
     *  Add a boolean field to the table.
     *
     *  @param $name    The name of the primary key column.
     *  @param $options The extra options to pass to the column.
     */
    public function bool($name, $options=null) {
        $this->addField($name, 'bool', $options);
    }
    
    /**
     *  Add a non-unique index to the table.
     *
     *  @param $name    The name of the index.
     *  @param $columns The column(s) to add to the index
     *  @param $unique  Whether a unique or non unique index should be created.
     */
    public function index($name, $columns, $unique=false) {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        $this->indexes[] = array($name, $columns, $unique);
    }
    
    /**
     *  Add a unique index to the table.
     *
     *  @param $name    The name of the index.
     *  @param $columns The column(s) to add to the index
     */
    public function unique($name, $columns) {
        $this->index($name, $columns, true);
    }
    
    /**
     *  Helper function to add a field to the column definition.
     *
     *  @param $name    The name of the field to add.
     *  @param $type    The type of the field to add.
     *  @param $options The extra options to pass to the column.
     */
    protected function addField($name, $type, $options=null) {
        $this->columns[] = array($name, $type, $options);
    }
    
}