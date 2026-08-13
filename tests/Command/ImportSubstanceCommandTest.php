<?php

namespace App\Tests\Command;

use App\Entity\Lab;
use App\Entity\Medicine;
use App\Enum\MedicineFormat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImportSubstanceCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        // Add needed medicine
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $lab = new Lab()
            ->setName('Lab 1');
        $em->persist($lab);

        foreach ([60002283, 60002746, 60002746, 60003620, 60004277] as $cis) {
            $medicine = new Medicine()
                ->setCis($cis)
                ->setName('MEDICAMENT TEST ' . $cis)
                ->setFormat(MedicineFormat::Unknown)
                ->setLab($lab);
            $em->persist($medicine);
        }

        $em->flush();
        $em->clear();


        $result = static::runCommand('app:import:substance');

        $this->assertCommandIsSuccessful($result);

        $output = $result->getOutput();
        $this->assertStringContainsString('Successfully imported 5 substances.', $output);
    }
}
