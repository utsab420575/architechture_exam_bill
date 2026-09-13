<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class GithubDeployController extends Controller
{
    protected string $logFile = 'github_deploy/last_pull.log';

    public function Index()
    {
        abort_unless(Auth::user()->can('github_deploy.view') || Auth::user()->hasRole('SuperAdmin'), 403);

        $repoPath = base_path();

        // Best-effort refresh of remote refs so the branch list / ahead-behind counts are current.
        try {
            Process::path($repoPath)->timeout(20)->run(['git', 'fetch', 'origin', '--prune']);
        } catch (\Throwable $e) {
            // Ignore network/fetch failures - the page still works off local refs.
        }

        $currentBranch = trim(Process::path($repoPath)->run(['git', 'rev-parse', '--abbrev-ref', 'HEAD'])->output());

        $isDirty = trim(Process::path($repoPath)->run(['git', 'status', '--porcelain'])->output()) !== '';

        $remoteUrl = trim(Process::path($repoPath)->run(['git', 'config', '--get', 'remote.origin.url'])->output());

        $branches = collect(explode("\n", trim(
                Process::path($repoPath)->run(['git', 'branch', '-r', '--format=%(refname:short)'])->output()
            )))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->reject(fn ($b) => str_ends_with($b, 'HEAD'))
            ->map(fn ($b) => str_starts_with($b, 'origin/') ? substr($b, 7) : $b)
            ->unique()
            ->sort()
            ->values();

        $ahead = null;
        $behind = null;
        try {
            $counts = trim(Process::path($repoPath)->run([
                'git', 'rev-list', '--left-right', '--count', "origin/{$currentBranch}...HEAD",
            ])->output());
            if ($counts !== '' && str_contains($counts, "\t")) {
                [$behind, $ahead] = explode("\t", $counts);
            }
        } catch (\Throwable $e) {
            // No upstream tracking branch, or origin/<branch> doesn't exist - leave as null.
        }

        $commits = collect(explode("\n", trim(
                Process::path($repoPath)->run([
                    'git', 'log', '-n', '20', '--pretty=format:%h|%an|%ad|%s', '--date=relative',
                ])->output()
            )))
            ->filter()
            ->map(function ($line) {
                [$hash, $author, $date, $message] = array_pad(explode('|', $line, 4), 4, '');
                return compact('hash', 'author', 'date', 'message');
            });

        $lastPullLog = Storage::disk('local')->exists($this->logFile)
            ? Storage::disk('local')->get($this->logFile)
            : null;

        return view('system_setting.github_deploy.index', compact(
            'currentBranch', 'isDirty', 'remoteUrl', 'branches', 'ahead', 'behind', 'commits', 'lastPullLog'
        ));
    }

    public function Pull(Request $request)
    {
        abort_unless(Auth::user()->can('github_deploy.pull') || Auth::user()->hasRole('SuperAdmin'), 403);

        $request->validate([
            'branch' => ['required', 'string', 'regex:/^[A-Za-z0-9_\-.\/]+$/'],
        ]);

        $repoPath = base_path();
        $branch = $request->branch;

        Process::path($repoPath)->timeout(30)->run(['git', 'fetch', 'origin', '--prune']);

        $availableBranches = collect(explode("\n", trim(
                Process::path($repoPath)->run(['git', 'branch', '-r', '--format=%(refname:short)'])->output()
            )))
            ->map(fn ($b) => trim($b))
            ->map(fn ($b) => str_starts_with($b, 'origin/') ? substr($b, 7) : $b);

        if (! $availableBranches->contains($branch)) {
            return redirect()->route('github_deploy.index')->with([
                'message' => "Branch '{$branch}' was not found on origin.",
                'alert-type' => 'error',
            ]);
        }

        $output = '';

        $checkout = Process::path($repoPath)->timeout(30)->run(['git', 'checkout', $branch]);
        $output .= "$ git checkout {$branch}\n" . $checkout->output() . $checkout->errorOutput() . "\n";

        $pull = Process::path($repoPath)->timeout(120)->run(['git', 'pull', 'origin', $branch]);
        $output .= "$ git pull origin {$branch}\n" . $pull->output() . $pull->errorOutput() . "\n";

        $success = $checkout->successful() && $pull->successful();

        $log = '[' . Carbon::now()->format('Y-m-d H:i:s') . '] Pulled by ' . Auth::user()->name . " (branch: {$branch})\n" . $output;
        Storage::disk('local')->put($this->logFile, $log);

        $notification = $success
            ? ['message' => "Successfully pulled '{$branch}'.", 'alert-type' => 'success']
            : ['message' => "Pull finished with errors on '{$branch}'. Check the log below.", 'alert-type' => 'warning'];

        return redirect()->route('github_deploy.index')->with($notification);
    }
}
