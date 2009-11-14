<?php

class m20090612162832_CreateTags extends CDbMigration {
    
    // Apply the migration
    public function up() {
        
        // Create the tags table
        $t = $this->newTable('tags');
        $t->primary_key();
        $t->string('name');
        $this->addTable($t);
        
        // Create the post_tags table
        $t = $this->newTable('post_tags');
        $t->primary_key();
        $t->integer('post_id');
        $t->integer('tag_id');
        $t->index('post_tags_post_tag', array('post_id', 'tag_id'), true);
        $this->addTable($t);
        
    }
    
    // Remove the migration
    public function down() {
        
        // Remove the tables
        $this->removeTable('post_tags');
        $this->removeTable('tags');
        
    }
    
}