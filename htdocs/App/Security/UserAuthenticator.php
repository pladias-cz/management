<?php declare(strict_types=1);

namespace App\Security;

use App\Services\AppConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\SimpleIdentity;
use Pladias\ORM\Entity\Public\Users;

final readonly class UserAuthenticator implements Authenticator
{

    public function __construct(private EntityManagerInterface $entityManager, private AppConfiguration $config)
    {
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->entityManager->getRepository(Users::class)->findOneByEmail($username);
        if (!$row) {
            throw new AuthenticationException('User not found.');
        }

        if (!$this->verifyPassword($password, $row->password)) {
            throw new AuthenticationException('Invalid password.');
        }

        return new Identity(
            $row->id,
            'user',
            ['name' => $row->name, 'surname' => $row->surname],
        );
    }

    function verifyPassword($inputPassword, $storedEncryptedPassword): bool
    {
        $encryptedInputPassword = $this->encryptPassword($inputPassword);
        return $encryptedInputPassword === $storedEncryptedPassword;
    }

    /**
     * imitates the pladias.ibot.cas.cz cipher process
     */
    function encryptPassword($password): string
    {
        $key = base64_decode($this->config->getPasswordCipherKey());
        $encrypted = openssl_encrypt($password, 'des-ede3-ecb', $key, OPENSSL_RAW_DATA);
        return base64_encode($encrypted);
    }

}
