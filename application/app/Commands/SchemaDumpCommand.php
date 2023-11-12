<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace App\Commands;

use Core\Config;
use Core\Support\Env;
use Core\Support\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SchemaDumpCommand extends Command
{
    /**
     * @var \PDO
     */
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        parent::__construct('schema:dump');
        $this->setDescription('Generate a schema.sql from the schema data source.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driver = Env::get('DB_DRIVER', 'pgsql');

        if ('mysql' !== $driver) {
            throw new \InvalidArgumentException(sprintf('Driver [%s] not supported.', $driver));
        }

        $database = Env::get('DB_DATABASE');
        $migrationName = Config::get('phinx.environments.default_migration_table', 'migrations');

        $output->writeln(sprintf('Use database: %s', $database));

        $statement = $this->query("SELECT TABLE_NAME
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME != '{$migrationName}'");

        $sql = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $statement2 = $this->query(sprintf('SHOW CREATE TABLE %s;', $row['TABLE_NAME']));
            $createTableSql = $statement2->fetch(\PDO::FETCH_ASSOC)['Create Table'];
            $sql[] = preg_replace('/AUTO_INCREMENT=\d+/', '', $createTableSql).';';
        }

        $sql = implode("\n\n", $sql);
        $filename = Path::resources('/database/schema.sql');
        file_put_contents($filename, $sql);

        $output->writeln(sprintf('Generated file: %s', realpath($filename)));
        $output->writeln('<info>Done</info>');

        return self::SUCCESS;
    }

    private function query(string $sql): \PDOStatement
    {
        $statement = $this->pdo->query($sql);

        if (!$statement) {
            throw new \UnexpectedValueException('Query failed');
        }

        return $statement;
    }
}
