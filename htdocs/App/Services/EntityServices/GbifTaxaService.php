<?php declare(strict_types=1);

namespace App\Services\EntityServices;

use Doctrine\ORM\QueryBuilder;
use Nette\Utils\ArrayHash;
use Pladias\ORM\Entity\Gbif\Taxa;
use Pladias\ORM\Entity\Public\Taxons;

class GbifTaxaService extends BaseEntityService
{
    protected string $entityName = Taxa::class;

    public function getRanks(): array
    {
        $values = $this->getQueryBuilder()
            ->select('DISTINCT a.taxonRank as rank')
            ->orderBy('a.taxonRank', 'ASC')
            ->getQuery()
            ->getResult();

        return array_combine(
            array_column($values, 'rank'),
            array_column($values, 'rank')
        );
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $qb = parent::getQueryBuilder();
        return $qb->leftJoin('a.pladiasTaxon', 't')
            ->orderBy('t.lft', 'ASC');
    }

    public function removeMapping(int $id): self
    {
        $mapping = $this->find($id);
        $mapping->setPladiasTaxon(null);
        $this->entityManager->flush();
        return $this;
    }

    public function addMapping(int $id, ArrayHash $values)
    {
        $pladias = $this->entityManager->getRepository(Taxons::class)->findOneBy(['nameLatin' => $values['pladiasTaxon']]);
        $gbif = $this->find($id);

        $gbif->setPladiasTaxon($pladias);
        $this->entityManager->flush();

    }
}
