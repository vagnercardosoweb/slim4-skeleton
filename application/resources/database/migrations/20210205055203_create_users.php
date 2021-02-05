<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/02/2021 Vagner Cardoso
 */

declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

/**
 * Class CreateUsers.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
final class CreateUsers extends AbstractMigration
{
    protected string $tableName = 'users';

    protected array $options = ['id' => false];

    public function up(): void
    {
        $table = $this->table($this->tableName, $this->options);

        $table
            ->addColumn('id', 'uuid', [
                'limit' => 16,
                'default' => Literal::from('(UUID())'),
            ])
            ->addIndex('id')
        ;

        $table
            ->addColumn('name', 'string', ['limit' => 128])
            ->addIndex('name')
        ;

        $table
            ->addColumn('email', 'string', ['limit' => 128])
            ->addIndex('email', ['unique' => true])
        ;

        $table->addColumn('password', 'string', ['limit' => 128]);

        $table
            ->addTimestampsWithTimezone()
            ->addIndex('created_at')
            ->addIndex('updated_at')
        ;

        $table->save();
    }

    public function down(): void
    {
        $this->table($this->tableName)->drop()->save();
    }
}
