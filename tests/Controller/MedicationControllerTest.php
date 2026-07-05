<?php

namespace App\Tests\Controller;

use App\Entity\Medication;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MedicationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Medication> */
    private EntityRepository $medicationRepository;
    private string $path = '/medication/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->medicationRepository = $this->manager->getRepository(Medication::class);

        foreach ($this->medicationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Medication index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'medication[name]' => 'Testing',
            'medication[source]' => 'Testing',
            'medication[deletedAt]' => 'Testing',
            'medication[createdAt]' => 'Testing',
            'medication[updatedAt]' => 'Testing',
            'medication[owner]' => 'Testing',
        ]);

        self::assertResponseRedirects('/medication');

        self::assertSame(1, $this->medicationRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Medication();
        $fixture->setName('My Title');
        $fixture->setSource('My Title');
        $fixture->setDeletedAt('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUpdatedAt('My Title');
        $fixture->setOwner('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Medication');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Medication();
        $fixture->setName('Value');
        $fixture->setSource('Value');
        $fixture->setDeletedAt('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setOwner('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'medication[name]' => 'Something New',
            'medication[source]' => 'Something New',
            'medication[deletedAt]' => 'Something New',
            'medication[createdAt]' => 'Something New',
            'medication[updatedAt]' => 'Something New',
            'medication[owner]' => 'Something New',
        ]);

        self::assertResponseRedirects('/medication');

        $fixture = $this->medicationRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getSource());
        self::assertSame('Something New', $fixture[0]->getDeletedAt());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getUpdatedAt());
        self::assertSame('Something New', $fixture[0]->getOwner());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Medication();
        $fixture->setName('Value');
        $fixture->setSource('Value');
        $fixture->setDeletedAt('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setOwner('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/medication');
        self::assertSame(0, $this->medicationRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
