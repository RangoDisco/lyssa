<?php

namespace App\Command;

use App\DTO\Import\CISBDPMDTO;
use App\Service\ImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[AsCommand(
    name: 'app:import:medicine',
    description: 'Import/Upsert Medicine and their lab from a file from the French medicine database.',
)]
class ImportMedicineCommand extends Command
{
    public function __construct(
        private readonly SerializerInterface                    $serializer,
        private readonly ImportService                          $importer,
        #[Autowire('%app.import_dir%')] private readonly string $importDir,

    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('persist', null, InputOption::VALUE_NONE, 'Whether the data is ultimately persisted or not.')
            ->addArgument('file-path', InputArgument::OPTIONAL, 'Path to the import file', default: sprintf('%s/%s', $this->importDir, 'CIS_bdpm.csv'));
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $shouldPersist = $input->getOption('persist');


        if ($shouldPersist === false) {
            $io->note('This command is running as a dry-run.');
        } else {
            $shouldContinue = $io->confirm('This command will persist data, continue ?', false);
            if (!$shouldContinue) {
                $io->error('Command aborted');
                return Command::FAILURE;
            }
        }

        $filePath = $input->getArgument('file-path');

        $csv = file_get_contents($filePath);
        $io->info('Imported csv');

        $data = $this->serializer->deserialize($csv, CISBDPMDTO::class . '[]', 'csv');
        $io->info(sprintf('Deserialized csv to %s', CISBDPMDTO::class));

        $io->progressStart(count($data));

        foreach ($data as $bdpm) {
            try {
                $this->importer->getMedicineFromBdpm($bdpm, $shouldPersist);
            } catch (Throwable $e) {
                $io->error(sprintf("An error occurred when importing: %s, err: %s", $bdpm->Name, $e->getMessage()));
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(sprintf('%s %d %s', 'Successfully imported', count($data), 'medicines.'));
        return Command::SUCCESS;
    }
}
