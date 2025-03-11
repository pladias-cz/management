<?php declare(strict_types = 1);

namespace App\UI\Admin\Gbif;

use App\UI\Base\AuthorizedPresenter;

final class GbifPresenter extends AuthorizedPresenter
{
    protected array $allowedUsers=[31];

    public function renderDefault(): void
    {
        $this->template->title = 'GBIF taxa mapping';
    }

}
