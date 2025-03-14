<?php declare(strict_types = 1);

namespace App\UI\Admin\Gbif;

use App\Grids\GbifTaxaGrid;
use App\Grids\GbifTaxaGridFactory;
use App\UI\Base\AuthorizedPresenter;

final class GbifPresenter extends AuthorizedPresenter
{
    public static array $allowedUsers=[AuthorizedPresenter::STECH, AuthorizedPresenter::DANIHELKA, AuthorizedPresenter::NOVOTNY];

    /** @inject  */ public GbifTaxaGridFactory $gbifTaxaGridFactory;

    public function renderDefault(): void
    {
        $this->template->title = 'GBIF taxa mapping';
    }

    public function createComponentTaxaGrid(): GbifTaxaGrid
    {
        return $this->gbifTaxaGridFactory->create();
    }

}
