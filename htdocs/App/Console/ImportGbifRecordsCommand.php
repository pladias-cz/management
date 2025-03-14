<?php

namespace App\Console;

use App\Services\TempDir;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'gbif:importRecords', description: 'Import data from CSV file into database')]
class ImportGbifRecordsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager, private TempDir $tempDir)
    {
        parent::__construct();
    }

    /**
     * ALTER TABLE IF EXISTS gbif.records DROP CONSTRAINT IF EXISTS gbif_records_taxon_fkey;
     *
     * DELETE FROM gbif.records r WHERE NOT EXISTS (SELECT 1 FROM gbif.taxa t WHERE t.taxon_key = r.taxon_key)
     *
     * ALTER TABLE IF EXISTS gbif.records
     * ADD CONSTRAINT gbif_records_taxon_fkey FOREIGN KEY (taxon_key)
     * REFERENCES gbif.taxa (taxon_key) MATCH SIMPLE
     * ON UPDATE NO ACTION
     * ON DELETE CASCADE;
     */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $this->tempDir->getPath('records.csv');

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $output->writeln('<error>Cannot open file: ' . $filePath . '</error>');
            return Command::FAILURE;
        }

        $headers = fgetcsv($handle, null, ';', '"', '\\');
        if ($headers === false) {
            fclose($handle);
            $output->writeln('<error>Empty or invalid CSV file.</error>');
            return Command::FAILURE;
        }

        $batchSize = 100;
        $count = 0;
        $importStartTime = date('Y-m-d H:i:s');

        $output->writeln('<info>Starting import...</info>');
//var_dump($headers);
        while (($row = fgetcsv($handle, null, ';', '"', '\\')) !== false) {
            $data = array_combine($headers, $row);
            foreach ($data as &$value) {
                $value = str_replace("'", "''", $value); // Escape single quotes for PostgreSQL
            }
            $lon = (float)$data['decimalLongitude'];
            ($lon != '') ? $coords = sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", (float)$data['decimalLongitude'], (float)$data['decimalLatitude']) : $coords = null;

            $day = ($data['day'] != '') ? $data['day'] : null;
            $month = ($data['month'] != '') ? $data['month'] : null;
            $year = ($data['year'] != '') ? $data['year'] : null;
            $precision = ($data['coordinateUncertaintyInMeters'] != '') ? $data['coordinateUncertaintyInMeters'] : null;
            $locality = ($data['locality'] != '') ? $data['locality'] : null;

            $sql = sprintf(
                "INSERT INTO gbif.records (gbif_id, taxon_key, locality, recorded_by,institution_code ,collection_code,coords,coords_precision, day, month, year, imported_at) VALUES (%d, %d, '%s','%s', '%s', '%s',%s, %d, %d,%d, %d, '%s');", //
                $data['gbifID'],
                $data['taxonKey'],
                $locality,
                $data['recordedBy'],
                $data['institutionCode'],
                $data['collectionCode'],
                $coords,
                $precision,
                $day,
                $month,
                $year,
                $importStartTime
            );

            try {

                $this->entityManager->getConnection()->executeStatement($sql);
            }catch (\Exception $exception){
            $output->writeln($sql . " \n");
                $output->writeln($exception->getMessage() . " \n");
                return Command::FAILURE;
            }
            $count++;

            if ($count % $batchSize === 0) {
                $output->writeln($count . " \n");
            }
        }

        fclose($handle);
        $output->writeln('<info>Import finished. Total rows: ' . $count . '</info>');
        return Command::SUCCESS;
    }
}
