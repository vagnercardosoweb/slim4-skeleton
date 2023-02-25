<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace Tests\Fixture;

/**
 * Class FixtureInterface.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
interface Fixture
{
    /**
     * Returns the name of the table.
     *
     * @return string
     */
    public function getTable(): string;

    /**
     * Returns collection with data for insertion into the bank.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecords(): array;
}
