<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Logowanie');
    }

    public function testPublishedArticlesPageIsPubliclyAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/articles/published');

        self::assertResponseIsSuccessful();
    }

    public function testProtectedDashboardRedirectsAnonymousUserToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseRedirects('/login');
    }

    public function testHealthEndpointReportsStatus(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('status', $data);
    }

    public function testMetricsEndpointIsPrometheusCompatible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/metrics');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('articles_total', $client->getResponse()->getContent());
    }
}
