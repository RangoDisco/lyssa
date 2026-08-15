<?php

namespace App\Tests\Controller;

use App\Entity\Invitation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InvitationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Invitation> */
    private EntityRepository $invitationRepository;
    private string $path = '/invitation/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->invitationRepository = $this->manager->getRepository(Invitation::class);

        foreach ($this->invitationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Invitation index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'invitation[email]' => 'Testing',
            'invitation[token]' => 'Testing',
            'invitation[accessLevel]' => 'Testing',
            'invitation[patient]' => 'Testing',
        ]);

        self::assertResponseRedirects('/invitation');

        self::assertSame(1, $this->invitationRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Invitation();
        $fixture->setEmail('My Title');
        $fixture->setToken('My Title');
        $fixture->setAccessLevel('My Title');
        $fixture->setPatient('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Invitation');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Invitation();
        $fixture->setEmail('Value');
        $fixture->setToken('Value');
        $fixture->setAccessLevel('Value');
        $fixture->setPatient('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'invitation[email]' => 'Something New',
            'invitation[token]' => 'Something New',
            'invitation[accessLevel]' => 'Something New',
            'invitation[patient]' => 'Something New',
        ]);

        self::assertResponseRedirects('/invitation');

        $fixture = $this->invitationRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getToken());
        self::assertSame('Something New', $fixture[0]->getAccessLevel());
        self::assertSame('Something New', $fixture[0]->getPatient());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Invitation();
        $fixture->setEmail('Value');
        $fixture->setToken('Value');
        $fixture->setAccessLevel('Value');
        $fixture->setPatient('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/invitation');
        self::assertSame(0, $this->invitationRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
