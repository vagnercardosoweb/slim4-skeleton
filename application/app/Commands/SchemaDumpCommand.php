<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 21/02/2021 Vagner Cardoso
 */

namespace App\Commands;

use Core\Support\Path;
use PDO;
use PDOStatement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * Class SchemaDumpCommand.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
final class SchemaDumpCommand extends Command
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * The constructor.
     *
     * @param PDO         $pdo  The database connection
     * @param string|null $name The name
     */
    public function __construct(PDO $pdo, ?string $name = null)
    {
        parent::__construct($name);
        $this->pdo = $pdo;
    }

    /**
     * Configure.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('schema:dump');
        $this->setDescription('Generate a schema.sql from the schema data source.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input  The input
     * @param OutputInterface $output The output
     *
     * @return int The error code, 0 on success
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Lazy loading, because the database may not exists
        $output->writeln(sprintf('Use database: %s', (string)$this->query('select database()')->fetchColumn()));

        $statement = $this->query('SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = DATABASE()');

        $sql = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $row = array_change_key_case($row);
            $statement2 = $this->query(sprintf('SHOW CREATE TABLE `%s`;', (string)$row['table_name']));
            $createTableSql = $statement2->fetch(PDO::FETCH_ASSOC)['Create Table'];
            $sql[] = preg_replace('/AUTO_INCREMENT=\d+/', '', $createTableSql).';';
        }

        $sql = implode("\n\n", $sql);
        $filename = Path::resources('/database/schema.sql');
        file_put_contents($filename, $sql);

        $output->writeln(sprintf('Generated file: %s', realpath($filename)));
        $output->writeln(sprintf('<info>Done</info>'));

        return self::SUCCESS;
    }

    /**
     * Create query statement.
     *
     * @param string $sql The sql
     *
     * @throws UnexpectedValueException
     *
     * @return PDOStatement The statement
     */
    private function query(string $sql): PDOStatement
    {
        $statement = $this->pdo->query($sql);

        if (!$statement) {
            throw new UnexpectedValueException('Query failed');
        }

        $statement->setFetchMode(PDO::FETCH_ASSOC);

        return $statement;
    }
}
