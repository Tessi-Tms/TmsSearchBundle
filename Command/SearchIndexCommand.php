<?php

namespace Tms\Bundle\SearchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class SearchIndexCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('tms:search:index')
            ->setDescription('This task intends for indexing elements')
            ->addArgument('index', InputArgument::REQUIRED, 'The index you want to create')
            ->addArgument('manager', InputArgument::REQUIRED, 'The object manager ("entity" or "document")')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeCommand();
        $verbose = $input->getOption('verbose');
        $indexName = $input->getArgument('index');
        $managerType = $input->getArgument('manager');

        if ('document' !== $managerType && 'entity' !== $managerType) {
            if (true === $verbose) {
                $output->writeln('<error>Wrong manager (should be "entity" or "document")</error>');
            }
            return;
        }

        $searchIndexHandler = $this->getContainer()->get('tms_search.handler');
        if ('document' === $managerType) {
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