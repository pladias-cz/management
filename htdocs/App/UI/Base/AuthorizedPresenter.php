<?php declare(strict_types=1);

namespace App\UI\Base;

abstract class AuthorizedPresenter extends SecuredPresenter
{

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        if (!in_array($this->user->getId(), $this->allowedUsers)) {
            $this->flashMessage('Nemáte oprávnění k přístupu.', 'danger');
            $this->redirect('Home:default');
        }

        parent::checkRequirements($element);
    }


}
