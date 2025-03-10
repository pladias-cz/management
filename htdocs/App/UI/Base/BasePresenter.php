<?php declare(strict_types = 1);

namespace App\UI\Base;

use App\Services\AppConfiguration;
use App\UI\Base\Form\FormFactory;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

    public const string DESTINATION_AFTER_SIGN_IN = ':Admin:Home:';
    public const string DESTINATION_AFTER_SIGN_OUT = ':Front:Home:';
    public const string DESTINATION_LOG_IN = ':Front:Sign:in';


}
