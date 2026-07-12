<?php

namespace App\Tests\Controller;

use App\Entity\Variant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class VariantControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Variant> */
    private EntityRepository $variantRepository;
    private string $path = '/variant/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->variantRepository = $this->manager->getRepository(Variant::class);

        foreach ($this->variantRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Variant index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'variant[dosage]' => 'Testing',
            'variant[format]' => 'Testing',
            'variant[deletedAt]' => 'Testing',
            'variant[createdAt]' => 'Testing',
            'variant[updatedAt]' => 'Testing',
            'variant[medication]' => 'Testing',
        ]);

        self::assertResponseRedirects('/variant');

        self::assertSame(1, $this->variantRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Variant();
        $fixture->setDosage('My Title');
        $fixture->setFormat('My Title');
        $fixture->setDeletedAt('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUpdatedAt('My Title');
        $fixture->setMedication('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Variant');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Variant();
        $fixture->setDosage('Value');
        $fixture->setFormat('Value');
        $fixture->setDeletedAt('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setMedication('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'variant[dosage]' => 'Something New',
            'variant[format]' => 'Something New',
            'variant[deletedAt]' => 'Something New',
            'variant[createdAt]' => 'Something New',
            'variant[updatedAt]' => 'Something New',
            'variant[medication]' => 'Something New',
        ]);

        self::assertResponseRedirects('/variant');

        $fixture = $this->variantRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDosage());
        self::assertSame('Something New', $fixture[0]->getFormat());
        self::assertSame('Something New', $fixture[0]->getDeletedAt());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getUpdatedAt());
        self::assertSame('Something New', $fixture[0]->getMedication());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Variant();
        $fixture->setDosage('Value');
        $fixture->setFormat('Value');
        $fixture->setDeletedAt('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setMedication('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/variant');
        self::assertSame(0, $this->variantRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
