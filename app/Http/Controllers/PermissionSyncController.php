<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionSyncController extends Controller
{
    /**
     * Route names are read straight out of routes/web.php (not the full route
     * collection) so this page only ever reflects that one file, per how the
     * app's permissions are currently seeded/organized.
     */
    protected function routeNamesFromWebFile(): array
    {
        $contents = file_get_contents(base_path('routes/web.php'));

        preg_match_all('/->name\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $contents, $matches);

        return collect($matches[1])->unique()->values()->all();
    }

    protected function suggestGroupName(string $routeName, Collection $existingPermissions): string
    {
        $segments = explode('.', $routeName);

        // Try progressively shorter prefixes ("committee.input.review.session", then
        // "committee.input.review", ...) so a route is matched against the closest
        // existing family of permissions rather than just its broad first segment.
        for ($len = count($segments) - 1; $len >= 1; $len--) {
            $prefix = implode('.', array_slice($segments, 0, $len));

            $related = $existingPermissions->filter(function ($permission) use ($prefix) {
                return $permission->name === $prefix || str_starts_with($permission->name, $prefix . '.');
            });

            if ($related->isNotEmpty()) {
                return $related->groupBy('group_name')
                    ->sortByDesc(fn ($group) => $group->count())
                    ->keys()
                    ->first();
            }
        }

        if (count($segments) > 1) {
            array_pop($segments);
            return implode('_', $segments);
        }

        return $segments[0];
    }

    public function Index()
    {
        abort_unless(Auth::user()->can('permission_sync.view') || Auth::user()->hasRole('SuperAdmin'), 403);

        $routeNames = $this->routeNamesFromWebFile();
        $existingPermissions = Permission::all(['name', 'group_name']);
        $existingNames = $existingPermissions->pluck('name');

        $missingRoutes = collect($routeNames)
            ->diff($existingNames)
            ->values()
            ->map(fn ($name) => [
                'name' => $name,
                'suggested_group' => $this->suggestGroupName($name, $existingPermissions),
            ]);

        return view('system_setting.permission_sync.index', [
            'missingRoutes' => $missingRoutes,
            'totalRoutes' => count($routeNames),
            'existingCount' => count($routeNames) - $missingRoutes->count(),
        ]);
    }

    public function Sync(Request $request)
    {
        abort_unless(Auth::user()->can('permission_sync.sync') || Auth::user()->hasRole('SuperAdmin'), 403);

        $rows = collect($request->input('rows', []));
        $quickSaveName = $request->input('quick_save_name');

        $toProcess = $quickSaveName
            ? $rows->filter(fn ($row) => ($row['name'] ?? null) === $quickSaveName)
            : $rows->filter(fn ($row) => ! empty($row['selected']));

        // Only allow creating permissions for routes that are genuinely missing right now -
        // recomputed server-side so posted form fields can't be used to create arbitrary permission names.
        $allowedMissing = collect($this->routeNamesFromWebFile())
            ->diff(Permission::pluck('name'))
            ->flip();

        $created = 0;

        foreach ($toProcess as $row) {
            $name = trim($row['name'] ?? '');
            $group = trim($row['group_name'] ?? '');

            if ($name === '' || ! $allowedMissing->has($name)) {
                continue;
            }

            if ($group === '' || ! preg_match('/^[a-z0-9_]+$/i', $group)) {
                $group = 'general';
            }

            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group_name' => $group]
            );

            $created++;
        }

        $notification = $created > 0
            ? ['message' => "{$created} permission(s) added.", 'alert-type' => 'success']
            : ['message' => 'No permissions were added. Select at least one route.', 'alert-type' => 'warning'];

        return redirect()->route('permission_sync.index')->with($notification);
    }
}
