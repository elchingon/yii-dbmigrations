<?php

class m20090611153243_CreateTables extends CDbMigration {
    
    // Apply the migration
    public function up() {
        
        // Create the posts table
        $t = $this->newTable('posts');
        $t->primary_key('id');
        $t->string('title');
        $t->text('body');
        $t->index('posts_title', 'title');
        $this->addTable($t);
        
    }
    
    // Remove the migration
    public function down() {
        
        // Remove the table
        $this->removeTable('posts');
        
    }
    
}