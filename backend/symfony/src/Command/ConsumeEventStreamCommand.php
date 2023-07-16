<?php

declare(strict_types=1);

namespace SymfonyApp\Command;

use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TaskManager\Projections\Domain\Service\Projector\ProjectionistInterface;

#[AsCommand(name: 'event_stream:consume', description: 'Consume events from a stream')]
final class ConsumeEventStreamCommand extends Command
{
    public function __construct(
        private readonly ProjectionistInterface $projectionist,
        private readonly ClockInterface $clock = new Clock(),
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument(
                'sleep',
                InputArgument::REQUIRED,
                'Amount of sleep between executions in seconds'
            ),
        ]);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(
            $input,
            $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output
        );
        $sleepAmount = (int) $input->getArgument('sleep');
        $io->success(sprintf('Consuming messages from event stream every %s second(s).', $sleepAmount));

        while (true) {
            $info = $this->projectionist->projectAll();

            if ($output->isVeryVerbose()) {
                foreach ($info as $dto) {
                    $io->comment(
                        sprintf('Consumed %s event(s) for projector "%s"', $dto->eventCount, $dto->projector).
                        ($dto->isBroken ? ' because it\'s broken' : '')
                    );
                }
            }

            $this->clock->sleep($sleepAmount);
        }

        return 0;
    }
}
