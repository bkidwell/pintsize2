<?php

namespace Pintsize\CLI\Commands;

use Pintsize\Common\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DbReset extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:reset')
            ->setDescription('Clear and recreate an empty database')
            ->setDefinition([
                new InputOption('force', 'f')
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        if (!$force) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'Are you sure you want to reset the database and lose all data? (y/n) ', false
            );
            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }
        $db = Config::getF3Db();
        $pdo = $db->pdo();
        $sql = file_get_contents(APPDIR . '/sql/reset-and-create.sql');

        $statement = $pdo->prepare($sql);
        $statement->execute();
        while ($statement->nextRowset()) {/* https://bugs.php.net/bug.php?id=61613 */};

        if($result === FALSE) {
            var_dump($db->pdo()->errorInfo());
        }
        $output->writeLn('<info>Database has been reset.</info>');
    }
}
