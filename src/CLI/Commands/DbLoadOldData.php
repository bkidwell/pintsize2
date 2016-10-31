<?php

namespace Pintsize\CLI\Commands;

use Pintsize\Common\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Csv\Reader;
use DB\SQL\Mapper;

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
    
    private function doFile($filename, $table, $keymap, $db) {
        $fpath = $this->path . '/' . $filename . '.csv';
        echo "$fpath\n";
        $csv = Reader::createFromPath($fpath);
        $csv->stripBom(true);

        $i = 0;
        $data = $csv->fetchAssoc(0);
        $db->begin();
        echo "$filename: 0 rows\n";
        foreach($data as $row) {
            $i++;
            if($i % 2000 == 0) {
                $db->commit();
                $db->begin();
                echo "\e[A$filename: $i rows\n";
            }

            foreach(array_keys($row) as $key) {
                if($row[$key] == '') { $row[$key] = null; }
            }

            $track = new Mapper($db, $table);
            foreach($keymap as $to => $from) {
                $track->set($to, $row[$from]);
            }
            $track->save();
        }
        $db->commit();
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
}
