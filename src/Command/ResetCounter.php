<?php

namespace Days\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Silex\Application;


class ResetCounter extends Command
{
    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    protected function configure()
    {
        $this
            ->setName('reset:counter')
            ->addArgument(
                'userId',
                InputArgument::OPTIONAL,
                'userId'
            )
            ->setDescription('Delete all DB table counter entries for a given userId. Use "all" to delete entries from ALL users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $input->getArgument('userId') ? : 'all';
        $conditions = [];
        if ($userId != 'all' && is_numeric($userId)) {
            $conditions['userId'] = $userId;
        }
        $this->app['repository.counter']->deleteByConditions($conditions);
    }
}