<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Artisan wrapper for the VoxTerra Python Scout Bot.
 *
 * Calls scout/scout.py as a subprocess and streams its output in real time.
 * All heavy lifting (search, enrich, DB write) stays in Python — this
 * command is purely a convenient Laravel-native entry point.
 *
 * Usage examples
 * --------------
 *   php artisan scout:run
 *   php artisan scout:run --country=MX
 *   php artisan scout:run --country=BR --max=20 --delay=2
 *   php artisan scout:run --jobs=5
 *   php artisan scout:run --dry-run
 *
 * Scheduling (in bootstrap/app.php or a ServiceProvider)
 * ------------------------------------------------------
 *   $schedule->command('scout:run --jobs=3')->daily();
 */
class ScoutRun extends Command
{
    protected $signature = 'scout:run
                            {--country=  : ISO 2-letter country code (random if omitted)}
                            {--max=10    : Max URLs to process per job}
                            {--delay=1.5 : Seconds between site fetch requests}
                            {--queries=0 : Limit query templates (0 = all)}
                            {--jobs=1    : Number of sequential scout jobs}
                            {--dry-run   : Search + enrich but skip DB writes}';
    // Tip: pass -v (Laravel built-in verbosity flag) to also enable --verbose in the Python script

    protected $description = 'Run the VoxTerra AI Scout Bot to discover local news sites worldwide.';

    public function handle(): int
    {
        $pythonBin  = $this->resolvePython();
        $scriptPath = base_path('../scout/scout.py');

        if (!file_exists($scriptPath)) {
            $this->error("Scout script not found at: {$scriptPath}");
            $this->line("Expected location: <project-root>/scout/scout.py");
            return self::FAILURE;
        }

        if (!$pythonBin) {
            $this->error("Python 3 not found. Install it and make sure 'python3' or 'python' is on PATH.");
            return self::FAILURE;
        }

        // Auto-install Python dependencies if needed
        if (!$this->ensureDepsInstalled($pythonBin, $scriptPath)) {
            return self::FAILURE;
        }

        $cmd = $this->buildCommand($pythonBin, $scriptPath);

        $this->info("▶  Starting VoxTerra Scout Bot");
        $this->line("   Command: " . implode(' ', $cmd));
        $this->newLine();

        $process = new Process(
            command: $cmd,
            cwd: dirname($scriptPath),
            timeout: null,           // no timeout — long runs are expected
        );

        $process->run(function (string $type, string $output): void {
            // Stream Python output line-by-line into the Artisan console
            foreach (explode("\n", rtrim($output)) as $line) {
                if ($line === '') continue;

                if (str_contains($line, '[ERROR]') || str_contains($line, 'Error')) {
                    $this->error($line);
                } elseif (str_contains($line, '[WARNING]') || str_contains($line, 'Warning')) {
                    $this->warn($line);
                } elseif (str_contains($line, '✅') || str_contains($line, 'done')) {
                    $this->info($line);
                } else {
                    $this->line($line);
                }
            }
        });

        $this->newLine();

        if ($process->isSuccessful()) {
            $this->info("✅  Scout Bot finished successfully.");
            return self::SUCCESS;
        }

        $this->error("Scout Bot exited with code " . $process->getExitCode());
        return self::FAILURE;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildCommand(string $pythonBin, string $scriptPath): array
    {
        $cmd = [$pythonBin, $scriptPath];

        if ($country = $this->option('country')) {
            $cmd[] = '--country=' . $country;
        }

        $cmd[] = '--max='     . $this->option('max');
        $cmd[] = '--delay='   . $this->option('delay');
        $cmd[] = '--queries=' . $this->option('queries');
        $cmd[] = '--jobs='    . $this->option('jobs');

        if ($this->option('dry-run')) {
            $cmd[] = '--dry-run';
        }

        // -v or -vv (Laravel built-in verbosity flags) → pass --verbose to Python
        if ($this->getOutput()->isVerbose()) {
            $cmd[] = '--verbose';
        }

        return $cmd;
    }

    private function ensureDepsInstalled(string $pythonBin, string $scriptPath): bool
    {
        $requirementsFile = dirname($scriptPath) . '/requirements.txt';

        if (!file_exists($requirementsFile)) {
            $this->warn("requirements.txt not found at: {$requirementsFile} — skipping dep check.");
            return true;
        }

        // Quick check: try importing the first critical package
        $check = new Process([$pythonBin, '-c', 'import dotenv, pymysql, bs4, requests']);
        $check->run();

        if ($check->isSuccessful()) {
            return true; // all good, nothing to install
        }

        $this->line("📦  Installing Python dependencies from requirements.txt...");

        $pip = new Process(
            [$pythonBin, '-m', 'pip', 'install', '-r', $requirementsFile, '--quiet'],
            cwd: dirname($scriptPath),
            timeout: 120,
        );

        $pip->run(function (string $type, string $output): void {
            if (trim($output)) {
                $this->line('   ' . trim($output));
            }
        });

        if (!$pip->isSuccessful()) {
            $this->error("pip install failed:\n" . $pip->getErrorOutput());
            $this->line("Run manually:  pip install -r " . $requirementsFile);
            return false;
        }

        $this->info("✅  Dependencies installed.");
        $this->newLine();
        return true;
    }

    private function resolvePython(): ?string
    {
        foreach (['python3', 'python'] as $candidate) {
            $check = new Process([$candidate, '--version']);
            $check->run();
            if ($check->isSuccessful()) {
                return $candidate;
            }
        }
        return null;
    }
}
