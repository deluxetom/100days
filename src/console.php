<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Days\Command\AdminPass;
use Days\Command\CacheClear;
use Days\Command\ResetCounter;

$console = new Application('100 Days Application', '1.0');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);

$console->add(new AdminPass());
$console->add(new CacheClear($app));
$console->add(new ResetCounter($app));

return $console;
