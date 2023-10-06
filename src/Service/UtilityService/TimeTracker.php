<?php

namespace App\Service\UtilityService;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class TimeTracker
{
    /**
     * @var ObjectManager
     */
    protected $doctrine;
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array $processes
     */
    protected $processes;

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * AlertUtility constructor.
     * @param Container $container
     * @param ObjectManager $doctrine
     */
    public function __construct(Container $container, EntityManagerInterface $doctrine, Stopwatch $stopwatch)
    {
        $this->container    = $container;
        $this->doctrine     = $doctrine;
        $this->stopwatch    = $stopwatch;
    }

    /**
     * @param string|null $section
     */
    public function openSection($section = null): void
    {
        $this->stopwatch->openSection($section);
    }

    /**
     * @param string $section
     */
    public function stopSection(string $section): void
    {
        $this->stopwatch->stopSection($section);
    }

    /**
     * @param string $section
     */
    public function stop(string $section): void
    {
        $this->stopwatch->stop($section);
    }

    /**
     * @param string $section
     * @param string|null $category
     */
    public function start(string $section, string $category = null): void
    {
        $this->stopwatch->start($section, $category);
    }

    /**
     * @param string $section
     * @return array
     */
    public function getSectionEvents(string $section): array
    {
        return $this->stopwatch->getSectionEvents($section);
    }
}
