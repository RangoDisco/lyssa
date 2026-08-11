<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\InvalidOptionException;

class ImportMedicineCommandTest extends KernelTestCase
{
    public function testImportMedicine(): void
    {
        $result = static::runCommand('app:import:medicine');

        $this->assertCommandIsSuccessful($result);

        $output = $result->getOutput();
        $this->assertStringContainsString('Successfully imported 5 medicines.', $output);
    }

    public function testImportGenerics(): void
    {
        $result = static::runCommand('app:import:medicine', ['--content' => 'generic']);

        $this->assertCommandIsSuccessful($result);

        $output = $result->getOutput();
        $this->assertStringContainsString('Successfully imported 5 generics.', $output);
    }

    public function testImportWrongContent(): void
    {
        $this->expectException(InvalidOptionException::class);
        static::runCommand('app:import:medicine', ['--content' => 'wrong']);
    }
}
