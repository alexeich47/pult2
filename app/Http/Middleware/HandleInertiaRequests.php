<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->all(),
                    // The Employee record bound to this User — used to default author/assignee/owner
                    // selectors to the current user. Prefer explicit FK (users.employee_id),
                    // fall back to matching by email for users that haven't been linked yet.
                    'employee_id' => $user->employee_id
                        ?? Employee::query()->where('email', $user->email)->value('id'),
                ] : null,
            ],
            'units' => fn () => $request->user()
                ? Unit::orderBy('sort_order')
                    ->get(['id', 'name', 'color', 'unit_type', 'parent_id'])
                : [],
            'activeUnitId' => fn () => session('active_unit_id'),
            'locale' => $locale,
            'supportedLocales' => PultEnums::supportedLocales(),
            'translations' => fn () => Lang::get('pult', [], $locale),
            'flash' => [
                'success' => fn () => $request->session()->get('flash.success'),
                'error' => fn () => $request->session()->get('flash.error'),
            ],
        ];
    }
}
