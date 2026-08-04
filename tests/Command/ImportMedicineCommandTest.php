<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImportMedicineCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $result = static::runCommand('app:import:medicine', [
            'file-path' => sprintf('%s/%s', __DIR__, '../Fixtures/import/TEST_CIS_bdpm.csv')
        ]);

        $this->assertCommandIsSuccessful($result);

        $output = $result->getOutput();
        $this->assertStringContainsString('Successfully imported 5 medicines.', $output);
    }
}
