<?php

namespace Psy\Command;

use Psy\Configuration;
use Psy\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class TimeitCommand
 * @package Psy\Command
 */
class TimeitCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('timeit')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::REQUIRED, 'A target object or primitive to profile.', null),
            ))
            ->setDescription('Profiles with a timer.')
            ->setHelp(
                <<<HELP
Time profiling for functions and commands.

e.g.
<return>>>> timeit \$closure()</return>
HELP
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $start = microtime(true);

        /** @var Shell $shell */
        $shell = $this->getApplication();
        $sh = new Shell(new Configuration());
        $sh->setOutput($output);
        $sh->setScopeVariables($shell->getScopeVariables());
        $sh->execute($target);

        $end = microtime(true);

        $output->writeln(sprintf('<info>Command took %.6f seconds to complete.</info>', $end-$start));
    }
}
