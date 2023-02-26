<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
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
                'null' => false,
                'default' => Literal::from('uuid_generate_v4()'),
            ])
            ->changePrimaryKey('id')
            ->addIndex('id')
        ;

        $table
            ->addColumn('user_id', 'uuid', ['null' => false])
            ->addIndex('user_id')
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                ['update' => 'CASCADE', 'delete' => 'CASCADE']
            )
        ;

        $table
            ->addColumn('expired_at', 'timestamp', ['timezone' => true])
            ->addIndex('expired_at')
        ;

        $table->save();
    }

    public function down(): void
    {
        $this->table($this->tableName)->drop()->save();
    }
}
