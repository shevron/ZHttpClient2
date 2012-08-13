<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Http
 */

namespace ZHttpClient2Test\Client;
use ZHttpClient2\Client;

/**
 * Zend_Http_Client_Adapter_Proxy test suite.
 *
 * In order to run, TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY must point to a working
 * proxy server, which can access TESTS_ZEND_HTTP_CLIENT_BASEURI.
 *
 * See TestConfiguration.php.dist for more information.
 *
 * @category   Zend
 * @package    Zend_Http_Client
 * @subpackage UnitTests
 * @group      Zend_Http
 * @group      Zend_Http_Client
 */
class ProxyAdapterTest extends SocketTest
{
    /**
     * Configuration array
     *
     * @var array
     */
    protected function setUp()
    {
        if (defined('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY') &&
              TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY) {

            list($host, $port) = explode(':', TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY, 2);

            if (! $host)
                $this->markTestSkipped('No valid proxy host name or address specified.');

            $port = (int) $port;
            if ($port == 0) {
                $port = 8080;
            } else {
                if (($port < 1 || $port > 65535))
                    $this->markTestSkipped("$port is not a valid proxy port number. Should be between 1 and 65535.");
            }

            $user = '';
            $pass = '';
            if (defined('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_USER') &&
                TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_USER)
                    $user = TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_USER;

            if (defined('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_PASS') &&
                TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_PASS)
                    $pass = TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_PASS;

            $this->config = array(
                'adapter'    => '\ZHttpClient2\Client\Adapter\Proxy',
                'proxy_host' => $host,
                'proxy_port' => $port,
                'proxy_user' => $user,
                'proxy_pass' => $pass,
            );

            parent::setUp();

        } else {
            $this->markTestSkipped('ZHttpClient2\Client proxy server tests are not enabled in TestConfiguration.php');
        }
    }

    /**
     * Test that when no proxy is set the adapter falls back to direct connection
     */
    public function testFallbackToSocket()
    {
        $this->_adapter->setOptions(array(
            'proxy_host' => null,
        ));

        $this->client->setUri($this->baseuri . 'testGetLastRequest.php');
        $res = $this->client->request(Client::TRACE);
        if ($res->getStatus() == 405 || $res->getStatus() == 501) {
            $this->markTestSkipped('Server does not allow the TRACE method');
        }

        $this->assertEquals($this->client->getLastRawRequest(), $res->getBody(), 'Response body should be exactly like the last request');
    }

    public function testGetLastRequest()
    {
        /**
         * This test will never work for the proxy adapter (and shouldn't!)
         * because the proxy server modifies the request which is sent back in
         * the TRACE response
         */
    }
}
