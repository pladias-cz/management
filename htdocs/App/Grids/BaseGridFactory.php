<?php declare(strict_types = 1);

namespace App\Grids;

use Contributte\Datagrid\Datagrid;

class BaseGridFactory
{

    public const array FILTER_NOTHING = ['' => ' - - - - - '];

    protected DataGrid $grid;

    public function createBaseDatagrid(): DataGrid
    {
        $this->grid = new DataGrid();
        $this->grid
            ->setItemsPerPageList([10, 50, 200])
            ->setStrictStorageFilterValues(false);

//        DataGrid::$iconPrefix = 'bi bi-';
        return $this->grid;
    }

}
