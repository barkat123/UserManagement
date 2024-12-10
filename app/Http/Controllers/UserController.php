<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
   // Show all users
   public function index()
   {
       $users = User::with('role')->get();
       return response()->json($users);
   }

   // Store new user
   public function store(Request $request)
   {
       $validator = Validator::make($request->all(), [
           'name' => 'required|string|max:255',
           'email' => 'required|email|unique:users,email',
           'phone' => 'required|regex:/^\+91[0-9]{10}$/',  // Indian phone validation
           'description' => 'nullable|string',
           'role_id' => 'required|exists:roles,id',
           'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
       ]);

       if ($validator->fails()) {
           return response()->json(['errors' => $validator->errors()], 422);
       }

       $data = $request->all();
       
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $imagePath;
        }

        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('images/profile'), $imageName);
            $data['profile_image'] = 'images/profile/'.$imageName;
        } 

       $user = User::create($data);

       return response()->json(['message' => 'User created successfully', 'user' => $user]);
   }

   // Get all roles
   public function getRoles()
   {
       $roles = Role::all();
       return response()->json($roles);
   }

}
