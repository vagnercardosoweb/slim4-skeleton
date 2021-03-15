<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 15/03/2021 Vagner Cardoso
 */

namespace Core\Mailer;

use Core\Contracts\Mailer as MailerContract;
use PHPMailer\PHPMailer\PHPMailer as LIBPHPMailer;

/**
 * Class PHPMailer.
 */
class PHPMailer implements MailerContract
{
    /**
     * @var \PHPMailer\PHPMailer\PHPMailer
     */
    protected LIBPHPMailer $mailer;

    /**
     * PHPMailer constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->configurePhpMailer($config);
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function from(string $address, ?string $name = null): PHPMailer
    {
        $this->mailer->setFrom($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function reply(string $address, ?string $name = null): PHPMailer
    {
        $this->mailer->addReplyTo($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addCC(string $address, ?string $name = null): PHPMailer
    {
        $this->mailer->addCC($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addBCC(string $address, ?string $name = null): PHPMailer
    {
        $this->mailer->addBCC($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function to(string $address, ?string $name = null): PHPMailer
    {
        $this->mailer->addAddress($address, $name);

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject): PHPMailer
    {
        $this->mailer->Subject = $subject;

        return $this;
    }

    /**
     * @param string      $path
     * @param string|null $name
     *
     * @throws \PHPMailer\PHPMailer\Exception
     *
     * @return $this
     */
    public function addAttachment(string $path, ?string $name = null): PHPMailer
    {
        $this->mailer->addAttachment($path, $name);

        return $this;
    }

    /**
     * @param string $message
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function body(string $message): PHPMailer
    {
        $this->mailer->msgHTML($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function altBody(string $message): PHPMailer
    {
        $this->mailer->AltBody = $message;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    public function send(): LIBPHPMailer
    {
        if (!$this->mailer->send()) {
            throw new \RuntimeException($this->mailer->ErrorInfo);
        }

        $this->clear();

        return $this->mailer;
    }

    /**
     * @return void
     */
    protected function clear(): void
    {
        $this->mailer->clearAddresses();
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearBCCs();
        $this->mailer->clearCCs();
        $this->mailer->clearCustomHeaders();
        $this->mailer->clearReplyTos();
    }

    /**
     * @param array $options
     */
    protected function validateConfig(array &$options): void
    {
        if (empty($options['host'])) {
            throw new \InvalidArgumentException(
                'Host not configured.'
            );
        }

        if (empty($options['username']) || empty($options['password'])) {
            throw new \InvalidArgumentException(
                'User and password not configured.'
            );
        }
    }

    /**
     * @param array $config
     */
    protected function configurePhpMailer(array $config): void
    {
        // PHPMailer
        $this->mailer = new LIBPHPMailer($config['exception'] ?? true);

        // Settings
        $this->mailer->SMTPDebug = $config['debug'] ?? 0;
        $this->mailer->CharSet = $config['charset'] ?? LIBPHPMailer::CHARSET_UTF8;
        $this->mailer->isSMTP();
        $this->mailer->isHTML(true);
        $this->mailer->setLanguage(
            $config['language']['code'] ?? 'pt_br',
            $config['language']['path'] ?? ''
        );

        // Authentication
        $this->mailer->SMTPAuth = $config['auth'] ?? true;

        if (!empty($config['secure'])) {
            $this->mailer->SMTPSecure = $config['secure'] ?? LIBPHPMailer::ENCRYPTION_STARTTLS;
        }

        // Server e-mail
        $this->mailer->Host = $this->buildHost($config['host']);
        $this->mailer->Port = $config['port'] ?? 587;
        $this->mailer->Username = $config['username'];
        $this->mailer->Password = $config['password'];

        // Default from
        if (!empty($config['from']['mail'])) {
            $this->mailer->From = $config['from']['mail'];

            if (!empty($config['from']['name'])) {
                $this->mailer->FromName = $config['from']['name'];
            }
        }
    }

    /**
     * @param string|array $host
     *
     * @return string
     */
    protected function buildHost(array | string $host): string
    {
        if (is_array($host)) {
            $host = implode(';', $host);
        }

        return $host;
    }
}
