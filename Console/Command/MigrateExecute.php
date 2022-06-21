<?php

namespace Pagarme\Pagarme\Console\Command;

use Pagarme\Pagarme\Console\MigrateData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateExecute extends Command
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
        $this->setName("pagarme:migrate:execute");
        $this->setDescription("Execute migrate Mundipagg databases to Pagarme");
        $this->setDefinition([
            new InputArgument('group', InputArgument::OPTIONAL, "Migrating group of tables"),
            new InputArgument('limit', InputArgument::OPTIONAL, "Limit number of lines")
        ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $arg[] = 'execute';
        $arg[] = $input->getArgument('group');
        $arg[] = $input->getArgument('limit');
        $output->writeln('Wait...' . $arg[0] . ' '. $arg[1]);
        $migrateResult = $this->migrateData->run($arg);
        $output->writeln($migrateResult);
    }
}
