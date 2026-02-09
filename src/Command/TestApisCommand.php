<?php

namespace App\Command;

use App\Service\GeminiService;
use App\Service\MailtrapService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-apis',
    description: 'Test all external API integrations (OpenRouter, Mailtrap)',
) ]
class TestApisCommand extends Command
{
    public function __construct(
        private \App\Service\OpenRouterService $openRouterService,
        private MailtrapService $mailtrapService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Testing External APIs');

        $io->title('Testing External APIs');

        $io->title('Testing External APIs');

        // 3. OpenRouter (Llama) Test
        $io->section('Testing OpenRouter (Llama) API');
        if ($_ENV['OPENROUTER_API_KEY'] ?? false) {
            $io->text('Sending prompt: "Hello"');
            $response = $this->openRouterService->chat('Hello');
            if (str_contains($response, 'Erreur')) {
                $io->error('OpenRouter: ' . $response);
            } else {
                $io->success('OpenRouter: OK (' . substr($response, 0, 50) . '...)');
            }
        } else {
            $io->warning('OpenRouter: Skipped (Missing OPENROUTER_API_KEY)');
        }

        // 4. Mailtrap Test
        $io->section('Testing Mailtrap API');
        if ($_ENV['MAILTRAP_API_TOKEN'] ?? false) {
             $io->text('Sending verification email...');
             $success = $this->mailtrapService->sendEmail('ahmedmhirsi955@gmail.com', 'Test Mailtrap', 'Test from TestApisCommand');
             if ($success) {
                 $io->success('Mailtrap: OK');
             } else {
                 $io->error('Mailtrap: Failed');
             }
        } else {
            $io->warning('Mailtrap: Skipped (Missing MAILTRAP_API_TOKEN)');
        }

        return Command::SUCCESS;
    }
}
