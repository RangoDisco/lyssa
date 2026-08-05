<?php

namespace App\Command;

use App\DTO\Import\CisBdpmDTO;
use App\DTO\Import\CisGenerDTO;
use App\Enum\MedicineImportContent;
use App\Service\ImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[AsCommand(
    name: 'app:import:medicine',
    description: 'Import Medicine/Generics from the French medicine database.',
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

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(
        InputInterface        $input,
        OutputInterface       $output,
        #[Option(description: 'Whether we import medicine or generics', suggestedValues: ['medicine', 'generic'])]
        MedicineImportContent $content = MedicineImportContent::Medicine,
        #[Option(description: 'Whether the data is ultimately persisted or not.')]
        bool                  $persist = false,
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        // Ask for user confirmation before real runs
        if ($persist === false) {
            $io->note('This command is running as a dry-run.');
        } else {
            $shouldContinue = $io->confirm('This command will persist data, continue ?', false);
            if (!$shouldContinue) {
                $io->error('Command aborted');
                return Command::FAILURE;
            }
        }

        if ($content === 'medicine') {
            $filePath = sprintf('%s/%s', $this->importDir, 'CIS_BDPM.csv');
            $type = CisBdpmDTO::class;
        } else {
            $filePath = sprintf('%s/%s', $this->importDir, 'CIS_GENER.csv');
            $type = CisGenerDTO::class;
        }

        $csv = file_get_contents($filePath);
        $io->info("Imported $content csv");

        $data = $this->serializer->deserialize($csv, $type . '[]', 'csv');
        $io->info(sprintf('Deserialized csv to %s', $type));

        $io->progressStart(count($data));

        foreach ($data as $bdpm) {
            try {
                $this->importer->importMedicine($bdpm, $persist);
            } catch (Throwable $e) {
                $io->error(sprintf("An error occurred when importing: %s, err: %s", $bdpm->name, $e->getMessage()));
                return Command::FAILURE;
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(sprintf('%s %d %ss.', 'Successfully imported', count($data), $content));
        return Command::SUCCESS;
    }
}
