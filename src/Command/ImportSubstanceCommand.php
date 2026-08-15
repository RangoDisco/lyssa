<?php

namespace App\Command;

use App\DTO\Import\CisCompoDTO;
use App\Service\ImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[AsCommand(
    name: 'app:import:substance',
    description: 'Import substances and link them to their medicines.',
)]
class ImportSubstanceCommand extends Command
{
    public function __construct(
        private readonly SerializerInterface                    $serializer,
        private readonly ImportService                          $importer,
        #[Autowire('%app.import_dir%')] private readonly string $importDir,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = sprintf("%s/%s", $this->importDir, 'CIS_COMPO.csv');

        $csv = file_get_contents($filePath);
        $io->info("Imported csv");

        $data = $this->serializer->deserialize($csv, CisCompoDTO::class . '[]', 'csv');
        $io->info(sprintf('Deserialized csv to %s', CisCompoDTO::class));

        $io->progressStart(count($data));

        foreach ($data as $row) {
            try {
                $this->importer->importSubstance($row);
            } catch (Throwable $e) {
                $io->error(sprintf("An error occurred when importing: %s, err: %s", $row->name, $e->getMessage()));
                return Command::FAILURE;
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(sprintf('%s %d %s.', 'Successfully imported', count($data), 'substances'));
        return Command::SUCCESS;
    }
}
