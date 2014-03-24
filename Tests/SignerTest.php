<?php
namespace Arthens\RequestSigner\Tests;


use Arthens\RequestSigner\Signer;

class SignerTest extends \PHPUnit_Framework_TestCase
{
    public function testSecretKeyCannotBeNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        $signer = new Signer(null);
    }

    public function testSecretKeyCannotBeEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $signer = new Signer('');
    }

    public function testSign()
    {
        $signer = new Signer('secret-key');
        $sign = $signer->sign('GET', '/users', null, null, array(
            'X-Header' => 'test',
        ));

        $this->assertNotNull($sign);
        $this->assertNotEmpty($sign);
    }

    public function testSignAndVerify()
    {
        $signer = new Signer('secret-key');

        // Let's check a method+url only first
        $sign = $signer->sign('GET', '/users');
        $this->assertTrue($signer->verify($sign, 'GET', '/users'));

        // Let's check a more complex request
        $sign = $signer->sign('GET', '/users', null, null, array(
            'X-Header' => 'test',
        ));
        $this->assertTrue($signer->verify($sign, 'GET', '/users', null, null, array(
            'X-Header' => 'test',
        )));

        // How about a post
        $sign = $signer->sign('POST', '/message', 'Hello there', 'text/plain', array(
            'X-Header' => 'test',
        ));
        $this->assertTrue($signer->verify($sign, 'POST', '/message', 'Hello there', 'text/plain', array(
            'X-Header' => 'test',
        )));
    }

    public function testOrderOfHeadersIsIrrelevant()
    {
        $signer = new Signer('secret-key');
        $sign = $signer->sign('GET', '/users', null, null, array(
            'X-Header-1' => 'some value',
            'X-Header-2' => 'and some other value',
            'X-Header-3' => '', // empty
        ));

        $this->assertTrue($signer->verify($sign, 'GET', '/users', null, null, array(
            'X-Header-3' => '', // empty
            'X-Header-2' => 'and some other value',
            'X-Header-1' => 'some value',
        )));
    }

    public function testDifferentCombinations()
    {
        $signer = new Signer('secret-key');

        // Different method
        $this->assertNotEquals(
            $signer->sign('GET', '/'),
            $signer->sign('POST', '/')
        );

        // Different url
        $this->assertNotEquals(
            $signer->sign('GET', '/'),
            $signer->sign('GET', '/news')
        );

        // Different content
        $this->assertNotEquals(
            $signer->sign('POST', '/news', 'This is new'),
            $signer->sign('POST', '/news', 'Not as new')
        );

        // Different content Type
        $this->assertNotEquals(
            $signer->sign('POST', '/news', 'This is new', 'plain/text'),
            $signer->sign('POST', '/news', 'This is new', 'unknown')
        );

        // Different headers
        $this->assertNotEquals(
            $signer->sign('GET', '/news', null, null, array(
                'X-Header-1' => 'hello there',
            )),
            $signer->sign('GET', '/news', null, null, array(
                'X-Header-1' => 'hello',
            ))
        );
    }

    /**
     * This test makes sure that the generated signature doesn't change with new versions
     */
    public function testRegression()
    {
        $signerA = new Signer('yPEC7s4k1nvFyd1d95jI');
        $this->assertEquals('YTJkYjYyMWU0ZmUxNmJkNWJjZTNlNDY5Nzg0ZTAxNGMyNWM3NTI4Mw', $signerA->sign('GET', '/news'));

        $signerB = new Signer('yxgVCBH8OdThT00OA85t');
        $this->assertEquals('Mzk3MzhjYTFlM2ZjMDAwNDFjZTRjZjQ5YzRkYWRhZTBjNThlYjYwYQ', $signerB->sign('POST', '/message', 'hello'));

        $signerC = new Signer('Ndi31wJIu0zRr24ZvWBB');
        $this->assertEquals('MjE0Yzc1YzJlNjAxMmQyYTE4NjcyOWEwYTA0MzE2OWVmY2Q2NGY4ZQ', $signerC->sign('GET', '/news/archive/2014/01/01'));
    }
}
