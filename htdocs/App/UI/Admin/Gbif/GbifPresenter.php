<?php declare(strict_types = 1);

namespace App\UI\Admin\Gbif;

use App\Services\EntityServices\GbifTaxaService;
use App\UI\Base\AuthorizedPresenter;

final class GbifPresenter extends AuthorizedPresenter
{
    protected array $allowedUsers=[AuthorizedPresenter::STECH, AuthorizedPresenter::DANIHELKA, AuthorizedPresenter::NOVOTNY];

    /** @inject  */ public GbifTaxaService $gbifTaxaService;

    public function renderDefault(): void
    {
        $this->template->title = 'GBIF taxa mapping';
        $this->template->taxa = $this->gbifTaxaService->findAll();
    }

}
