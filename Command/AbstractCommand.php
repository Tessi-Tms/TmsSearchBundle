<?php

namespace Tms\Bundle\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractCommand extends ContainerAwareCommand
{
    protected $startTime;
    protected $startMemoryUsage;

    protected function initializeCommand()
    {
        $this->startTime = microtime(true);
        $this->startMemoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
    }

    /**
     * @return number
     */
    protected function getExecutionTime()
    {
        return round(microtime(true) - $this->startTime, 2);
    }

    /**
     * @return string
     */
    protected function getMemoryUsage()
    {
        return 'Start: ' . $this->startMemoryUsage . ' Mb - End: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' Mb - Peak: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' Mb';
    }

    /**
     * @return string
     */
    protected function getMemoryCurrentUsage()
    {
        return 'Current: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' Mb';
    }
}
