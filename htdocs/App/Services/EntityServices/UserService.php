<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use Pladias\ORM\Entity\Public\Users;

class UserService extends BaseEntityService
{
    protected string $entityName = Users::class;

}
