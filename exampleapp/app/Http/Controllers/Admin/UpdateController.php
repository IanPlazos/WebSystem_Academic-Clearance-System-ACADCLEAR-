<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;
use Throwable;

class UpdateController extends Controller
{
    public function __construct(private readonly AppUpdateService $appUpdateService)
    {
    }

    public function index(Request $request): View
    {
        $forceRefresh = $request->boolean('refresh');
        $status = $this->appUpdateService->getStatus($forceRefresh);

        return view('admin.update.index', [
            'currentVersion' => $status['current_version'] ?? config('app.version', '1.0.0'),
            'latestVersion' => $status['latest_version'] ?? null,
            'hasUpdate' => (bool) ($status['has_update'] ?? false),
            'isUpToDate' => $status['is_up_to_date'] ?? null,
            'updateError' => $status['error'] ?? null,
            'localChanges' => $this->localChangeLines(),
        ]);
    }

    public function install(Request $request): RedirectResponse
    {
        $status = $this->appUpdateService->getStatus();

        if (($status['is_up_to_date'] ?? null) === true) {
            return redirect()
                ->route('admin.update.index')
                ->with('update_success', 'App is already updated to the latest version (' . ($status['current_version'] ?? 'current') . ').');
        }

        $localChanges = $this->localChangeLines();
        if ($localChanges !== []) {
            return redirect()
                ->route('admin.update.index')
                ->with('update_error', 'Update blocked because this app has uncommitted local changes. Commit or stash them first.')
                ->with('update_logs', [[
                    'label' => 'Preflight check',
                    'command' => 'git status --short -- .',
                    'exit_code' => 1,
                    'output' => "Uncommitted changes found inside exampleapp:\n  " . implode("\n  ", $localChanges),
                ]]);
        }

        $command = $this->updateCommand();
        $logs = [[
            'label' => 'Download latest code and run update tasks',
            'command' => $this->displayCommand($command),
            'exit_code' => null,
            'output' => 'Starting update...',
        ]];

        try {
            $result = Process::path(base_path())
                ->timeout((int) config('services.app_updates.timeout', 600))
                ->run($command);

            $output = trim($result->output() . PHP_EOL . $result->errorOutput());
            $logs[0]['exit_code'] = $result->exitCode();
            $logs[0]['output'] = $output !== '' ? $output : 'No output.';

            if ($result->failed()) {
                $failureSummary = $this->failureSummary($logs[0]['output']);

                return redirect()
                    ->route('admin.update.index')
                    ->with('update_error', 'Update failed while downloading or applying the latest code. ' . $failureSummary)
                    ->with('update_logs', $logs);
            }
        } catch (Throwable $e) {
            $logs[0]['exit_code'] = 1;
            $logs[0]['output'] = $e->getMessage();

            return redirect()
                ->route('admin.update.index')
                ->with('update_error', 'Update failed while starting the updater script.')
                ->with('update_logs', $logs);
        }

        $this->appUpdateService->clearStatusCache();

        return redirect()
            ->route('admin.update.index')
            ->with('update_success', 'Latest version installed successfully.')
            ->with('update_logs', $logs);
    }

    private function updateCommand(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return [
                'cmd.exe',
                '/d',
                '/c',
                base_path('scripts/apply-latest-update.cmd'),
                '-Branch',
                (string) config('services.app_updates.branch', 'master'),
            ];
        }

        return [
            'pwsh',
            '-NoProfile',
            '-ExecutionPolicy',
            'Bypass',
            '-File',
            base_path('scripts/apply-latest-update.ps1'),
            '-Branch',
            (string) config('services.app_updates.branch', 'master'),
        ];
    }

    private function displayCommand(array $command): string
    {
        return collect($command)
            ->map(fn (string $part): string => str_contains($part, base_path())
                ? 'scripts/' . basename($part)
                : $part)
            ->implode(' ');
    }

    private function localChangeLines(): array
    {
        try {
            $result = Process::path(base_path())
                ->timeout(10)
                ->run(['git', 'status', '--short', '--', '.']);

            if ($result->failed()) {
                return [];
            }

            $lines = preg_split('/\R/', trim($result->output())) ?: [];

            return array_values(array_filter(
                array_map(fn (string $line): string => trim($line), $lines),
                fn (string $line): bool => $line !== ''
            ));
        } catch (Throwable) {
            return [];
        }
    }

    private function failureSummary(string $output): string
    {
        $lines = preg_split('/\R/', trim($output)) ?: [];
        $lines = array_values(array_filter($lines, fn (string $line): bool => trim($line) !== ''));

        if ($lines === []) {
            return 'Open Update Logs below for details.';
        }

        $errorLines = array_values(array_filter($lines, fn (string $line): bool => str_starts_with(trim($line), 'ERROR:')));
        $lastLine = trim((string) ($errorLines !== [] ? end($errorLines) : end($lines)));

        return 'Last error: ' . $lastLine;
    }
}
