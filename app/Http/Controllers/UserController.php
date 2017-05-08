<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
	/**
	 * return user paginated
	 * @return \Illuminate\Http\Response
	 */
    public function index()
	{
	    $result = User::latest()->paginate();
	    return view('user.index', compact('result'));
	}

	/**
	 * return user form
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
	    $roles = Role::pluck('name', 'id');
	    return view('user.new', compact('roles'));
	}

	/**
	 * store user
	 * @param  Request $request [description]
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
	    $this->validate($request, [
	        'name' => 'bail|required|min:2',
	        'email' => 'required|email|unique:users',
	        'password' => 'required|min:6',
	        'roles' => 'required|min:1'
	    ]);

	    // hash password
	    $request->merge(['password' => bcrypt($request->get('password'))]);

	    // Create the user
	    if ( $user = User::create($request->except('roles', 'permissions')) ) {
	        $this->syncPermissions($request, $user);
	        flash('User has been created.');
	    } else {
	        flash()->error('Unable to create user.');
	    }

	    return redirect()->route('users.index');
	}

	/**
	 * edit user form
	 * @param  Int $id id of the user
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
	    $user = User::find($id);
	    $roles = Role::pluck('name', 'id');
	    $permissions = Permission::all('name', 'id');

	    return view('user.edit', compact('user', 'roles', 'permissions'));
	}

	/**
	 * update user information
	 * @param  Request $request
	 * @param  Int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
	    $this->validate($request, [
	        'name' => 'bail|required|min:2',
	        'email' => 'required|email|unique:users,email,' . $id,
	        'roles' => 'required|min:1'
	    ]);

	    // Get the user
	    $user = User::findOrFail($id);

	    // Update user
	    $user->fill($request->except('roles', 'permissions', 'password'));

	    // check for password change
	    if($request->get('password')) {
	        $user->password = bcrypt($request->get('password'));
	    }

	    // Handle the user roles
	    $this->syncPermissions($request, $user);

	    $user->save();
	    flash()->success('User has been updated.');
	    return redirect()->route('users.index');
	}

	/**
	 * delete specific user
	 * @param  Int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
	    if ( Auth::user()->id == $id ) {
	        flash()->warning('Deletion of currently logged in user is not allowed :(')->important();
	        return redirect()->back();
	    }

	    if( User::findOrFail($id)->delete() ) {
	        flash()->success('User has been deleted');
	    } else {
	        flash()->success('User not deleted');
	    }

	    return redirect()->back();
	}

	/**
	 * synchronise permission of a user
	 * @param  Request $request 
	 * @param  User  $user    
	 * @return \Illuminate\Http\Response           
	 */
	private function syncPermissions(Request $request, $user)
	{
	    // Get the submitted roles
	    $roles = $request->get('roles', []);
	    $permissions = $request->get('permissions', []);

	    // Get the roles
	    $roles = Role::find($roles);

	    // check for current role changes
	    if( ! $user->hasAllRoles( $roles ) ) {
	        // reset all direct permissions for user
	        $user->permissions()->sync([]);
	    } else {
	        // handle permissions
	        $user->syncPermissions($permissions);
	    }

	    $user->syncRoles($roles);
	    return $user;
	}
}
