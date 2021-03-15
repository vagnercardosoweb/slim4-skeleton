<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 15/03/2021 Vagner Cardoso
 */

namespace Core\Contracts;

/**
 * Interface Message.
 */
interface Mailer
{
    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function from(string $address, ?string $name): self;

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function reply(string $address, ?string $name): self;

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function addCC(string $address, ?string $name): self;

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function addBCC(string $address, ?string $name): self;

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function to(string $address, ?string $name): self;

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject): self;

    /**
     * @param string      $path
     * @param string|null $name
     *
     * @return $this
     */
    public function addAttachment(string $path, ?string $name): self;

    /**
     * @param string $message
     *
     * @return $this
     */
    public function body(string $message): self;

    /**
     * @param string $message
     *
     * @return $this
     */
    public function altBody(string $message): self;

    /**
     * @return mixed
     */
    public function send(): mixed;
}
