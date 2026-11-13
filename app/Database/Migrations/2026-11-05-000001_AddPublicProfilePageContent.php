<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicProfilePageContent extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('profil_jemaat') && ! $this->db->fieldExists('page_content', 'profil_jemaat')) {
            $this->forge->addColumn('profil_jemaat', [
                'page_content' => ['type' => 'TEXT', 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('profil_jemaat') && $this->db->fieldExists('page_content', 'profil_jemaat')) {
            $this->forge->dropColumn('profil_jemaat', 'page_content');
        }
    }
}
