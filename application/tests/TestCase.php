<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Tests\Traits\AppTestTrait;

/**
 * Class TestCase.
 *
 * @internal
 *
 * @coversNothing
 */
class TestCase extends PHPUnitTestCase
{
    use AppTestTrait;

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        // if (method_exists($this, 'tearDownDatabase')) {
        //     $this->tearDownDatabase();
        // }
    }
}
