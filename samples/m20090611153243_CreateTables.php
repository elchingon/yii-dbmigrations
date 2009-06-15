<?php

class m20090611153243_CreateTables extends CDbMigration {
    
    public function up() {
        
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