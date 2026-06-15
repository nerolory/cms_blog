<?php

namespace App\DTO;

/**
 * DTO resolved mailer config.

 *
 * @property-read string $mailerName
 * @property-read string $transport
 * @property-read ?string $host
 * @property-read ?int $port
 * @property-read ?string $scheme
 * @property-read ?string $username
 * @property-read ?string $password
 * @property-read ?string $path
 * @property-read ?string $localDomain
 */
readonly class ResolvedMailerConfig extends AbstractData
{
    public function __construct(public string $mailerName, public string $transport, public ?string $host,
        public ?int $port, public ?string $scheme, public ?string $username, public ?string $password,
        public ?string $path, public ?string $localDomain) {}

    /**
     * Mailer configuration array for Laravel MailManager.
     *
     * @return array<string, mixed>
     */
    public function toMailerArray(): array
    {
        if ($this->transport === 'sendmail') {
            return ['transport' => 'sendmail', 'path' => $this->path ?? '/usr/sbin/sendmail -bs -i'];
        }
        $config = ['transport' => 'smtp', 'host' => $this->host ?? '127.0.0.1', 'port' => $this->port ?? 587,
            'username' => $this->username, 'password' => $this->password, 'timeout' => null,
            'local_domain' => $this->localDomain];
        if ($this->scheme !== null && $this->scheme !== '') {
            $config['scheme'] = $this->scheme;
        }

        return $config;
    }
}
