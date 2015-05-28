<?php

namespace Days\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


class AdminPass extends Command
{

    protected function configure()
    {
        $this
            ->setName('admin:pass')
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Password to crypt'
            )
            ->addArgument(
                'salt',
                InputArgument::OPTIONAL,
                'salt to encode the pass'
            )
            ->setDescription('Crypt a password for the security firewall')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($pass = $input->getArgument('password')) {
            if (!$salt = $input->getArgument('salt')) {
                $salt = md5($pass);
            }

            $encoder = new MessageDigestPasswordEncoder();
            $crypted = $encoder->encodePassword($pass, $salt);
            $output->writeln('<info>Pass:</info>');
            $output->writeln($pass);
            $output->writeln('<info>Crypted Pass:</info>');
            $output->writeln($crypted);
            $output->writeln('<info>Salt:</info>');
            $output->writeln($salt);
            $output->writeln(' ');
        }
    }
}