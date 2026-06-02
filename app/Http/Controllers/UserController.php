<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display active users.
     */
    public function index()
    {
        $users = User::with('role')->whereNull('deleted_at')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Display archived users.
     */
    public function archive()
    {
        $users = User::with('role')
            ->onlyTrashed()
            ->get();

        return view('users.archive', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $imagePath = null;

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $request->validate([
                'avatar' => 'image|max:2048|mimes:jpg,jpeg,png',
            ]);

            $avatar = $request->file('avatar');
            $host = $request->getSchemeAndHttpHost();
            $storage = Storage::disk('public');
            $filePath = $storage->putFile('avatars', new File($avatar), 'public');
            $imagePath = $host . '/storage/' . $filePath;
        }

        $request->validate([
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'civil_status' => 'required',
            'age' => 'required',
            'birthdate' => 'required',
            'present_address' => 'required',
            'gender' => 'required',
            'role_id' => 'required',
            'phone_number' => 'required',
            'license_number' => 'required',
        ]);

        User::create([
            'role_id' => $request->role_id,
            'avatar' => $imagePath,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'civil_status' => $request->civil_status,
            'age' => $request->age,
            'birthdate' => $request->birthdate,
            'present_address' => $request->present_address,
            'home_address' => $request->home_address,
            'phone_number' => $request->phone_number,
            'license_number' => $request->license_number,
        ]);

        return redirect()->back()->with('success', 'A user has been successfully added.');
    }

    /**
     * Show a specific user.
     */
    public function show(User $user)
    {
        return view('users.show')->with('user', $user);
    }

    /**
     * Edit user details.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Search active users.
     */
    public function search()
    {
        $data = request()->validate([
            'search' => 'required',
        ]);

        $users = User::where(function ($query) use ($data) {
            $query->where('first_name', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('middle_name', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('last_name', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('gender', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('phone_number', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('civil_status', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('home_address', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('present_address', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('age', 'LIKE', '%' . $data['search'] . '%')
                ->orWhere('birthdate', 'LIKE', '%' . $data['search'] . '%');
        })->paginate(20);

        return view('users.search', ['users' => $users]);
    }

    /**
     * Search archived users.
     */
    public function archive_search()
    {
        $data = request()->validate([
            'search' => 'required',
        ]);

        $users = User::onlyTrashed()
            ->where(function ($query) use ($data) {
                $query->where('first_name', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('middle_name', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('gender', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('phone_number', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('civil_status', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('home_address', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('present_address', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('age', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('birthdate', 'LIKE', '%' . $data['search'] . '%');
            })->paginate(20);

        return view('users.archive_search', ['users' => $users]);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'nullable|confirmed',
            'gender' => 'required',
            'civil_status' => 'required',
            'age' => 'required',
            'birthdate' => 'required',
            'present_address' => 'required',
            'role_id' => 'required',
            'phone_number' => 'required',
            'license_number' => 'required',
        ]);

        $user->fill($request->except(['password', 'avatar']));

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatar = $request->file('avatar');
            $host = $request->getSchemeAndHttpHost();
            $storage = Storage::disk('public');
            $filePath = $storage->putFile('avatars', new File($avatar), 'public');
            $user->avatar = $host . '/storage/' . $filePath;
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'User has been updated successfully.');
    }

    /**
     * Soft delete (archive) user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // prevent deleting the admin itself
        if ($user->role->name === 'Administrator') {
            return redirect()->back()->with('error', 'You cannot archive an Administrator account.');
        }

        $user->delete(); // soft delete
        return redirect()->back()->with('success', 'User archived successfully.');
    }

    /**
     * Disable permanent delete.
     */
    public function deleteUser($id)
    {
        return redirect()->back()->with('error', 'Permanent deletion is disabled. Please contact the system administrator.');
    }

    /**
     * Restore archived user.
     */
    public function restoreUser($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'Unable to restore user.');
        }

        $user->restore();
        return redirect()->back()->with('success', 'User restored successfully.');
    }
}
