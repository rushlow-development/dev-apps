<?php

namespace RushlowDevelopment\DevApps\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'linker', description: 'Create a symlink')]
class LinkCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED, 'The location that the symlink will point to.')
            ->addArgument('target', InputArgument::REQUIRED, 'The location where the "source" should be made available.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fs = new Filesystem();

        $sourcePath = (string) $input->getArgument('source');
        $targetPath = (string) $input->getArgument('target');

        if (!$fs->exists($sourcePath)) {
            $output->writeln(sprintf('Source path doesn\'t exist: %s', $sourcePath));

            return Command::FAILURE;
        }

        if ($fs->exists($targetPath)) {
            $fs->remove($targetPath);
        }

        $process = Process::fromShellCommandline(sprintf('ln -sF %s %s', $sourcePath, $targetPath));
        return $process->run();
    }
}