<?php

namespace Tms\Bundle\SearchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class SearchIndexCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('tms:search:index')
            ->setDescription('This task intends for indexing elements')
            ->addArgument('index', InputArgument::REQUIRED, 'The index you want to create')
            ->addArgument('manager', InputArgument::REQUIRED, 'The object manager (ORM || ODM)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeCommand();
        $verbose = $input->getOption('verbose');
        $indexName = $input->getArgument('index');
        $managerType = $input->getArgument('manager');

        if ('ODM' !== $managerType && 'ORM' !== $managerType) {
            if (true === $verbose) {
                $output->writeln('<error>Wrong manager (should be ODM or ORM)</error>');
            }
            exit;
        }

        $searchIndexHandler = $this->getContainer()->get('tms_search.handler');
        if ('ODM' === $managerType) {
            $indexedElements = $searchIndexHandler->batchIndexDocuments($indexName, ($verbose ? $output : null));
        } else {
            $indexedElements = $searchIndexHandler->batchIndexEntities($indexName, ($verbose ? $output : null));
        }

        if (true === $verbose) {
            $message = '<info>' . $indexedElements . ' indexes created. - Execution time: ' . $this->getExecutionTime() . ' second(s) - Memory Usage: ' . $this->getMemoryUsage() . '</info>';
            $output->writeln($message);
        }
    }
}