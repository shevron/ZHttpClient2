<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Authentication;

use ZHttpClient2\Authentication\Basic;
use ZHttpClient2\Request as HttpRequest;

class BasicTest extends \PHPUnit_Framework_TestCase
{
    public function testAuthorizationHeaderAdded()
    {
        $username = "Shahar";
        $password = "this is !@$:passs";

        $request = new HttpRequest('http://www.example.com');

        $auth = new Basic($username, $password);
        $auth->authenticate($request);

        $this->assertTrue($request->getHeaders()->has('Authorization'));
        $authData = $request->getHeaders()->get('Authorization')->getFieldValue();
        $this->assertEquals("Basic " . base64_encode("{$username}:{$password}"), $authData);
    }
}
