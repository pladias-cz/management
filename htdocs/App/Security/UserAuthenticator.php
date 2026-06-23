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

        if (!password_verify($password, $row->password)) {
            throw new AuthenticationException('Invalid password.');
        }

        return new Identity(
            $row->id,
            'user',
            ['name' => $row->name, 'surname' => $row->surname],
        );
    }

    /**
     * Hash a password using Argon2id (recommended) or Argon2i
     * Can be used to upgrade legacy passwords to Argon2
     */
    function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

}
