<?php

namespace Keboola\WorkspaceProvider\Tests;

use Keboola\InputMapping\Staging\StrategyFactory as InputStrategyFactory;
use Keboola\OutputMapping\Staging\StrategyFactory as OutputStrategyFactory;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\Components;
use Keboola\StorageApi\Workspaces;
use Keboola\StorageApiBranch\ClientWrapper;
use Keboola\WorkspaceProvider\InputProviderInitializer;
use Keboola\WorkspaceProvider\OutputProviderInitializer;
use Keboola\WorkspaceProvider\WorkspaceProviderFactory\ComponentWorkspaceProviderFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class CombinedProviderInitializerTest extends TestCase
{
    /** @var Client */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = new Client([
            'url' => getenv('STORAGE_API_URL'),
            'token' => getenv('STORAGE_API_TOKEN'),
        ]);
    }


    public function testWorkspaceIsInitializedOnlyOnce()
    {
        $clientWrapper = new ClientWrapper(
            $this->client,
            null,
            new NullLogger(),
            ''
        );
        $logger = new NullLogger();

        $componentsApi = new Components($this->client);
        $workspacesApi = new Workspaces($this->client);

        $providerFactory = new ComponentWorkspaceProviderFactory(
            $componentsApi,
            $workspacesApi,
            'my-test-component',
            'my-test-config'
        );

        $inputStagingFactory = new InputStrategyFactory($clientWrapper, $logger, 'json');
        $inputInitializer = new InputProviderInitializer($inputStagingFactory, $providerFactory);
        $inputInitializer->initializeProviders(
            InputStrategyFactory::WORKSPACE_SNOWFLAKE,
            [
                'owner' => ['hasSnowflake' => true],
            ],
            '/tmp/random/data'
        );

        $outputStagingFactory = new OutputStrategyFactory($clientWrapper, $logger, 'json');
        $outputInitializer = new OutputProviderInitializer($outputStagingFactory, $providerFactory);
        $outputInitializer->initializeProviders(
            OutputStrategyFactory::WORKSPACE_SNOWFLAKE,
            [
                'owner' => ['hasSnowflake' => true],
            ],
            '/tmp/random/data'
        );

        // TODO how to test?
        
//        $inputStagingFactory->getTableInputStrategy(OutputStrategyFactory::WORKSPACE_SNOWFLAKE, 'test', new InputTableStateList([]))->downloadTable(new InputTableOptions([]));
//        $inputStagingFactory->getFileInputStrategy(OutputStrategyFactory::WORKSPACE_SNOWFLAKE);
//        $outputStagingFactory->getTableOutputStrategy(OutputStrategyFactory::WORKSPACE_SNOWFLAKE, 'test', new InputTableStateList([]));
//        $outputStagingFactory->getFileOutputStrategy(OutputStrategyFactory::WORKSPACE_SNOWFLAKE);
    }
}
