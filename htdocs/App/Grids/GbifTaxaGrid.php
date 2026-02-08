<?php declare(strict_types=1);

namespace App\Grids;


use App\Services\EntityServices\GbifTaxaService;
use App\UI\Admin\Gbif\GbifPresenter;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\DataGrid\Datagrid;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Control;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

class GbifTaxaGrid extends Control
{

    private DataGrid $grid;

    public function __construct(protected readonly GbifTaxaService $service, protected readonly BaseGridFactory $gridFactory, private readonly User $user)
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
            $this->service->removeMapping($id);
        } catch (\Exception $e) {
            $this->presenter->flashMessage("It is not possible to remove the mapping.", 'danger');
        }
        $this->redirect('this');
    }

    public function createComponentGrid(): DataGrid
    {
        $this->grid->setDataSource($this->defaultDatasource($this->user))->setRememberState(false);
        $this->grid->setDefaultSort(['myValueIsNull' => 'DESC', 'a.scientificName' => 'ASC']);

        $this->grid->addColumnText('scientificName', 'GBIF scientificName')->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
            $qb->andWhere('LOWER(a.scientificName) LIKE LOWER(:name)')
                ->setParameter('name', $value . '%');
        });
        $this->grid->addColumnText('acceptedScientificName', 'GBIF accepted scientificName')->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
            $qb->andWhere('LOWER(a.acceptedScientificName) LIKE LOWER(:name)')
                ->setParameter('name', $value . '%');
        });

        $this->grid->addColumnText('species', 'GBIF species')
            ->setRenderer(function ($value) {
                return $value?->species . '';
            })
            ->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('LOWER(a.species) LIKE LOWER(:name)')
                    ->setParameter('name', $value . '%');
            });

        $this->grid->addColumnText('taxonRank', 'GBIF rank')
            ->setRenderer(function ($value) {
                return $value?->taxonRank . '';
            })
            ->setFilterSelect(BaseGridFactory::FILTER_NOTHING + $this->service->getRanks())
            ->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('a.taxonRank = :rank')
                    ->setParameter('rank', $value);
            });


        $this->grid->addColumnText('pladiasTaxon', 'Pladias taxon')
            ->setRenderer(function ($value) {
                return $value?->pladiasTaxon?->nameLatin . '';
            })
            ->setFilterText()->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('LOWER(t.nameLatin) LIKE LOWER(:name)')
                    ->setParameter('name', $value . '%');
            });

        $this->grid->addExportCsvFiltered('Csv export (filtered)', 'curator_imported.csv')
            ->setTitle('Csv export (filtered)');

        $this->inlineDelete();
        $this->inlineEdit();
        return $this->grid;
    }

    protected function inlineDelete()
    {
        $this->grid->addAction('remove', '', 'remove!')
            ->setIcon('trash')
            ->setTitle('Remove')
            ->setClass('btn btn-xs btn-danger')
            ->setRenderCondition(function ($value) {
                return !empty($value->pladiasTaxon);
            })
            ->setConfirmation(new StringConfirmation('Remove mapping of Pladias to this GBIF taxon?', 'id'));
    }

    protected function inlineEdit()
    {
        $presenter = $this->presenter;
        $this->grid->addInlineEdit()
            ->onControlAdd[] = function ($container) {

            $container->addText('pladiasTaxon', '')
                ->setHtmlAttribute('class', 'autocomplete-edit')
                ->setHtmlAttribute('data-source', $this->presenter->link(':Front:Autocomplete:taxons-all'));

        };
        $this->grid->getInlineEdit()->onSetDefaults[] = function ($container, $item) {
            $container->setDefaults([
                'pladiasTaxon' => $item?->pladiasTaxon?->nameLatin . ''
            ]);
        };

        $this->grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($presenter) {
            try {
                $this->service->addMapping((int) $id, $values);
                $presenter->makeFlashMessage("Mapování " . $values['pladiasTaxon'] . " vytvořeno.");
            } catch (\Exception $e) {
                $presenter->makeFlashMessage('Nebylo možné přidat mapování.', null, 'danger', $e);
            }
            $presenter->redrawControl('flashes');

        };
    }

    protected function defaultDatasource(User $user): QueryBuilder
    {
        return $this->service->getQueryBuilder()
            ->addSelect('CASE WHEN a.pladiasTaxon IS NULL THEN 1 ELSE 0 END AS HIDDEN myValueIsNull');


    }

}
