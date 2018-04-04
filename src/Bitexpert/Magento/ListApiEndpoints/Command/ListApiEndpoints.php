<?php

namespace Bitexpert\Magento\ListApiEndpoints\Command;

use Magento\Framework\App\ObjectManager;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListApiEndpoints extends AbstractMagentoCommand
{

    protected function configure()
    {
        $this
            ->setName('api:list:endpoints')
            ->setDescription('List all API endpoints')
        ;
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
            /** @var \Magento\Webapi\Model\Config $serviceConfig */
            $serviceConfig = ObjectManager::getInstance()->get(\Magento\Webapi\Model\Config::class);
            $services = $serviceConfig->getServices();

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
}