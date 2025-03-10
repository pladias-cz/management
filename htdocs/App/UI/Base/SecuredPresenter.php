<?php declare(strict_types = 1);

namespace App\UI\Base;

abstract class SecuredPresenter extends BasePresenter
{


    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect(
                BasePresenter::DESTINATION_LOG_IN,
                ['backlink' => $this->storeRequest()]
            );
        }

        parent::checkRequirements($element);
    }

}
