<?php

class m20090612162832_CreateTags extends CDbMigration {
    
    public function up() {
        
        $this->createTable(
            'tags',
            array(
                array('id', 'primary_key'),
                array('name', 'string'),
            )
        );
        
        $this->createTable(
            'post_tags',
            array(
                array('id', 'primary_key'),
                array('post_id', 'int'),
                array('tag_id', 'int'),
            )
        );
        
        $this->addIndex(
            'post_tags', 'post_tags_post_tag', array('post_id', 'tag_id'), true
        );
        
    }
    
    public function down() {
        $this->removeTable('post_tags');
        $this->removeTable('tags');
    }
    
}