<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{AssignRoute, StaffRoute, Profile, StaffMember, Notification, User};
use Illuminate\Http\Request;
use App\Http\Requests\StaffMemberRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use File;
use DB;
use Hash;
use Illuminate\Validation\Rule;

class StaffMembersController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:staffmembers-list|staffmembers-create|staffmembers-edit|staffmembers-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:staffmembers-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:staffmembers-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:staffmembers-delete', ['only' => ['destroy']]);
        $this->middleware('permission:staffmembers-list', ['only' => ['show']]);
    }

    public function index()
    {
        $staffs = User::whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        })->orderBy('created_at', 'DESC')->withCount('staffJobs')->get();
        //        return $staffs;
        return view('staffmembers.index', ['staffs' => $staffs]);
    }

    public function create()
    {
        return view('staffmembers.create');
    }

    public function store(StaffMemberRequest $request)
    {
        //        return $request->all();
        $validatedData = $request->validate([
            //            'image' => 'required|mimes:jpeg,jpg,png|max:5120',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->withoutTrashed(),
            ],
            'address' => 'nullable|string|max:500',
            'hiring_date' => 'nullable',
            'training_rate' => 'nullable|numeric|min:0',
            'normal_rate' => 'nullable|numeric|min:0',
            'password' => [
                'required',
                'string',
                'min:8',
            ],
            'confirm_password' => 'required|same:password',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $safeName = 'users/no_avatar.jpg';
        if ($request->hasFile('image')) {
            $safeName = $this->storeImage('users', $request->image);
        }

        Profile::create([
            'user_id' => $user->id,
            'pic' => $safeName,
            'address' => $validatedData['address'],
            'hiring_date' => isset($validatedData['hiring_date']) ? \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['hiring_date'])->format('m-d-y') : null,
            'training_rate' => $validatedData['training_rate'] ?? null,
            'normal_rate' => $validatedData['normal_rate'] ?? null,
            'employment_type' => $request->boolean('is_subcontractor') ? 'subcontractor' : 'employee',
            'plain_password' => $validatedData['password'],
        ]);

        $message = "Account created for " . $user->name . "\n";
        $message .= "Email: " . $user->email . "\n";
        $message .= "Hiring Date: " . ($validatedData['hiring_date'] ?? 'N/A');

        Notification::create([
            'user_id' => $user->id,
            'action_id' => $user->id,
            'title' => $user->name . ' - Account Created',
            'message' => $message,
            'type' => 'account_created',
        ]);

        $user->assignRole(['staff']);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $validatedData['password'],
            'subject' => 'Paul (Cleaning Window)!',
            'message' => "Paul (Cleaning Window)! Your Account Created!.",
            'url' => env('APP_URL'),
            'detail' => 'Thank you for joining our platform.',
            'site_url' => env('APP_URL'),
        ];

        Mail::send('website.email_templates.creating_staff_template', ['data' => $data], function ($message) use ($data) {
            $message->to($data['email'], $data['name'])->cc('paul@paulswindowcleaning.org', 'Admin')->subject($data['subject']);
        });

        return redirect()->route('staffmembers.index')->with(['title' => 'Done', 'message' => 'Staff Created Successfully', 'type' => 'success',]);
    }

    public function show($id, Request $request)
    {
        $staff = User::whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        })->with('profile')->withCount('staffJobs')->findOrFail($id);

        $route = StaffRoute::get();
        $staffRoute = AssignRoute::where('staff_id', $id)->get();
        $allMonths = collect([
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ]);

        $year = now()->year;
        $months = $allMonths->map(function ($month) use ($year) {
            return "$month $year";
        });
        $selectedMonth = $request->input('month', now()->format('F Y'));
        return view('staffmembers.show', ['staff' => $staff, 'route' => $route, 'staffRoute' => $staffRoute, 'months' => $months, 'selectedMonth' => $selectedMonth]);
    }

    public function edit($id)
    {
        $staff = User::with('profile')->findOrFail($id);
        return view('staffmembers.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'hiring_date' => 'nullable',
            'training_rate' => 'nullable|numeric|min:0',
            'normal_rate' => 'nullable|numeric|min:0',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8';
            $rules['confirm_password'] = 'same:password';
        }

        $validatedData = $request->validate($rules);

        $user->name = $validatedData['name'];

        $passwordChanged = false;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $passwordChanged = true;
        }
        $user->save();

        $profile = $user->profile;
        if ($profile) {
            $profile->address = $validatedData['address'];
            // Convert d/m/Y format to Y-m-d for database
            $hiringDate = isset($validatedData['hiring_date']) ? \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['hiring_date'])->format('m-d-y') : $profile->hiring_date;
            $profile->hiring_date = $hiringDate;
            $profile->training_rate = $validatedData['training_rate'] ?? $profile->training_rate;
            $profile->normal_rate = $validatedData['normal_rate'] ?? $profile->normal_rate;
            $profile->employment_type = $request->boolean('is_subcontractor') ? 'subcontractor' : 'employee';

            if ($request->filled('password')) {
                $profile->plain_password = $request->password;
            }

            if ($request->hasFile('image')) {
                if ($profile->pic && $profile->pic !== 'users/no_avatar.jpg') {
                    $this->deleteImage('users', $profile->pic);
                }
                $profile->pic = $this->storeImage('users', $request->image);
            }
            $profile->save();
        }

        $message = "Account updated for " . $user->name . "\n";
        $message .= "Email: " . $user->email;
        if ($passwordChanged) {
            $message .= "\nPassword changed - Check email";
        }

        Notification::create([
            'user_id' => $user->id,
            'action_id' => $user->id,
            'title' => $user->name . ' - Account Updated',
            'message' => $message,
            'type' => 'account_updated',
        ]);

        // Send email when password is changed
        if ($passwordChanged) {
            $data = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $request->password,
                'subject' => 'Paul (Cleaning Window)!',
                'message' => "Paul (Cleaning Window)! Your Password Has Been Updated!.",
                'url' => env('APP_URL'),
                'detail' => 'Your account password has been updated. Please use the new password to login.',
                'site_url' => env('APP_URL'),
            ];

            Mail::send('website.email_templates.creating_staff_template', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'], $data['name'])->cc('paul@paulswindowcleaning.org', 'Admin')->subject($data['subject']);
            });
        }

        return redirect()->route('staffmembers.index')->with(['title' => 'Done', 'message' => 'Staff Updated Successfully', 'type' => 'success']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $profile = $user->profile;

        if ($profile && $profile->pic && $profile->pic !== 'users/no_avatar.jpg') {
            $this->deleteImage('users', $profile->pic);
        }

        if ($profile) {
            $profile->delete();
        }
        $timestamp = now()->timestamp;
        $updatedEmail = $user->email . $timestamp;

        $user->update([
            'email' => $updatedEmail,
            'deleted_at' => now(),
        ]);


        return redirect()->route('staffmembers.index')->with(['title' => 'Done', 'message' => 'Staff Deleted Successfully', 'type' => 'success',]);
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status == 1 ? 0 : 1;
        $user->save();

        $statusText = $user->status == 1 ? 'Activated' : 'Deactivated';

        if ($user->status == 1) {
            $message = "Account activated for " . $user->name . "\n";
            $message .= "Email: " . $user->email . "\n";
            $message .= "Status: Active";

            Notification::create([
                'user_id' => $user->id,
                'action_id' => $user->id,
                'title' => $user->name . ' - Account Activated',
                'message' => $message,
                'type' => 'account_activated',
            ]);
        } else {
            $message = "Account deactivated for " . $user->name . "\n";
            $message .= "Email: " . $user->email . "\n";
            $message .= "Status: Inactive";

            Notification::create([
                'user_id' => $user->id,
                'action_id' => $user->id,
                'title' => $user->name . ' - Account Deactivated',
                'message' => $message,
                'type' => 'account_deactivated',
            ]);
        }

        return redirect()->back()->with(['title' => 'Done', 'message' => 'Staff ' . $statusText . ' Successfully', 'type' => 'success']);
    }
}
