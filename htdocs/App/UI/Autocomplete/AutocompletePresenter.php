<?php declare(strict_types=1);

namespace App\UI\Autocomplete;

use App\UI\Base\UnsecuredPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Nette;
use Pladias\ORM\Entity\Public\Taxons;


class AutocompletePresenter extends UnsecuredPresenter
{
    const MIN_LENGHT = 2;
    /**   @inject */ public EntityManagerInterface $entityManager;

    public function renderDefault($id)
    {
        $this->emptyResponse();
    }

    private function emptyResponse()
    {
        $this->sendJson(array());
    }

    public function renderTaxonsAll($term)
    {
        if (strlen($term) < self::MIN_LENGHT) {
            $this->sendJson(array($term));
        }

        $this->sendJson($this->entityManager->getRepository(Taxons::class)->findAutocompleteInValidNames($term));

    }
}
