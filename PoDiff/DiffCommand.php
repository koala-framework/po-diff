<?php
namespace PoDiff;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->setName('diff')
            ->setDescription('Show diff of two po files')
            ->addArgument('old-file', InputArgument::REQUIRED)
            ->addArgument('new-file', InputArgument::REQUIRED)
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Filter results to added, removed or changed', 'all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filter = $input->getOption('filter');
        $differ = new Differ($input->getArgument('old-file'), $input->getArgument('new-file'));
        $diff = $differ->getDiff();
        if ($filter == 'all' || $filter == 'added') {
            $output->writeLn('');
            $output->writeLn('Added translations '.count($diff['added']));
            foreach ($diff['added'] as $entry) {
                $this->_outputEntry($output, $entry);
            }
        }
        if ($filter == 'all' || $filter == 'removed') {
            $output->writeLn('');
            $output->writeLn('Removed translations '.count($diff['removed']));
            foreach ($diff['removed'] as $entry) {
                $this->_outputEntry($output, $entry);
            }
        }
        if ($filter == 'all' || $filter == 'changed') {
            $output->writeLn('');
            $output->writeLn('Changed translations '.count($diff['changed']));
            foreach ($diff['changed'] as $entries) {
                $this->_outputEntry($output, $entries['old']);
                $this->_outputEntry($output, $entries['new']);
            }
        }
    }

    private function _outputEntry($output, $entry)
    {
        $msgId = isset($entry['msgid']) ? implode($entry['msgid']) : '';
        $output->writeLn($msgId);

        foreach ($entry as $key => $value) {
            if (strpos($key, 'msgstr') !== 0) continue;
            $translation = isset($entry[$key]) ? implode($entry[$key]): '';
            $output->writeLn("\t".str_replace('msgstr', '', $key).' '.$translation);
        }
    }
}
