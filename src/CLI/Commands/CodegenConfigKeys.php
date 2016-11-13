<?php

namespace Pintsize\CLI\Commands;

use Pintsize\Common\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CodegenConfigKeys extends Command
{
    protected function configure()
    {
        $this
            ->setName('codegen:configkeys')
            ->setDescription('Create \Pintsize\Common\Config class with property names from Config.yaml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = Yaml::parse(file_get_contents(APPDIR . '/src/Common/ConfigKeys.yaml'));
        $code = file_get_contents(APPDIR . '/src/Common/ConfigKeys.php');
        for($i = 0; $i < 2; $i++) {
            $properties = [];
            foreach($data as $tabname => $tab) {
                $columns = $tab['columns'];
                if(!is_array($columns)) { $columns = []; }
                $tablabel = $tab['label'];
                foreach($tab['settings'] as $key => $value) {
                    $name = "{$tabname}_{$key}";
                    $type = 'string';
                    if($value['type'] == 'int') { $type = 'int'; }
                    if($value['type'] == 'bool') { $type = 'bool'; }
                    $label = $value['label'];
                    $properties[] = $this->propLine($type, $name, "$tablabel: $label");
                    if(in_array('Enabled', $columns)) {
                        $properties[] = $this->propLine($type, $name . '_enabled', "$tablabel: Enable $label");
                    }
                    if(in_array('Reply To', $columns)) {
                        $properties[] = $this->propLine($type, $name . '_replyto', "$tablabel: $label Reply-To");
                    }
                }
        }
        }
        $propcode= "// CODEGEN\n/**\n" . implode("\n", $properties) . "\n */";
        $code = preg_replace('/\/\/ CODEGEN.*?\*\//ms', $propcode, $code);
        file_put_contents(APPDIR . '/src/Common/ConfigKeys.php', $code);
    }
    
    private function propLine($type, $name, $desc) {
        if(!isset($this->typeLen)) { $this->typeLen = 0; }
        if(!isset($this->nameLen)) { $this->nameLen = 0; }
        if(strlen($type) > $this->typeLen) { $this->typeLen = strlen($type); }
        if(strlen($name) > $this->nameLen) { $this->nameLen = strlen($name); }
        $type = str_pad($type, $this->typeLen);
        $name = str_pad($name, $this->nameLen);
        return " * property-read  $type  $name  $desc";
    }
}
