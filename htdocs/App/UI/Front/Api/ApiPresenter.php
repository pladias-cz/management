<?php declare(strict_types=1);

namespace App\UI\Front\Api;

use App\UI\Base\UnsecuredPresenter;
use Doctrine\ORM\EntityManagerInterface;


class ApiPresenter extends UnsecuredPresenter
{
    const MIN_LENGHT = 2;
    /**   @inject */
    public EntityManagerInterface $entityManager;

    public function renderDefault($id)
    {
        $this->emptyResponse();
    }

    private function emptyResponse()
    {
        $this->sendJson(array());
    }
    public function actionTest(int $term)
    {
        $sql = "SELECT t.id FROM public.taxons t
          JOIN gbif.taxa g ON g.pladias_taxon_id = t.id
          WHERE g.taxon_key = :taxonId LIMIT 1";
        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('taxonId', $term);
        $result = $query->executeQuery()->fetchFirstColumn();
        if (!empty($result)) {
            $this->sendJson(true);
        }
        $this->sendJson(false);

    }

}
