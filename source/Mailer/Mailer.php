<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

namespace Core\Mailer;

use Core\Contracts\Mailer as MailerContract;

/**
 * Class PHPMailer.
 */
class Mailer
{
    /**
     * PHPMailer constructor.
     *
     * @param \Core\Contracts\Mailer $mailer
     */
    public function __construct(
        protected MailerContract $mailer
    ) {
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return Mailer
     */
    public function from(string $address, ?string $name = null): Mailer
    {
        $this->mailer->from($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return Mailer
     */
    public function reply(string $address, ?string $name = null): Mailer
    {
        $this->mailer->addReply($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return Mailer
     */
    public function addCC(string $address, ?string $name = null): Mailer
    {
        $this->mailer->addToCc($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return Mailer
     */
    public function addBCC(string $address, ?string $name = null): Mailer
    {
        $this->mailer->addToBcc($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @return Mailer
     */
    public function to(string $address, ?string $name = null): Mailer
    {
        $this->mailer->addTo($address, $name);

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return Mailer
     */
    public function subject(string $subject): Mailer
    {
        $this->mailer->subject($subject);

        return $this;
    }

    /**
     * @param string      $path
     * @param string|null $name
     *
     * @return Mailer
     */
    public function addFile(string $path, ?string $name = null): Mailer
    {
        $this->mailer->addFile($path, $name);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return Mailer
     */
    public function body(string $message): Mailer
    {
        $this->mailer->body($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return Mailer
     */
    public function altBody(string $message): Mailer
    {
        $this->mailer->altBody($message);

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    public function send(): mixed
    {
        return $this->mailer->send();
    }
}
