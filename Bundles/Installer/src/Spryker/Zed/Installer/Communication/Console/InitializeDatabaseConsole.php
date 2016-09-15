<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Installer\Communication\Console;

use Spryker\Zed\Console\Business\Model\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Installer\Business\InstallerFacade getFacade()
 */
class InitializeDatabaseConsole extends Console
{

    const COMMAND_NAME = 'setup:init-db';
    const DESCRIPTION = 'Fill the database with required data';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME)
            ->setDescription(static::DESCRIPTION);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installerPlugins = $this->getInstallerPlugins();

        $messenger = $this->getMessenger();

        try {
            foreach ($installerPlugins as $plugin) {
                $name = $this->getPluginNameFromClass(get_class($plugin));

                $output->writeln('Installing DB data for ' . $name);

                $plugin->setMessenger($messenger);
                $plugin->run();
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return static::CODE_ERROR;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }

    /**
     * @return \Spryker\Zed\Installer\Communication\Plugin\AbstractInstallerPlugin[]
     */
    protected function getInstallerPlugins()
    {
        return $this->getFacade()->getInstallerPlugins();
    }

    /**
     * @param string $className
     *
     * @return mixed
     */
    protected function getPluginNameFromClass($className)
    {
        $pattern = '#^(.+)\\\(.+)\\\(.+)\\\(.+)\\\(.*)$#i';
        return preg_replace($pattern, '${2}', $className);
    }

}
