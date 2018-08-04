<?php

/*
 * This file is part of the magerun2-list-api-endpoints package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\Magento\ListApiEndpoints\Command;

use InvalidArgumentException;
use N98\Magento\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Unit tests for {@link \BitExpert\Magento\ListApiEndpoints\Command\ListApiEndpoints}.
 */
class ListApiEndpointsUnitTest extends TestCase
{
    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var Application
     */
    private $application;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $outputFormatter = $this->createMock(OutputFormatterInterface::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->output->expects($this->any())
            ->method('getFormatter')
            ->willReturn($outputFormatter);
        $this->application = new Application();
        $this->application->init([], $this->input, $this->output);
    }

    /**
     * @test
     */
    public function missingOutputFormatParameterThrowsException()
    {
        self::expectException(InvalidArgumentException::class);

        /** @var ListApiEndpoints $command */
        $command = $this->getApiEndpointsMock();
        $command->method('getDefinedServices')
            ->willReturn([]);
        $command->setApplication($this->application);
        $command->run($this->input, $this->output);
    }

    /**
     * @test
     */
    public function withOutputFormatParameterSetTheCommandWillRenderTableStructure()
    {
        // since no services are returned, just the table header is rendered
        $this->output->expects($this->exactly($this->countTableRowsToPrint()))
            ->method('writeln');

        $this->input->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap([
                [ListApiEndpoints::OPTION_FILTER_METHOD, ''],
                [ListApiEndpoints::OPTION_OUTPUT_FORMAT, 'table'],
            ]));

        /** @var ListApiEndpoints $command */
        $command = $this->getApiEndpointsMock();
        $command->method('getDefinedServices')
            ->willReturn([]);
        $command->setApplication($this->application);
        $command->run($this->input, $this->output);
    }

    /**
     * @test
     */
    public function forEachDefinedRouteTheCommandWillRenderTableRow()
    {
        $services['routes'] = [
            '/route' => [
                'GET' => ['resources' => '{}']
            ]
        ];

        $this->output->expects($this->exactly($this->countTableRowsToPrint($services)))
            ->method('writeln');

        $this->input->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap([
                [ListApiEndpoints::OPTION_FILTER_METHOD, ''],
                [ListApiEndpoints::OPTION_OUTPUT_FORMAT, 'table'],
            ]));

        /** @var ListApiEndpoints $command */
        $command = $this->getApiEndpointsMock();
        $command->method('getDefinedServices')
            ->willReturn($services);
        $command->setApplication($this->application);
        $command->run($this->input, $this->output);
    }

    /**
     * @test
     */
    public function forEachFilteredRouteTheCommandWillRenderTableRow()
    {
        $filter = 'GET';
        $services['routes'] = [
            '/route' => [
                'GET' => ['resources' => '{}'],
                'PUT' => ['resources' => '{}']
            ],
            '/other-route' => [
                'GET' => ['resources' => '{}'],
                'POST' => ['resources' => '{}'],
                'DELETE' => ['resources' => '{}']
            ],
        ];

        $this->output->expects($this->exactly($this->countTableRowsToPrint($services, $filter)))
            ->method('writeln');

        $this->input->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap([
                [ListApiEndpoints::OPTION_FILTER_METHOD, $filter],
                [ListApiEndpoints::OPTION_OUTPUT_FORMAT, 'table'],
            ]));

        /** @var ListApiEndpoints $command */
        $command = $this->getApiEndpointsMock();
        $command->method('getDefinedServices')
            ->willReturn($services);
        $command->setApplication($this->application);
        $command->run($this->input, $this->output);
    }

    /**
     * @test
     */
    public function forMultipleFilteredRoutesTheCommandWillRenderTableRow()
    {
        $filter = 'GET, POST';
        $services['routes'] = [
            '/route' => [
                'GET' => ['resources' => '{}'],
                'PUT' => ['resources' => '{}']
            ],
            '/other-route' => [
                'GET' => ['resources' => '{}'],
                'POST' => ['resources' => '{}'],
                'DELETE' => ['resources' => '{}']
            ],
        ];

        $this->output->expects($this->exactly($this->countTableRowsToPrint($services, $filter)))
            ->method('writeln');

        $this->input->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap([
                [ListApiEndpoints::OPTION_FILTER_METHOD, $filter],
                [ListApiEndpoints::OPTION_OUTPUT_FORMAT, 'table'],
            ]));

        /** @var ListApiEndpoints $command */
        $command = $this->getApiEndpointsMock();
        $command->method('getDefinedServices')
            ->willReturn($services);
        $command->setApplication($this->application);
        $command->run($this->input, $this->output);
    }

    /**
     * Helper method to configure a mocked version of {@link \BitExpert\Magento\LisaApiEndpoints\Command\ListApiEndpoints}.
     *
     * @return ListApiEndpoints|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getApiEndpointsMock()
    {
        $command = $this->getMockBuilder(ListApiEndpoints::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods(['getDefinedServices', 'detectMagento', 'initMagento'])
            ->getMock();
        $command->method('detectMagento')
            ->willReturn(null);
        $command->method('initMagento')
            ->willReturn(true);
        return $command;
    }

    /**
     * Helper method to count the routes in given $services array.
     *
     * @param array $services
     * @param string $filter
     * @return int
     */
    protected function countRoutes(array $services, $filter = '')
    {
        $routesCounter = 0;
        $methodsToFilterArray = explode(',', strtoupper($filter));

        if(isset($services['routes']) && is_array($services['routes'])) {
            foreach ($services['routes'] as $route => $methods) {
                foreach ($methods as $method => $config) {
                    if(empty($filter) || in_array($method, $methodsToFilterArray)) {
                        $routesCounter++;
                    }
                }
            }
        }

        return $routesCounter;
    }

    /**
     * Helper method to count all the rows printed by the Symfony Console Table component
     * based on the given input parameters.
     *
     * @param array $services
     * @param string $filter
     * @return int
     */
    protected function countTableRowsToPrint(array $services = [], $filter = '')
    {
        if (count($services) === 0 && empty($filter)) {
            // default amount of rows that Symfony Console Table component will render
            return 3;
        }

        return 4 + $this->countRoutes($services, $filter);
    }
}
