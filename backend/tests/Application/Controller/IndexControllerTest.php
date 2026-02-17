<?php

namespace App\Tests\Application\Controller;

use App\Tests\Application\TestCase;
use PHPUnit\Framework\Attributes\Test;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function createLink_shouldViewNewLink()
    {
        // Arrange
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/web/index');

        $link = 'https://google.com';

        // Act
        $crawler = $client->submitForm('Сократить', [
            'link_form[urlTarget]' => $link
        ]);

        // Assert
        $this->assertResponseIsSuccessful();

        $linkShort = $crawler->filter('#link-short');
        $linkFull = $crawler->filter('#link-full');

        $this->assertMatchesRegularExpression('/Сокращенная ссылка: .+/i', $linkShort->text('Сокращенная ссылка'));
        $this->assertEquals("Полная ссылка: $link", $linkFull->text('Полная ссылка'));
    }

    #[Test]
    public function redirect_existsLink_shouldRedirect()
    {
        // Arrange
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/web/index');
        $link = 'https://google.com';
        $crawler = $client->submitForm('Сократить', [
            'link_form[urlTarget]' => $link
        ]);
        $linkNode = $crawler->filter('#link-short > a');
        $client->followRedirects(false);

        // Act
        $client->clickLink($linkNode->text());

        // Assert
        $this->assertResponseRedirects($link);
    }

    #[Test]
    public function redirect_notExistsLink_shouldShowMessageError()
    {
        // Arrange
        $client = static::createClient();
        $client->followRedirects();
        $code = '00000';
        $expectedMessage = "Не удалось найти ссылку на $code";

        // Act
        $crawler = $client->request('GET', "/web/index/r/$code");

        // Assert
        $this->assertPageTitleSame('Сокращатель ссылок');

        $alert = $crawler->filter('.alert');

        $this->assertEquals($expectedMessage, $alert->text());
    }
}
