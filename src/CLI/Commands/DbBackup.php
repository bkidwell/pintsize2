<?php

namespace Pintsize\CLI\Commands;

use Pintsize\Common\Config;
use Pintsize\Common\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbBackup extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:backup')
            ->setDescription('Backup the database to an SQL file in ./data/backups')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $folder = APPDIR . '/data/backups';
        Util::createPath($folder);
        chdir($folder);
        $file = 'pintsize ' . (new \DateTime())->format('Y-m-d H.i.s') . '.sql';
        
        $host = Config::get('database.host');
        $port = Config::get('database.port');
        $dbname = Config::get('database.dbname');
        $username = Config::get('database.username');
        $password = Config::get('database.password');
        
        $cli = '"' . Config::get('mysql.mysqldump_bin') . '" ';
        $cli .= "--host=$host ";
        $cli .= "--user=$username ";
        $cli .= "--password=\"$password\" ";
        if($port) {
            $cli .= "--port=$port ";
        }
        $cli .= "$dbname >\"$file\"";
        
        system($cli);
        $output->writeLn("<info>Database has been backed up to \"$file\".</info>");
    }
}
