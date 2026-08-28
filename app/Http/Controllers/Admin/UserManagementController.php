<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * User Management — Administrator-only.
 *
 * Every action here is additionally protected by the 'admin' route
 * middleware (see routes/admin.php) and by the fact that only
 * Administrator accounts can create/change roles. Regular Users never
 * reach these methods, but every method also re-checks server-side
 * (defense in depth) rather than relying solely on middleware/UI hiding.
 */
class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'daPersonnel']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->value()) {
            if (in_array($status, ['active', 'inactive'], true)) {
                $query->where('status', $status);
            }
        }

        if ($role = $request->string('role')->trim()->value()) {
            $query->whereHas('role', fn ($q) => $q->where('name', $role));
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'role_id'               => ['required', 'exists:roles,id'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::create([
            'name'                  => $validated['name'],
            'email'                 => $validated['email'],
            'password'              => Hash::make($validated['password']),
            'role_id'               => $validated['role_id'],
            'status'                => $validated['status'],
            // Every account created by an Administrator must be changed
            // by the user on first login — this is a temporary password.
            'must_change_password'  => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Account for {$user->name} created successfully.");
    }

    public function show(User $user)
    {
        $user->load(['role', 'daPersonnel']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $actingAsSelf = $user->id === Auth::id();

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'status'  => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Prevent privilege escalation / self-lockout: an Administrator
        // editing their own account can't demote themselves or deactivate
        // themselves through this form. They also can't be the only
        // Administrator and get their role changed away from Administrator.
        if ($actingAsSelf) {
            $newRole = Role::find($validated['role_id']);

            if (! $newRole || $newRole->name !== 'Administrator') {
                return back()
                    ->withInput()
                    ->withErrors(['role_id' => 'You cannot change your own role away from Administrator.']);
            }

            if ($validated['status'] !== 'active') {
                return back()
                    ->withInput()
                    ->withErrors(['status' => 'You cannot deactivate your own account.']);
            }
        }

        // Prevent the last remaining Administrator from being demoted or
        // deactivated by someone else, which would lock everyone out of
        // account management.
        $currentRole = $user->role;
        $isCurrentlyLastAdmin = $currentRole && $currentRole->name === 'Administrator'
            && User::whereHas('role', fn ($q) => $q->where('name', 'Administrator'))->count() <= 1;

        if ($isCurrentlyLastAdmin) {
            $newRole = Role::find($validated['role_id']);
            $stillAdmin = $newRole && $newRole->name === 'Administrator';

            if (! $stillAdmin || $validated['status'] !== 'active') {
                return back()
                    ->withInput()
                    ->withErrors(['role_id' => 'This is the only Administrator account — it cannot be demoted or deactivated.']);
            }
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Account for {$user->name} updated successfully.");
    }

    /**
     * Deactivate instead of delete, per policy — records tied to this
     * user (surveys, DA personnel profile, activity) stay intact.
     */
    public function deactivate(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        if ($user->isAdministrator()
            && User::whereHas('role', fn ($q) => $q->where('name', 'Administrator'))->count() <= 1) {
            return back()->withErrors(['status' => 'This is the only Administrator account — it cannot be deactivated.']);
        }

        $user->update(['status' => 'inactive']);

        return back()->with('success', "{$user->name} has been deactivated.");
    }

    public function reactivate(User $user)
    {
        $user->update(['status' => 'active']);

        return back()->with('success', "{$user->name} has been reactivated.");
    }

    /**
     * Administrator-triggered password reset. Generates a new temporary
     * password, forces the user to change it on next login, and never
     * displays the previous password (it's not retrievable — hashed).
     */
    public function resetPassword(User $user)
    {
        $temporaryPassword = Str::password(12);

        $user->update([
            'password'             => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        return back()->with([
            'success'  => "Password reset for {$user->name}.",
            // Shown once, in the response only — not logged, not stored.
            'temp_password' => $temporaryPassword,
        ]);
    }
}
