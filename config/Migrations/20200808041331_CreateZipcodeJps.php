<?php
use Migrations\AbstractMigration;

class CreateZipcodeJps extends AbstractMigration
{
    public function up()
    {
        $this->table('zipcode_jps')
        ->addColumn('zipcode', 'string', [
            'comment' => '郵便番号',
            'limit' => 7,
            'null' => false,
        ])
        ->addColumn('pref', 'string', [
            'comment' => '都道府県名',
            'limit' => 255,
            'null' => false,
        ])
        ->addColumn('city', 'string', [
            'comment' => '市区町村名',
            'limit' => 255,
            'null' => false,
        ])
        ->addColumn('address', 'string', [
            'comment' => '町域名',
            'limit' => 255,
            'null' => false,
        ])
        ->create();
    }

    public function down()
    {
        $this->table('zipcode_jps')->drop()->save();
    }
}
