<?php

namespace App\Console;

use App\Services\TempDir;
use Doctrine\ORM\EntityManagerInterface;
use Pladias\ORM\Entity\Atlas\RecordValidationStatus;
use Pladias\ORM\Entity\Bayernflora\DistributionNonautomatic;
use Pladias\ORM\Entity\Bayernflora\FSGTaxons;
use Pladias\ORM\Entity\Geodata\Quadrants;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'gbif:austriaOnlyZmizik', description: 'Import data from CSV file into database')]
class AustriaOnlyZmizikCommand extends Command
{
    public const string SEPARATOR = "	";

    public function __construct(private EntityManagerInterface $entityManager, private TempDir $tempDir)
    {
        parent::__construct();
    }

    protected function searchRelevantFsgTaxaIds(): array
    {

        $sql = 'SELECT t.id FROM bayernflora.taxons_fsg t
           WHERE t.is_fvd = true
            AND NOT EXISTS (SELECT 1 FROM bayernflora.taxons_convertor c WHERE c.fsg_taxon_id = t.id AND pladias_taxon IS NOT NULL )
             AND EXISTS (SELECT 1 FROM gbif.taxa g WHERE g.species = t.name_lat)
             -- AND EXISTS (SELECT 1 FROM gbif.taxa g WHERE  g.accepted_scientific_name LIKE t.name_lat|| \'%\')
             ORDER BY t.name_lat';
        $query = $this->entityManager->getConnection()->prepare($sql);
        $ids = $query->executeQuery()->fetchFirstColumn();
        return $this->entityManager->getRepository(FSGTaxons::class)->findBy(['id' => $ids]);
    }

    protected function getQuadrantsFromGbif(FSGTaxons $FSGTaxon): array
    {
        /**
         * tester Campanula scheuchzeri
         */
        $sql = ' SELECT DISTINCT q.id
                FROM geodata.quadrants_full q
                 JOIN geodata.regions reg ON st_intersects(q.geom_wgs, reg.geom)
                 JOIN gbif.records r ON st_intersects(r.coords, q.geom_wgs)
                 JOIN gbif.taxa gt ON gt.taxon_key = r.taxon_key

                  WHERE reg.id = 4
                  AND gt.species = :taxonName';
        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('taxonName', $FSGTaxon->nameLat);
        return $query->executeQuery()->fetchFirstColumn();

    }

    protected function getQuadrantIdsFromZmizik(FSGTaxons $fsgTaxon): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('IDENTITY(d.quadrant) AS quadrantId')
            ->from(DistributionNonautomatic::class, 'd')
            ->where('d.fsgTaxon = :taxon')
            ->setParameter('taxon', $fsgTaxon);

        $result = $qb->getQuery()->getScalarResult();

        return array_map(
            static fn(array $row) => (int)$row['quadrantId'],
            $result
        );
    }

    protected function addZmizik(FSGTaxons $FSGTaxon, int $quadId)
    {
        $zmizik = new DistributionNonautomatic();
        $zmizik->setFsgTaxon($FSGTaxon);
        $zmizik->setQuadrant($this->entityManager->getRepository(Quadrants::class)->find($quadId));
        $zmizik->setStatus($this->entityManager->getRepository(RecordValidationStatus::class)->find(3));
        $zmizik->setRemark('created automatic on ' . date('Y-m-d'));
        $zmizik->setCreatedByMachine(true);
        $this->entityManager->persist($zmizik);
        $this->entityManager->flush();
    }

    protected function checkRemoveZmizik(FSGTaxons $FSGTaxon, int $quadId)
    {
        $quadrant = $this->entityManager->getRepository(Quadrants::class)->find($quadId);
        $zmizik = $this->entityManager->getRepository(DistributionNonautomatic::class)->findOneBy(['fsgTaxon' => $FSGTaxon, 'quadrant' => $quadrant]);
        if ($zmizik->createdByMachine) {
            $this->entityManager->remove($zmizik);
            $this->entityManager->flush();
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting austria-only zmizik...</info>');
        $output->writeln('<info>taxon - quads in GBIFdata | quads in zmizik</info>');

        $fsgTaxa = $this->searchRelevantFsgTaxaIds();

        foreach ($fsgTaxa as $fsgTaxon) {

            $quadrantsInGbif = $this->getQuadrantsFromGbif($fsgTaxon);
            $quadrantsInZmizik = $this->getQuadrantIdsFromZmizik($fsgTaxon);

            // v GBIF jsou, ve zmiziku chybí
            $missingInZmizik = array_diff($quadrantsInGbif, $quadrantsInZmizik);
            if ($missingInZmizik) {
                foreach ($missingInZmizik as $quadId) {
                    $this->addZmizik($fsgTaxon, $quadId);
                }
            }

            // ve zmiziku jsou navíc
            $extraInZmizik = array_diff($quadrantsInZmizik, $quadrantsInGbif);
            if ($extraInZmizik) {
                foreach ($extraInZmizik as $quadId) {
                    $this->checkRemoveZmizik($fsgTaxon, $quadId);
                }
            }

            // společné
            $common = array_intersect($quadrantsInGbif, $quadrantsInZmizik);


            $output->writeln('<info>' . $fsgTaxon->nameLat . ' - ' . count($quadrantsInGbif) . ' | ' . count($quadrantsInZmizik) . '</info>');
        }
        $output->writeln('<info>---- all taxa processed</info>');
        return Command::SUCCESS;
    }
}
