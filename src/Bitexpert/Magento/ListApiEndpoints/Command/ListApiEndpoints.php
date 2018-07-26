<?php

/*
 * This file is part of the magerun2-list-api-endpoints package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitexpert\Magento\ListApiEndpoints\Command;

use Magento\Framework\App\ObjectManager;
use N98\Magento\Command\AbstractMagentoCommand;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListApiEndpoints extends AbstractMagentoCommand
{

    const OPTION_OUTPUT_FORMAT = 'output-format';

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
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $outputFormat = $input->getOption(self::OPTION_OUTPUT_FORMAT);
            /** @var \Magento\Webapi\Model\Config $serviceConfig */
            $serviceConfig = ObjectManager::getInstance()->get(\Magento\Webapi\Model\Config::class);
            $services = $serviceConfig->getServices();

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
     * @param array $services
     * @param OutputInterface $output
     */
    private function printAsTable(array $services, OutputInterface $output)
    {
        //format the table
        $table = new Table($output);
        $table->setHeaders(array('Method', "Route", "Resources"));

        foreach ($services['routes'] as $route => $methods) {
            foreach ($methods as $method => $config) {
                $table->addRow([
                    sprintf('<fg=green>%s</>', $method),
                    sprintf('<fg=white>%s</>', $route),
                    sprintf('<fg=red>%s</>', json_encode($config['resources']))
                ]);
            }
        }

        $table->render();
    }
}
