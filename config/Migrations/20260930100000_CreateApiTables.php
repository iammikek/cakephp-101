<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateApiTables extends BaseMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addIndex(['email'], ['unique' => true, 'name' => 'users_email_unique'])
            ->create();

        $this->table('categories')
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addIndex(['name'], ['unique' => true, 'name' => 'categories_name_unique'])
            ->create();

        $this->table('items')
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('category_id', 'integer', ['null' => true])
            ->addForeignKey('category_id', 'categories', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
