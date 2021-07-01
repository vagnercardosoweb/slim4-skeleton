<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
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
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function addTo(string $address, ?string $name): self;

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function addToCc(string $address, ?string $name): self;

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function addToBcc(string $address, ?string $name): self;

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return $this
     */
    public function addReply(string $address, ?string $name): self;

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
    public function addFile(string $path, ?string $name): self;

    /**
     * @return mixed
     */
    public function send(): mixed;
}
