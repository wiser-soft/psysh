<?php

namespace Psy\Command;

use Psy\Command\Command;
use Psy\Shell;
use Psy\ShellAware;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HistoryCommand extends Command implements ShellAware
{
    /**
     * Shell instance (for ShellAware interface)
     *
     * @type Psy\Shell
     */
    private $shell;

    /**
     * ShellAware interface.
     *
     * @param Psy\Shell $shell
     */
    public function setShell(Shell $shell)
    {
        $this->shell = $shell;
    }

    protected function configure()
    {
        $this
            ->setName('history')
            ->setAliases(array('hist'))
            ->setDefinition(array(
                new InputOption('show',        's', InputOption::VALUE_REQUIRED, 'Show the given range of lines'),
                new InputOption('head',        'H', InputOption::VALUE_OPTIONAL, 'Display the first N items.'),
                new InputOption('tail',        'T', InputOption::VALUE_OPTIONAL, 'Display the last N items.'),

                new InputOption('grep',        'G', InputOption::VALUE_REQUIRED, 'Show lines matching the given pattern (string or regex).'),
                new InputOption('insensitive', 'i', InputOption::VALUE_NONE,     'Case insensitive search (requires --grep).'),
                new InputOption('invert',      'v', InputOption::VALUE_NONE,     'Inverted search (requires --grep).'),

                new InputOption('no-numbers',  'N', InputOption::VALUE_NONE,     'Omit line numbers.'),

                new InputOption('save',        '',  InputOption::VALUE_REQUIRED, 'Save history to a file.'),
                new InputOption('replay',      '',  InputOption::VALUE_NONE,     'Replay'),
            ))
            ->setDescription('Show the Psy shell history.')
            ->setHelp('Show, search or save the Psy shell history.')
        ;
    }

    protected function extractRange($range)
    {
        if (preg_match('/^\d+$/', $range)) {
            return array($range, $range + 1);
        }

        $matches = array();
        if ($range !== '..' && preg_match('/^(\d*)\.\.(\d*)$/', $range, $matches)) {
            $start = $matches[1] ? intval($matches[1]) : 0;
            $end   = $matches[2] ? intval($matches[2]) + 1 : PHP_INT_MAX;

            return array($start, $end);
        }

        throw new \InvalidArgumentException('Unexpected range: '.$range);
    }

    protected function getHistorySlice($show, $head, $tail)
    {
        $history = readline_list_history();

        if ($show) {
            list($start, $end) = $this->extractRange($show);
            $length = $end - $start;
        } elseif ($head) {
            if (!preg_match('/^\d+$/', $head)) {
                throw new \InvalidArgumentException('Please specificy an integer argument for --head.');
            }

            $start  = 0;
            $length = intval($head);
        } elseif ($tail) {
            if (!preg_match('/^\d+$/', $tail)) {
                throw new \InvalidArgumentException('Please specificy an integer argument for --tail.');
            }

            $start  = count($history) - $tail;
            $length = intval($tail) + 1;
        } else {
            return $history;
        }

        return array_slice($history, $start, $length, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateOnlyOne($input, array('show', 'head', 'tail'));
        $this->validateOnlyOne($input, array('save', 'replay'));

        $history = $this->getHistorySlice(
            $input->getOption('show'),
            $input->getOption('head'),
            $input->getOption('tail')
        );
        $highlighted = false;

        $invert      = $input->getOption('invert');
        $insensitive = $input->getOption('insensitive');
        if ($pattern = $input->getOption('grep')) {
            if (strlen($pattern) < 3 || substr($pattern, 0, 1) != substr($pattern, -1)) {
                $pattern = '/'.preg_quote($pattern, '/').'/';
            }
            if ($insensitive) {
                $pattern .= 'i';
            }

            $ret = array();
            $matches = array();
            $highlighted = array();
            foreach ($history as $i => $line) {
                if (preg_match($pattern, $line, $matches) xor $invert) {
                    $highlighted[$i] = str_replace($matches[0], sprintf('<strong>%s</strong>', $matches[0]), $history[$i]);
                } else {
                    unset($history[$i]);
                }
            }
        } elseif ($invert) {
            throw new \InvalidArgumentException('Cannot use -v without --grep.');
        } elseif ($insensitive) {
            throw new \InvalidArgumentException('Cannot use -i without --grep.');
        }

        if ($save = $input->getOption('save')) {
            $output->writeln(sprintf('Saving history in %s...', $save));
            file_put_contents($save, implode(PHP_EOL, $history) . PHP_EOL);
            $output->writeln('<info>History saved.</info>');
        } elseif ($input->getOption('replay')) {
            if (!($input->getOption('show') || $input->getOption('head') || $input->getOption('tail'))) {
                throw new \InvalidArgumentException('You must limit history via --head, --tail or --show before replaying.');
            }

            $count = count($history);
            $output->writeln(sprintf('Replaying %d line%s of history', $count, ($count != 1) ? 's' : ''));
            $this->shell->addInput($history);
        } else {
            if ($input->getOption('no-numbers')) {
                $output->writeln($highlighted ?: $history);
            } else {
                $output->writelnnos($highlighted ?: $history);
            }
        }
    }

    protected function validateOnlyOne(InputInterface $input, array $options)
    {
        $count = 0;
        foreach ($options as $opt) {
            if ($input->getOption($opt)) {
                $count++;
            }
        }

        if ($count > 1) {
            throw new \InvalidArgumentException('Please specify only one of --'.implode(', --', $options));
        }
    }
}
