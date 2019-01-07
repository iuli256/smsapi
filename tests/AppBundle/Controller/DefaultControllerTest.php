<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testErrorOnNonPost()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"all request should be made only using POST"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostWithoutHeaderSet()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $crawler = $client->request('POST', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"Content-Type should be application\/json"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostWithEmptyData()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"no data received on POST"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostWithJSON_ERROR_SYNTAX()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"mmm","}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"Input json - Syntax error, malformed JSON"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostWithJSON_ERROR_STATE_MISMATCH()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"mmm": 1 ]}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"Input json - Underflow or the modes mismatch"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostInputIsArray()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '1111');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"Input json - is not an array"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostInputRecipient()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"sss":"SSS"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"recipient is not defined"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostRecipientFormat()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"recipient":"aaa"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"recipient have not the correct format. it should be an international phone number without leading 0. ex: 40723123789"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostInputOriginator()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"recipient":"40723586983"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"originator is not defined"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostOriginatorFormat()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"recipient":"40723586983", "originator":"test"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"originator can be only MessageBird"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostInputMessage()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"recipient":"40723586983", "originator":"MessageBird"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"message is not defined"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostMessageFormat()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"recipient":"40723586983", "originator":"MessageBird", "message":"i9i9i9i9i19i19ud9ud9ud9ud9ud9ud92u92u9ud92u9ud92u289d892u9u8282892d89u2d892d8928u28u2892892d8u289289u289u2d89u2d89u2d89u289282828282828282udhdhhdhdhdhdhdhjdjdjdjdjdjdjjdhdhdhhdhdhdhhdhdhdhhdhdjshsjshshshjshsjshjshjshsjhsjhsjhsjhajhsjahsjhajhsjahshjahjjshajhshjshjshjshuwuiwuwiouhskjhskojhsjihsjoihsojishjshishjio"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"error","data":"","message":"message must not exced 160 characters"}', $client->getResponse()->getContent());
    }

    public function testErrorOnPostMessageCorrect()
    {
        $client = static::createClient(array(), array(
            'HTTP_HOST' => 'MessageBird', // Set HOST HTTP Header.
            'HTTP_USER_AGENT' => 'Symfony Browser/1.0',
        ));
        $header = array('HTTP_ACCEPT' => 'application/json');
        $header['CONTENT_TYPE'] = 'application/json';
        $crawler = $client->request('POST', '/',array(), array(), $header,
            '{"recipient":"40723586983", "originator":"MessageBird", "message":"this is a test message"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('{"status":"success","data":"","message":"message have been sent"}', $client->getResponse()->getContent());
    }
}
