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
use Magento\Framework\App\ObjectManager;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListApiEndpoints extends AbstractMagentoCommand
{
    const OPTION_OUTPUT_FORMAT = 'output-format';
    const OPTION_FILTER_METHOD = 'method';
    const OPTION_FILTER_ROUTE = 'route';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('api:list:endpoints')
            ->setDescription('List all API endpoints')
            ->addOption(
                self::OPTION_OUTPUT_FORMAT,
                'o',
                InputOption::VALUE_OPTIONAL,
                'Specify the desired output format [table (default)]',
                'table'
            )
            ->addOption(
                self::OPTION_FILTER_METHOD,
                'm',
                InputOption::VALUE_OPTIONAL,
                'Filters routes for given method. Pass multiple methods as comma-separated list',
                ''
            )
            ->addOption(
                self::OPTION_FILTER_ROUTE,
                'r',
                InputOption::VALUE_OPTIONAL,
                'Filters routes by given part',
                ''
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $methodFilter = $input->getOption(self::OPTION_FILTER_METHOD);
            $routeFilter = $input->getOption(self::OPTION_FILTER_ROUTE);
            $services = $this->getDefinedServices();
            $services = $this->filterServices($services, $methodFilter, $routeFilter);

            $outputFormat = $input->getOption(self::OPTION_OUTPUT_FORMAT);
            switch ($outputFormat) {
                case 'table':
                    $this->printAsTable($services, $output);
                    break;
                default:
                    throw new InvalidArgumentException('Selected output-format is not a valid option');
            }
        }
    }

    /**
     * @return array
     */
    protected function getDefinedServices()
    {
        /** @var \Magento\Webapi\Model\Config $serviceConfig */
        $serviceConfig = ObjectManager::getInstance()->get(\Magento\Webapi\Model\Config::class);
        return $serviceConfig->getServices();
    }

    /**
     * @param array $services
     * @param OutputInterface $output
     */
    private function printAsTable(array $services, OutputInterface $output)
    {
        //format the table
        $table = new Table($output);
        $table->setHeaders(array('Method', "Route", "Resources"));

        if(isset($services['routes']) && is_array($services['routes'])) {
            foreach ($services['routes'] as $route => $methods) {
                foreach ($methods as $method => $config) {
                    $table->addRow([
                        sprintf('<fg=green>%s</>', $method),
                        sprintf('<fg=white>%s</>', $route),
                        sprintf('<fg=red>%s</>', json_encode($config['resources']))
                    ]);
                }
            }
        }

        $table->render();
    }

    /**
     * Remove routes from given $services array that do not match given $methodsToFilter. $methodsToFilter can
     * contain a single HTTP method like GET or POST or multiple ones separated by a comma.
     *
     * @param array $services
     * @param string $methodsToFilter
     * @param string $routesToFilter
     * @return array
     */
    private function filterServices(array $services, $methodsToFilter, $routesToFilter)
    {
        if(!isset($services['routes']) || !is_array($services['routes'])) {
            return $services;
        }

        if(!empty($routesToFilter)) {
            foreach ($services['routes'] as $route => $methods) {
                if (strpos($route, $routesToFilter) === false) {
                    unset($services['routes'][$route]);
                }
            }
        }

        if(!empty($methodsToFilter)) {
            $methodsToFilterArray = explode(',', strtoupper($methodsToFilter));
            array_walk($methodsToFilterArray, function(&$value, $index) {
                $value = trim($value);
            });

            foreach ($services['routes'] as $route => $methods) {
                foreach ($methods as $method => $config) {
                    if(!in_array($method, $methodsToFilterArray)) {
                        unset($services['routes'][$route][$method]);
                    }
                }
            }
        }

        return $services;
    }
}
