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

        $command = $this->updateCommand();
        $logs = [[
            'label' => 'Download latest code and run update tasks',
            'command' => basename((string) $command[0]) . ' scripts/' . (PHP_OS_FAMILY === 'Windows' ? 'apply-latest-update.cmd' : 'apply-latest-update.ps1'),
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
                return redirect()
                    ->route('admin.update.index')
                    ->with('update_error', 'Update failed while downloading or applying the latest code.')
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
                'cmd',
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
}
