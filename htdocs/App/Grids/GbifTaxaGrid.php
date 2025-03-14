<?php declare(strict_types=1);

namespace App\Grids;


use App\Services\EntityServices\GbifTaxaService;
use App\UI\Admin\Gbif\GbifPresenter;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Control;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Pladias\ORM\Entity\Public\Taxons;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class GbifTaxaGrid extends Control
{

    private DataGrid $grid;

    public function __construct(protected readonly GbifTaxaService $gbifTaxaService, protected readonly BaseGridFactory $gridFactory, private readonly User $user, private EntityManagerInterface $entityManager)
    {
        $this->grid = $this->gridFactory->createBaseDatagrid();
        if (!in_array($this->user->id, GbifPresenter::$allowedUsers)) {
            throw new AuthenticationException();
        }
    }

    public function create(): self
    {
        return $this;
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/gbifTaxaGrid.latte');
        $template->render();
    }

    public function handleRemove(int $id): void
    {
        try {
            $this->gbifTaxaService->removeMapping($id);
        } catch (\Exception $e) {
            $this->presenter->flashMessage("It is not possible to remove the mapping.", 'danger');
        }
        $this->redirect('this');
    }

    public function createComponentGrid(): DataGrid
    {
        $this->grid->setDataSource($this->defaultDatasource($this->user))->setDefaultSort(['id' => Criteria::DESC])->setRememberState(false);
        $this->grid->addColumnText('scientificName', 'GBIF scientificName')->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
            $qb->andWhere('LOWER(a.scientificName) LIKE LOWER(:name)')
                ->setParameter('name', $value . '%');
        });
        $this->grid->addColumnText('acceptedScientificName', 'GBIF accepted scientificName')->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
            $qb->andWhere('LOWER(a.acceptedScientificName) LIKE LOWER(:name)')
                ->setParameter('name', $value . '%');
        });

        $this->grid->addColumnText('species', 'GBIF species')
            ->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('LOWER(a.species) LIKE LOWER(:name)')
                    ->setParameter('name', $value . '%');
            });

        $this->grid->addColumnText('taxonRank', 'GBIF rank')
            ->setFilterSelect(BaseGridFactory::FILTER_NOTHING + $this->gbifTaxaService->getRanks())
            ->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('a.taxonRank = :rank')
                    ->setParameter('rank', $value);
            });


        $this->grid->addColumnText('pladiasTaxon', 'Pladias taxon')
            ->setRenderer(function ($value) {
                return $value->pladiasTaxon?->nameLatin;
            })
            ->setEditableCallback([$this, 'pladiasTaxonEdited'])
            ->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('LOWER(t.nameLatin) LIKE LOWER(:name)')
                    ->setParameter('name', $value . '%');
            });

        $this->grid->addExportCsvFiltered('Csv export (filtered)', 'curator_imported.csv')
            ->setTitle('Csv export (filtered)');

        $this->grid->addAction('remove', '', 'remove!')
            ->setIcon('trash')
            ->setTitle('Remove')
            ->setClass('btn btn-xs btn-danger')
            ->setConfirmation(
                new StringConfirmation('Remove mapping of Pladias-GBIF taxon?', 'id')
            );

        return $this->grid;
    }

    protected function defaultDatasource(User $user): QueryBuilder
    {
        return $this->gbifTaxaService->getQueryBuilder();
    }

    public function pladiasTaxonEdited($id, $value)
    {
        $pladias = $this->entityManager->getRepository(Taxons::class)->findOneBy(['nameLatin' => $value]);
        $gbif = $this->gbifTaxaService->find((int)$id);
        if ($pladias) {
            $gbif->setPladiasTaxon($pladias);
            $this->entityManager->flush();
            return $value;
        } else {
            return $gbif->pladiasTaxon?->nameLatin;
        }
    }

}
