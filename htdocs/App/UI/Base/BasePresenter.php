<?php declare(strict_types=1);

namespace App\UI\Base;

use Nette\Application\UI\Presenter;
use Tracy\Debugger;

abstract class BasePresenter extends Presenter
{

    public const string DESTINATION_AFTER_SIGN_IN = ':Admin:Home:';
    public const string DESTINATION_AFTER_SIGN_OUT = ':Front:Home:';
    public const string DESTINATION_LOG_IN = ':Front:Home:';

    public function makeFlashMessage($format, $value = '', $type = "success", ?\Exception $e = null)
    {
        $text = sprintf($format, '"' . $value . '"');

        switch ($type) {
            case 'danger':
                $text .= $this->formatExceptionMessage($e);
                $this->flashMessage($text, $type);
                return true;
            default:
                $this->flashMessage($text, $type);
                return true;
        }

    }

    private function formatExceptionMessage(?\Exception $exception = null): string
    {
        if ($exception instanceof \Exception) {
            Debugger::log($exception);
            return " "
                . 'error'
                . "["
                . $exception->getMessage()
                . "]";
        }
        return "";
    }
}
