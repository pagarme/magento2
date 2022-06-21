<?php

namespace Pagarme\Pagarme\Console\Command;

use Pagarme\Pagarme\Console\MigrateData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateList extends Command
{

    public function __construct(MigrateData $migrateData)
    {
        $this->migrateData = $migrateData;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("pagarme:migrate:list");
        $this->setDescription("List changes to migrate Mundipagg databases to Pagarme");
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('Wait...');
        $migrateResult = $this->migrateData->run();
        $output->writeln($migrateResult);
    }
}
