<?php

namespace RushlowDevelopment\DevApps\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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

        if (!is_dir($sourcePath)) {
            // The source is a file, create the link.
            return $this->createLink($sourcePath, $targetPath);
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('The target path already exists. Do you want to replace it with the symlink?', false);

        if ($fs->exists($targetPath) && $helper->ask($input, $output, $question)) {
            // The source is not a dir, the target already exists, & the user wants to remove it.
            $fs->remove($targetPath);
        }

        return $this->createLink($sourcePath, $targetPath);
    }

    private function createLink(string $source, string $target): int
    {
        $process = Process::fromShellCommandline(sprintf('ln -s %s %s', $source, $target));
        return $process->run();
    }
}