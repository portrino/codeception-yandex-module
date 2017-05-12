<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\API;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Codeception\TestInterface;

/**
 * Class Yandix
 * @package Codeception\Module
 */
class Yandix extends Module implements DependsOnModule, API, ConflictsWithModule
{

    protected $config = [
        'url' => ''
    ];

    protected $dependencyMessage = <<<EOF
Example configuring PhpBrowser as backend for Yandix module.
--
modules:
    enabled:
        - Yandix:
            depends: PhpBrowser
            url: http://localhost/api/
--
Framework modules can be used for testing of API as well.
EOF;

    /**
     * @var \Symfony\Component\HttpKernel\Client|\Symfony\Component\BrowserKit\Client
     */
    public $client = null;
    public $isFunctional = false;

    /**
     * @var InnerBrowser
     */
    protected $connectionModule;

    public $params = [];
    public $response = "";

    public function _before(TestInterface $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->resetVariables();
    }

    protected function resetVariables()
    {
        $this->params = [];
        $this->response = "";
        $this->connectionModule->headers = [];
    }

    public function _conflicts()
    {
        return 'Codeception\Lib\Interfaces\API';
    }

    public function _depends()
    {
        return ['Codeception\Lib\InnerBrowser' => $this->dependencyMessage];
    }

    public function _inject(InnerBrowser $connection)
    {
        $this->connectionModule = $connection;
        if ($this->connectionModule instanceof Framework) {
            $this->isFunctional = true;
        }
        if ($this->connectionModule instanceof PhpBrowser) {
            if (!$this->connectionModule->_getConfig('url')) {
                $this->connectionModule->_setConfig(['url' => $this->config['url']]);
            }
        }
    }

    protected function getRunningClient()
    {
        if ($this->client->getInternalRequest() === null) {
            throw new ModuleException($this, "Response is empty. Use `\$I->sendXXX()` methods to send HTTP request");
        }
        return $this->client;
    }

    /**
     * @return bool
     */
    public function seeResponseContainsJsonLdMarkup()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        \PHPUnit_Framework_Assert::assertNotEmpty($responseContent, 'response does not contain json+ld empty');
    }

}