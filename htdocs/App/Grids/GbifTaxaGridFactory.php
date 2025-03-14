<?php declare(strict_types = 1);

namespace App\Grids;

interface GbifTaxaGridFactory
{

    public function create(): GbifTaxaGrid;

}
