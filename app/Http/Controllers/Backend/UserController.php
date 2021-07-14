<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// Models
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller {
    const MODULE = 'user';

    public function index(Request $request) {
        $this->authorize(mapPermission(self::MODULE));

        if ($request->has('_token')) :
            $users = User::getUserByKeyword(request('keyword'))->ignoreSuperAdmin()->adminOnly()->get();
        else :
            $users = User::ignoreSuperAdmin()->adminOnly()->limit(50)->get();
        endif;

        if ($users->count() != 0) :
            foreach ($users as $key => $item) :
                $updated_at = Carbon::parse(date('Y-m-d H:i:s', strtotime("$item->updated_at, + 543 years")))
                ->locale('th_TH')->isoFormat('D MMM g HH:mm:ss');

                $lists[$key]['count'] = $key + 1;
                $lists[$key]['id'] = $item->id;
                $lists[$key]['name'] = $item->name;
                $lists[$key]['email'] = $item->email;
                $lists[$key]['role'] = $item->getRoleNames()->implode(', ');
                $lists[$key]['updated_at'] = $updated_at;
                $lists[$key]['updated_by'] = $item->user_updates == null ? '-' : $item->user_updates->name;
                // $lists[$key][''] = $item->;
            endforeach;
        else :
            $lists = [];
        endif;

        $compacts = ['lists'];
        return view('backend.user.index', compact($compacts));
    }

  public function create() {
    $roles = Role::all();
    $user = new User;

    return view('backend.user.create', compact(['user', 'roles']));
  }

  public function store(Request $request) {
    $this->authorize(mapPermission(self::MODULE));

    $data = request()->validate([
      'first_name' => ['required', 'string', 'max:255'],
      'last_name' => '',
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password' => ['required', 'string', 'min:6', 'confirmed'],
      'department_id' => ''
    ]);

    $role_id = request()->validate([
      'role_id' => ['required', 'array'],
      'role_id.*' => ['required']
    ]);

    $data['password'] = Hash::make($data['password']);
    $data['created_by'] = Auth::id();
    $data['updated_by'] = Auth::id();
    $user = User::create($data);
    $user->syncRoles(request('role_id'));

    return redirect(route('backend.user.index'));
  }

  public function edit(User $user) {
    $this->authorize(mapPermission(self::MODULE));

    $roles = Role::all();

    return view('backend.user.update', compact(['user', 'roles']));
  }

  public function update(Request $request, User $user) {
    $this->authorize(mapPermission(self::MODULE));

    if ($request->filled('password')) :
      $data = request()->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => '',
        'password' => ['string', 'min:6', 'confirmed'],
        'active' => '',
      ]);

      $data['password'] = Hash::make($data['password']);
    else :
      $data = request()->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => '',
        'active' => '',
        'department_id' => '',
      ]);
    endif;

    $role_id = request()->validate([
      'role_id' => ['required', 'array'],
      'role_id.*' => ['required'],
    ]);

    $data['updated_by'] = Auth::id();
    $user->update($data);
    $user->syncRoles(request('role_id'));

    return redirect(route('backend.user.index'));
  }

    public function destroy(User $user) {
        dd($user);
        $this->authorize(mapPermission(self::MODULE));

        $user->delete();

        return redirect(route('backend.user.index'));
    }
}
