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
use PHPMailer\PHPMailer\PHPMailer as LIBPHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Class PHPMailer.
 */
class PHPMailer extends LIBPHPMailer implements MailerContract
{
    /**
     * PHPMailer constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config['exception'] ?? true);

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
        $this->setFrom($address, $name);

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
    public function addTo(string $address, ?string $name = null): PHPMailer
    {
        $this->addAddress($address, $name);

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
    public function addReply(string $address, ?string $name = null): PHPMailer
    {
        $this->addReplyTo($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @throws \PHPMailer\PHPMailer\Exception
     *
     * @return $this
     */
    public function addToBcc(string $address, ?string $name = null): PHPMailer
    {
        $this->addBCC($address, $name);

        return $this;
    }

    /**
     * @param string      $address
     * @param string|null $name
     *
     * @throws \PHPMailer\PHPMailer\Exception
     *
     * @return $this
     */
    public function addToCc(string $address, ?string $name = null): PHPMailer
    {
        $this->addCC($address, $name);

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject): PHPMailer
    {
        $this->Subject = $subject;

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
    public function addFile(string $path, ?string $name = null): PHPMailer
    {
        $this->addAttachment($path, $name);

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
        $this->msgHTML($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function altBody(string $message): PHPMailer
    {
        $this->AltBody = $message;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    public function send(): LIBPHPMailer
    {
        if (!parent::send()) {
            throw new \RuntimeException($this->ErrorInfo);
        }

        $this->clear();

        return $this;
    }

    /**
     * @return void
     */
    protected function clear(): void
    {
        $this->clearAddresses();
        $this->clearAllRecipients();
        $this->clearAttachments();
        $this->clearBCCs();
        $this->clearCCs();
        $this->clearCustomHeaders();
        $this->clearReplyTos();
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
        // Settings
        $this->SMTPDebug = $config['debug'] ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
        $this->CharSet = $config['charset'] ?? LIBPHPMailer::CHARSET_UTF8;
        $this->isSMTP();
        $this->isHTML(true);

        // Language
        $langCode = $config['language']['code'] ?? 'pt_br';
        $langPath = $config['language']['path'] ?? '';

        $this->setLanguage($langCode, $langPath);

        // Authentication
        $this->SMTPAuth = $config['auth'] ?? true;

        if (!empty($config['secure'])) {
            $this->SMTPSecure = $config['secure'] ?? LIBPHPMailer::ENCRYPTION_STARTTLS;
        }

        // Server e-mail
        $this->Host = $this->buildHost($config['host']);
        $this->Port = $config['port'] ?? 587;
        $this->Username = $config['username'];
        $this->Password = $config['password'];

        // Default from
        if (!empty($config['from']['mail'])) {
            $this->From = $config['from']['mail'];

            if (!empty($config['from']['name'])) {
                $this->FromName = $config['from']['name'];
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
