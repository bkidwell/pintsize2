<?php

namespace Pintsize\CLI\Commands;

use Pintsize\Common\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbLoadOldData extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:loadolddata')
            ->setDescription('Load CSV dump from music-stream-vote')
            ->setDefinition([
                new InputArgument('path', InputArgument::REQUIRED, 'Path of the three .csv files to load')
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->path = $input->getArgument('path');

        $db = Config::getF3Db();
        $pdo = $db->pdo();

        $map = $this->getMap();
        foreach($map as $parts) {
            $filename = $parts[0];
            $table = $parts[1];
            $keymap = $parts[2];
            $this->doFile($filename, $table, $keymap, $db);
        }
    }
    
    private function doFile($filename, $table, $keymap, \DB\SQL $db) {
        $fpath = $this->path . '/' . $filename . '.csv';
        echo "$fpath\n";
        $file = new \SplFileObject($fpath, 'r');

        $i = 0;
        $columns = $file->fgetcsv();
        $columns[0] = $this->removeBom($columns[0]);

        $toColumns = array_keys($keymap);
        $insertStatement = $db->pdo()->prepare(
            "INSERT INTO $table (`" .
            implode('`, `', $toColumns) .
            "`) VALUES (:" .
            implode(', :', $toColumns) .
            ")"
        );
        
        $db->exec("ALTER TABLE $table DISABLE KEYS");
        $db->begin();
        echo "$filename: 0 rows\n";
        $csvValues = [];
        $writeValues = [];
        $notNull = ['artist', 'title'];
        $isBit = ['is_authed', 'deleted'];
        while(!$file->eof()) {
            $csvRow = $file->fgetcsv();
            $colNum = 0;
            if($csvRow[0] == '') { continue; }
            foreach($columns as $column) {
                $value = $csvRow[$colNum];
                if(in_array($column, $isBit)) {
                    $value = ($value == 1) ? true : false;
                } elseif(strlen($value) == 0 && !in_array($column, $notNull)) {
                    $value = null;
                }
                $csvValues[$column] = $value;
                $colNum++;
            }
            foreach($keymap as $to => $from) {
                $writeValues[":$to"] = $csvValues[$from];
            }
            if($table == 'vote') {
                $writeValues['source'] = 'irc';
            }

            $insertStatement->execute($writeValues);

            $i++;
            if($i % 100 == 0) {
                echo "\e[A$filename: $i rows\n";
            }
            if($i % 10000 == 0) {
                $db->commit();
                $db->begin();
            }
        }
        $db->commit();
        $db->exec("ALTER TABLE $table ENABLE KEYS");
        echo "$i rows read.\n\n";
    }

    private function getMap() {
        return [
            [
                'wp_musvote_track', 'track',
                [
                    'id' => 'id',
                    'key' => 'track_key',
                    'artist' => 'artist',
                    'title' => 'title',
                    'play_count' => 'play_count',
                    'vote_count' => 'vote_count',
                    'vote_total' => 'vote_total',
                    'vote_average' => 'vote_average',
                ]
            ],
            [
                 'wp_musvote_play', 'play',
                 [
                    'id' => 'id',
                     'time_utc' => 'time_utc',
                     'track_id' => 'track_id',
                 ]
            ],
            [
                'wp_musvote_vote', 'vote',
                [
                    'id' => 'id',
                    'time_utc' => 'time_utc',
                    'track_id' => 'track_id',
                    'value' => 'value',
                    'nick' => 'nick',
                    'source' => 'source',
                    'user_id' => 'user_id',
                    'is_authed' => 'is_authed',
                    'deleted' => 'deleted',
                    'comment' => 'comment',
                ]
            ],
        ];
    }
    
    private function removeBom($text) : string {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
}
