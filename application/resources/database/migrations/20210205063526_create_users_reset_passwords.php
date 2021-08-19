<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

/**
 * Class CreateUsersResetPasswords.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
final class CreateUsersResetPasswords extends AbstractMigration
{
    protected string $tableName = 'users_reset_passwords';

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
            ->addColumn('user_id', 'uuid', ['limit' => 16])
            ->addIndex('user_id')
            ->addForeignKey('user_id', 'users', 'id', [
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
        ;

        $table
            ->addColumn('expired_at', 'datetime')
            ->addIndex('expired_at')
        ;

        $table->save();
    }

    public function down(): void
    {
        $this->table($this->tableName)->drop()->save();
    }
}
