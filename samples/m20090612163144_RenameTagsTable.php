<?php

class m20090612163144_RenameTagsTable extends CDbMigration {
    
    public function up() {
        $this->renameTable(
            'post_tags', 'post_tags_link_table'
        );
    }
    
    public function down() {
        $this->renameTable(
            'post_tags_link_table', 'post_tags'
        );
    }
    
}