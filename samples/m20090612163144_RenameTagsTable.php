<?php

class m20090612163144_RenameTagsTable extends CDbMigration {
    
    // Apply the migration
    public function up() {
        
        // Rename a table
        $this->renameTable('post_tags', 'post_tags_link_table');
        
    }
    
    // Remove the migration
    public function down() {
        
        // Undo the table rename
        $this->renameTable('post_tags_link_table', 'post_tags');
        
    }
    
}