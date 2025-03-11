<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use Pladias\ORM\Entity\Gbif\Taxa;

class GbifTaxaService extends BaseEntityService
{
    protected string $entityName = Taxa::class;

}
