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
    public const string SEPARATOR = "\t";

    public function __construct(private EntityManagerInterface $entityManager, private TempDir $tempDir)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $this->tempDir->getPath('records.csv');

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $output->writeln('<error>Cannot open file: ' . $filePath . '</error>');
            return Command::FAILURE;
        }

        $headers = fgetcsv($handle, null, self::SEPARATOR, '"', '\\');
        if ($headers === false) {
            fclose($handle);
            $output->writeln('<error>Empty or invalid CSV file.</error>');
            return Command::FAILURE;
        }

        $batchSize = 10000;
        $count = 0;
        $importStartTime = date('Y-m-d H:i:s');

        $output->writeln('<info>Starting import...</info>');
//var_dump($headers);
        while (($row = fgetcsv($handle, null, self::SEPARATOR,chr(1), '\\')) !== false) {

            $data = array_combine($headers, $row);
            $lon = (float)$data['decimalLongitude'];
            ($lon != '') ? $coords = sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", (float)$data['decimalLongitude'], (float)$data['decimalLatitude']) : $coords = null;

                $sql = '
                    INSERT INTO gbif.records (
                        gbif_id,
                        taxon_key,
                        locality,
                        recorded_by,
                        institution_code,
                        collection_code,
                        coords,
                        coords_precision,
                        day,
                        month,
                        year,
                        imported_at,
                        taxon_rank,
                        verbatim_scientific_name,
                        dataset_key
                    ) VALUES (
                        :gbif_id,
                        :taxon_key,
                        :locality,
                        :recorded_by,
                        :institution_code,
                        :collection_code,
                        :coords,
                        :coords_precision,
                        :day,
                        :month,
                        :year,
                        :imported_at,
                        :taxon_rank,
                        :verbatim_scientific_name,
                        :dataset_key
                    )
                ';

                $params = [
                    'gbif_id' => (int) $data['gbifID'],
                    'taxon_key' => (int) $data['taxonKey'],
                    'locality' => ($data['locality'] != '') ? $data['locality'] : null,
                    'recorded_by' => $data['recordedBy'],
                    'institution_code' => $data['institutionCode'],
                    'collection_code' => $data['collectionCode'],
                    'coords' => $coords,
                    'coords_precision' => ($data['coordinateUncertaintyInMeters'] != '') ? $data['coordinateUncertaintyInMeters'] : null,
                    'day' => ($data['day'] != '') ? $data['day'] : null,
                    'month' => ($data['month'] != '') ? $data['month'] : null,
                    'year' => ($data['year'] != '') ? $data['year'] : null,
                    'imported_at' => $importStartTime,
                    'taxon_rank' => $data['taxonRank'],
                    'verbatim_scientific_name' => $data['verbatimScientificName'],
                    'dataset_key' => $data['datasetKey'],
                ];

                try {
                    $this->entityManager
                        ->getConnection()
                        ->executeStatement($sql, $params);
            } catch (\Exception $exception) {
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
