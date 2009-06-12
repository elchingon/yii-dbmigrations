<?php

class m20090611153243_CreateTables extends CDbMigration {
    
    public function up() {
        
        /*
        $tables = $this->query("show tables");
        $this->renameTable('test_table', 'posts');
        $this->removeIndex('test_table', 'idx_pk');
        $this->removeColumn('test_table', 'comment_id');
        $this->changeColumn('test_table', 'comment_id', 'boolean');
        $this->addColumn('test_table', 'title', 'string');
        $this->addIndex('posts', 'posts_sort', array('field1', 'field2'), true);
        $this->removeTable('test_table');
        */
        
        $this->createTable(
            'posts',
            array(
                array('id', 'primary_key'),
                array('title', 'string'),
                array('body', 'text'),
            )
        );
        
        $this->addIndex(
            'posts', 'posts_title', array('title')
        );
        
    }
    
    public function down() {
        $this->removeTable('posts');
    }
    
}