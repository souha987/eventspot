<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EvenementApiTest extends WebTestCase
{
    public function testGetEvenementsReturnsJsonLd(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/evenements', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/ld+json; charset=utf-8');
    }

    public function testGetEvenementsStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/evenements', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('member', $data);
    }

    public function testGetEvenementNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/evenements/99999', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }
}