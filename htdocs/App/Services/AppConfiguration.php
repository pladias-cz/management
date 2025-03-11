<?php declare(strict_types=1);

namespace App\Services;


final readonly class AppConfiguration
{

    /**
     * @param mixed[] $config
     */
    public function __construct(private array $config)
    {
    }

    public function isProduction(): bool
    {
        return $this->getPlatform() === 'production';
    }

    public function getPlatform(): ?string
    {
        if (!isset($this->config['environment'])) {
            return null;
        }

        return $this->config['environment'];
    }

    public function getPasswordCipherKey(): string
    {
        return $this->config['CipherSecretKey'];
    }

}
