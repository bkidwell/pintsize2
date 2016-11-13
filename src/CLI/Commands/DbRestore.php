<?php

namespace Pintsize\CLI\Commands;

use Pintsize\Common\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DbRestore extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:restore')
            ->setDescription('Restore the database')
            ->setDefinition([
                new InputArgument('file', InputArgument::REQUIRED, $description = 'SQL file to restore from')
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        $host = Config::get('database.host');
        $port = Config::get('database.port');
        $dbname = Config::get('database.dbname');
        $username = Config::get('database.username');
        $password = Config::get('database.password');
        
        $cli = '"' . Config::get('mysql.mysql_bin') . '" ';
        $cli .= "--host=$host ";
        $cli .= "--user=$username ";
        $cli .= "--password=\"$password\" ";
        if($port) {
            $cli .= "--port=$port ";
        }
        $cli .= "$dbname <\"$file\"";
        
        system($cli);
        $output->writeLn("<info>Database has been restored from \"$file\".</info>");
    }
}
