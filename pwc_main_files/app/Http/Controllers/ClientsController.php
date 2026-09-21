<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{
    AssignRoute,
    AssignWeek,
    Client,
    ClientImage,
    ClientPriceList,
    ClientRoute,
    ClientTime,
    Notification,
    Profile,
    StaffRoute,
    UnavailDay,
    User
};
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, DB};
use Carbon\Carbon;
use Hash;
use Illuminate\Support\Facades\{Auth, Log};

class ClientsController extends Controller
{
    protected $quickBooksService;

    function __construct(QuickBooksService $quickBooksService)
    {
        $this->quickBooksService = $quickBooksService;
        $this->middleware('permission:clients-list|clients-create|clients-edit|clients-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:clients-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:clients-create|clients-edit', ['only' => ['edit', 'update']]);
        $this->middleware(function ($request, $next) {
            if (auth()->user()->can('clients-delete') || auth()->user()->hasRole('staff')) {
                return $next($request);
            }
            abort(403, 'This action is unauthorized.');
        })->only(['destroy']);
        $this->middleware('permission:clients-list', ['only' => ['show']]);
    }

    public function index()
    {
        // Performance: eager-load everything the list view touches (avoids N+1 queries)
        // and fetch only MAX(updated_at) of schedules instead of loading every schedule row.
        $clients = Client::where('is_child', false)
            ->with([
                'user.roles',
                'user.profile',
                'profile',
                'staff.profile',
                'parentClient',
                'clientRoute',
                'clientRouteStaff',
                'childClients' => function ($query) {
                    $query->with([
                        'clientRouteStaff',
                        'clientRoute',
                        'user.profile',
                        'staff.profile',
                        'parentClient',
                    ])->orderBy('updated_at', 'desc');
                },
            ])
            ->withMax('clientSchedule', 'updated_at')
            ->get()
            ->sortByDesc(function ($client) {
                $clientUpdated = $client->updated_at ?? $client->created_at;
                $scheduleUpdated = $client->client_schedule_max_updated_at
                    ? Carbon::parse($client->client_schedule_max_updated_at)
                    : null;
                $branchUpdated = $client->childClients->max('updated_at');
                return collect([$clientUpdated, $scheduleUpdated, $branchUpdated])
                    ->filter()
                    ->max();
            })->values();
        $routes = StaffRoute::get();

        return response()
            ->view('clients.index', ['clients' => $clients, 'routes' => $routes])->header('Cache-Control', 'no-cache, no-store, must-revalidate')->header('Pragma', 'no-cache')->header('Expires', '0');
    }

    public function create()
    {
        $route = StaffRoute::where('status', 1)->orderBy('name')->get();
        return view('clients.create', ['route' => $route]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $status = auth()->user()->hasRole('admin') ? '1' : '0';
            $staff_id = auth()->user()->hasRole('staff') ? auth()->id() : null;
            $client = null;
            $parentClient = null;
            $parentClientId = null;
            $createdClients = [];

            foreach ($request->input('name') as $index => $clientName) {
                if ($clientName == null) {
                    continue;
                }

                $phones = is_array($request->input('phone')[$index]) ? $request->input('phone')[$index] : [];
                $cleanPhones = array_map(function ($phoneNumber) {
                    return preg_replace('/\D/', '', $phoneNumber);
                }, $phones);

                // Get arrays for this client index
                $contactNames = $request->input('contact_name')[$index] ?? [];
                $positions = $request->input('positions')[$index] ?? [];
                $emails = $request->input('email')[$index] ?? [];
                $notes = $request->input('note')[$index] ?? [];

                $start_date = $this->parseDate($request->input('start_date')[$index] ?? null);
                $start_date_second = $this->parseDate($request->input('start_date_second')[$index] ?? null);

                $endDateFormatted = null;
                if (isset($request->input('service_frequency')[$index]) && $request->input('service_frequency')[$index] == 'normalWeek' && $start_date) {
                    $startDate = Carbon::createFromFormat('d/m/Y', $start_date);
                    $endDate = $startDate->copy()->addWeeks(3)->startOfWeek(Carbon::MONDAY)->endOfWeek(Carbon::SUNDAY);
                    $endDateFormatted = $endDate->format('d/m/Y');
                }

                $frontImagePath = $this->handleImageUpload($request->file('front_image'), $index);
                $backImagePath = $this->handleImageUpload($request->file('back_image'), $index);

                $client = Client::create([
                    'user_id' => null, // No longer using users table for clients
                    'parent_id' => ($index == 0) ? null : $parentClientId,
                    'staff_id' => $staff_id ?? null,
                    'name' => $clientName,
                    'client_type' => $request->input('client_type')[$index] ?? null,
                    'house_no' => $request->input('house_no')[$index],
                    'address' => $request->input('street')[$index],
                    'city' => $request->input('city')[$index] ?? '',
                    'state' => $request->input('state')[$index] ?? '',
                    'postal' => $request->input('postal')[$index] ?? '',
                    'contact_name' => $request->input('contact_name')[$index][0] ?? null,
                    'contact_email' => $request->input('email')[$index][0] ?? null,
                    'contact_phone' => $cleanPhones[0] ?? null,
                    'position' => $positions[0] ?? null,
                    'payment_type' => $request->input('payment_type')[$index] ?? 'cash',
                    'commission_percentage' => $request->input('commission_percentage')[$index] ?? null,
                    'description' => $request->input('note')[$index][0] ?? null,
                    'service_frequency' => $request->input('service_frequency')[$index] ?? null,
                    'start_date' => $start_date,
                    'second_start_date' => $start_date_second ?? null,
                    'end_date' => $endDateFormatted ?? null,
                    'front_image' => $frontImagePath,
                    'back_image' => $backImagePath,
                    'additional_note' => $request->input('additional_note')[$index] ?? ($request->input('note')[$index][0] ?? null),
                    'status' => $status ?? 1,
                    'is_child' => ($index == 0) ? 0 : 1,
                ]);

                $createdClients[] = $client;

                if ($index == 0) {
                    $parentClient = $client;
                }
                $invoiceEmails = [];
                if (isset($request->input('invoice_email_parent')[$index])) {
                    $invoiceEmailIndices = $request->input('invoice_email_parent')[$index] ?? [];
                    foreach ($invoiceEmailIndices as $emailIndex) {
                        if (isset($emails[$emailIndex])) {
                            $invoiceEmails[] = $emails[$emailIndex];
                        }
                    }
                } else {
                    $invoiceEmails = $emails;
                }

                Profile::create([
                    'client_id' => $client->id,
                    'user_id' => null, // No user_id for clients
                    'phone' => $cleanPhones[0] ?? null,
                    'address' => $request->input('house_no')[$index] ?? null,
                    'street_number' => $request->input('street')[$index] ?? null,
                    'city' => $request->input('city')[$index] ?? null,
                    'state' => $request->input('state')[$index] ?? null,
                    'postal' => $request->input('postal')[$index] ?? null,
                    // Save ALL data including first item
                    'additional_names' => json_encode(is_array($contactNames) ? $contactNames : []),
                    'additional_phones' => json_encode($cleanPhones),
                    'additional_positions' => json_encode(is_array($positions) ? $positions : []),
                    'additional_emails' => json_encode(is_array($emails) ? $emails : []),
                    'additional_notes' => json_encode(is_array($notes) ? $notes : []),
                    'invoice_email' => json_encode($invoiceEmails),
                ]);

                if ($status == 1) {
                    Notification::create([
                        'user_id' => 2,
                        'action_id' => 2,
                        'title' => $clientName . ' Client has been created',
                        'message' => $clientName  . ' A new Client has been created.',
                        'type' => 'new_client',
                    ]);
                } else {
                    Notification::create([
                        'user_id' => 2,
                        'action_id' => 2,
                        'title' => Auth::user()->name . ' has created a new Client',
                        'message' => 'A new Client has been created and is pending approval.',
                        'type' => 'new_client_pending',
                    ]);
                }

                if ($index == 0) {
                    $parentClientId = $client->id;
                }
                $this->saveClientAdditionalData($client, $request, $index);
            }
            if (!$client) {
                throw new \Exception('No client was created');
            }

            DB::commit();
            foreach ($createdClients as $createdClient) {
                $this->syncClientToQuickBooks($createdClient);
            }
            if (auth()->user()->hasRole('staff')) {
                return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => 'Client created successfully. Please wait for the admin to accept your client request.', 'type' => 'success']);
            }
            $action = $request->input('action', 'create');
            if ($action === 'create_and_schedule') {
                $clientForSchedule = $parentClient ?? $client;
                $hasRoute = !empty($request->input('route_id')[0]);
                if (!$hasRoute) {
                    return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => 'Client Created Successfully. Please assign a route to schedule this client.', 'type' => 'success']);
                }
                return redirect()->to(url('client-schedule/' . $clientForSchedule->id . '?first-time=true'))->with(['title' => 'Done', 'message' => 'Client Created Successfully', 'type' => 'success']);
            }
            return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => 'Client Created Successfully', 'type' => 'success']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $exception->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);
            $status = auth()->user()->hasRole('admin') ? '1' : '0';

            $start_date = $this->parseDate($request->input('start_date'));
            $start_date_second = $this->parseDate($request->input('start_date_second'));

            $endDateFormatted = null;
            if ($request->service_frequency == 'normalWeek' && $start_date) {
                $startDate = Carbon::createFromFormat('d/m/Y', $start_date);
                $endDate = $startDate->copy()->addWeeks(3);
                $endDate = $endDate->startOfWeek(Carbon::MONDAY)->endOfWeek(Carbon::SUNDAY);
                $endDateFormatted = $endDate->format('d/m/Y');
            }

            $phones = is_array($request->phone) ? $request->phone : [];
            $cleanPhones = array_map(function ($phoneNumber) {
                return preg_replace('/\D/', '', $phoneNumber);
            }, $phones);

            $contactNames = is_array($request->contact_name) ? $request->contact_name : [];
            $positions = is_array($request->positions) ? $request->positions : [];
            $emails = is_array($request->email) ? $request->email : [];
            $notes = isset($request->note[0]) && is_array($request->note[0]) ? $request->note[0] : [];

            $invoiceEmails = [];
            if (isset($request->input('invoice_email_parent')[0]) && is_array($request->input('invoice_email_parent')[0])) {
                $invoiceEmailIndices = $request->input('invoice_email_parent')[0] ?? [];
                foreach ($invoiceEmailIndices as $emailIndex) {
                    if (isset($emails[$emailIndex])) {
                        $invoiceEmails[] = $emails[$emailIndex];
                    }
                }
            } else {
                $invoiceEmails = $emails;
            }

            if ($client->profile) {
                $profile = $client->profile;
                $profile->update([
                    'phone' => $cleanPhones[0] ?? $profile->phone,
                    'address' => is_array($request->address) ? $request->address[0] : $profile->address,
                    'city' => is_array($request->city) ? $request->city[0] : $profile->city,
                    'street_number' => $request->input('street_number', $profile->street_number) ?? null,
                    'state' => is_array($request->state) ? $request->state[0] : null,
                    'postal' => is_array($request->postal) ? $request->postal[0] : null,
                    'additional_emails' => json_encode($emails),
                    'additional_phones' => json_encode($cleanPhones),
                    'additional_names' => json_encode($contactNames),
                    'additional_positions' => json_encode($positions),
                    'additional_notes' => json_encode($notes),
                    'invoice_email' => json_encode($invoiceEmails),
                ]);
            } else {
                Profile::create([
                    'client_id' => $client->id,
                    'phone' => $cleanPhones[0] ?? null,
                    'address' => is_array($request->address) ? $request->address[0] : null,
                    'city' => is_array($request->city) ? $request->city[0] : null,
                    'street_number' => $request->input('street_number') ?? null,
                    'state' => is_array($request->state) ? $request->state[0] : null,
                    'postal' => is_array($request->postal) ? $request->postal[0] : null,
                    'additional_emails' => json_encode($emails),
                    'additional_phones' => json_encode($cleanPhones),
                    'additional_names' => json_encode($contactNames),
                    'additional_positions' => json_encode($positions),
                    'additional_notes' => json_encode($notes),
                    'invoice_email' => json_encode($invoiceEmails),
                ]);
            }

            if (str_contains($client->name, '-')) {
                $clientName = strtok($client->name, '-');
            } else {
                $clientName = $client->name;
            }

            if ($client->service_frequency != $request->service_frequency) {
                $client->clientSchedule()->delete();
            }

            $clientType = $request->input('client_type');
            if (is_array($clientType)) {
                $clientType = $clientType[1] ?? $clientType[0] ?? $client->client_type;
            }

            $paymentType = $request->input('payment_type');
            if (is_array($paymentType)) {
                $paymentType = $paymentType[1] ?? $paymentType[0] ?? $client->payment_type;
            }

            $client->update([
                'name' => $request->address_contact_name[0] ?? $request->input('name', $clientName) ?? null,
                'client_type' => $clientType ?? $client->client_type,
                'payment_type' => $paymentType ?? $client->payment_type,
                'address' => $request->address[0] ?? $client->address,
                'house_no' => $request->house_no[0] ?? $client->house_no,
                'city' => $request->city[0] ?? $client->city,
                'state' => $request->state[0] ?? $client->state,
                'postal' => $request->postal[0] ?? $client->postal,
                'contact_name' => $request->contact_name[0] ?? $client->contact_name,
                'contact_email' => $request->email[0] ?? $client->contact_email,
                'contact_phone' => $cleanPhones[0] ?? $client->contact_phone,
                'position' => $request->positions[0] ?? $client->position,
                'commission_percentage' => $request->input('commission_percentage', $client->commission_percentage),
                'description' => $request->note[0][0] ?? $client->description,
                'service_frequency' => $request->input('service_frequency', $client->service_frequency),
                'start_date' => $start_date ?? $client->start_date,
                'second_start_date' => $start_date_second ?? $client->start_date_second,
                'additional_note' => $request->input('additional_note', $client->additional_note),
            ]);

            if ($status == 1) {
                Notification::create([
                    'user_id' => 2,
                    'action_id' => 2,
                    'title' =>  $client->name . ' Client Updated',
                    'message' => $client->name . ' A client has been updated.',
                    'type' => 'client_updated',
                ]);
            } else {
                Notification::create([
                    'user_id' => 2,
                    'action_id' => 2,
                    'title' => Auth::user()->name . ' has updated a Client',
                    'message' => 'A client has been updated and is pending approval.',
                    'type' => 'client_updated_pending',
                ]);
            }

            if ($request->hasFile('front_image')) {
                $frontImagePath = $request->file('front_image')->store('client_document', 'website');
                $client->update(['front_image' => $frontImagePath]);
            }

            if ($request->hasFile('back_image')) {
                $backImagePath = $request->file('back_image')->store('client_document', 'website');
                $client->update(['back_image' => $backImagePath]);
            }

            $client->clientDay()->delete();
            $unavailDays = $request->input('unavail_day', []);
            if (is_array($unavailDays)) {
                foreach ($unavailDays as $day) {
                    if (!empty($day)) {
                        UnavailDay::create([
                            'client_id' => $client->id,
                            'day' => $day,
                        ]);
                    }
                }
            }

            $client->clientHour()->delete();
            $bestTimes = $request->input('best_time', []);
            if (is_array($bestTimes)) {
                foreach ($bestTimes as $time) {
                    if (!empty($time['start_hour']) && !empty($time['end_hour'])) {
                        ClientTime::create([
                            'client_id' => $client->id,
                            'start_hour' => $time['start_hour'],
                            'end_hour' => $time['end_hour'],
                        ]);
                    }
                }
            }

            $prices = $request->input('prices', []);
            $position = 1;
            $submittedNames = [];

            if (is_array($prices)) {
                foreach ($prices as $price) {
                    if (!empty($price['side'])) {
                        ClientPriceList::updateOrCreate(
                            ['client_id' => $client->id, 'name' => $price['side']],
                            ['value' => $price['number'] ?? 0, 'position' => $position]
                        );
                        $submittedNames[] = $price['side'];
                        $position++;
                    }
                }
            }

            ClientPriceList::where('client_id', $client->id)
                ->whereNotIn('name', $submittedNames)
                ->delete();

            if (!empty($request->input('route_id'))) {
                $client->clientRouteStaff()->forceDelete();
                ClientRoute::create([
                    'client_id' => $client->id,
                    'route_id' => $request->route_id[0],
                ]);
            }

            $clientImages = ClientImage::where('client_id', $client->id)->get();
            foreach ($clientImages as $image) {
                if (in_array($image->name, $request->existing_image ?? [])) {
                    continue;
                }
                Storage::disk('website')->delete($image->name);
                $image->forceDelete();
            }

            if (!empty($request->input('image'))) {
                foreach ($request->input('image') as $base64Image) {
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                    $filename = 'image_' . uniqid() . '.png';
                    $filePath = 'client_document/' . $filename;
                    Storage::disk('website')->put($filePath, $imageData);

                    ClientImage::create([
                        'client_id' => $client->id,
                        'name' => $filePath,
                    ]);
                }
            }
            $client->touch();

            DB::commit();

            // Update in QuickBooks (after successful database commit)
            $this->updateClientInQuickBooks($client);

            // Check which button was clicked
            $action = $request->input('action', 'update');

            // Debug: Log the action value
            \Log::info('Update Client Action: ' . $action);
            \Log::info('Client ID: ' . $client->id);

            // If "Update & Schedule" button was clicked
            if ($action === 'update_and_schedule') {
                \Log::info('Redirecting to schedule page for client: ' . $client->id);

                // Check if route was assigned
                $hasRoute = !empty($request->input('route_id')[0]);
                if (!$hasRoute) {
                    return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => 'Client Updated Successfully. Please assign a route to schedule this client.', 'type' => 'success']);
                }

                // Redirect to schedule page
                return redirect()->to(url('client-schedule/' . $client->id . '?first-time=true'))->with(['title' => 'Done', 'message' => 'Client Updated Successfully', 'type' => 'success']);
            }

            // Default "Update" button - redirect to clients list
            \Log::info('Redirecting to clients index');
            return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => 'Client Updated successfully.', 'type' => 'success']);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Client Update Error: " . $exception->getMessage());
            Log::error("File: " . $exception->getFile() . " Line: " . $exception->getLine());
            Log::error("Trace: " . $exception->getTraceAsString());
            return back()->with('error', 'Error updating client data. Please try again. ' . $exception->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);

            if (auth()->user()->hasRole('staff') && !auth()->user()->can('clients-delete')) {
                if ($client->staff_id != auth()->id() || $client->status != 0) {
                    abort(403, 'You can only delete your own potential clients.');
                }
            }

            $this->deleteClientData($client);
            $clientsToDeactivate = [$client];
            if ($client->parent_id == null) {
                $childClients = Client::where('parent_id', $client->id)->get();
                foreach ($childClients as $child) {
                    $this->deleteClientData($child);
                    $clientsToDeactivate[] = $child;
                    $child->delete();
                }
            }
            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' => $client->name . ' has deleted a Client',
                'message' => $client->name . ' A client has been deleted.',
                'type' => 'client_deleted',
            ]);

            $client->delete();
            DB::commit();
            foreach ($clientsToDeactivate as $clientToDeactivate) {
                $this->deactivateClientInQuickBooks($clientToDeactivate);
            }
            return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => 'Client and all related data deleted successfully.', 'type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting client: ' . $e->getMessage());
        }
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Try Y-m-d format first (HTML date input)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y');
            }
            // Try m/d/Y format (with slashes)
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
                return Carbon::createFromFormat('m/d/Y', $date)->format('d/m/Y');
            }
            // Try m-d-Y format (with dashes)
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $date)) {
                return Carbon::createFromFormat('m-d-Y', $date)->format('d/m/Y');
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function handleImageUpload($file, $index)
    {
        if ($file && isset($file[$index])) {
            return $file[$index]->store('client_document', 'website');
        }
        return null;
    }

    private function saveClientAdditionalData($client, $request, $index)
    {
        $unavailDays = $request->input('unavail_day')[$index] ?? [];
        foreach ($unavailDays as $day) {
            UnavailDay::create([
                'client_id' => $client->id,
                'day' => $day,
            ]);
        }

        if (!empty($request->input('best_time')[$index])) {
            foreach ($request->input('best_time')[$index] as $timeRow) {
                // Handle both 2-level and 3-level array structures
                if (is_array($timeRow) && isset($timeRow['start_hour']) && isset($timeRow['end_hour'])) {
                    // 2-level structure: best_time[index][start_hour]
                    if (!empty($timeRow['start_hour']) && !empty($timeRow['end_hour'])) {
                        ClientTime::create([
                            'client_id' => $client->id,
                            'start_hour' => $timeRow['start_hour'],
                            'end_hour' => $timeRow['end_hour'],
                        ]);
                    }
                } elseif (is_array($timeRow)) {
                    // 3-level structure: best_time[index][rowIndex][start_hour]
                    foreach ($timeRow as $time) {
                        if (is_array($time) && !empty($time['start_hour']) && !empty($time['end_hour'])) {
                            ClientTime::create([
                                'client_id' => $client->id,
                                'start_hour' => $time['start_hour'],
                                'end_hour' => $time['end_hour'],
                            ]);
                        }
                    }
                }
            }
        }

        if (!empty($request->input('prices')[$index])) {
            $position = 1;
            foreach ($request->input('prices')[$index] as $custom_price) {
                ClientPriceList::create([
                    'client_id' => $client->id,
                    'name' => $custom_price['side'],
                    'value' => $custom_price['number'] ?? 0,
                    'position' => $position,
                ]);
                $position++;
            }
        }

        if (!empty($request->input('route_id')[$index])) {
            ClientRoute::create([
                'client_id' => $client->id,
                'route_id' => $request->input('route_id')[$index],
            ]);
        }

        $images = $request->input('image')[$index] ?? [];
        // Ensure images is an array
        if (!is_array($images)) {
            $images = !empty($images) ? [$images] : [];
        }
        foreach ($images as $base64Image) {
            if (empty($base64Image) || !is_string($base64Image)) {
                continue;
            }
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            $filename = 'image_' . uniqid() . '.png';
            $filePath = 'client_document/' . $filename;
            Storage::disk('website')->put($filePath, $imageData);

            ClientImage::create([
                'client_id' => $client->id,
                'name' => $filePath,
            ]);
        }
    }

    public function show($id)
    {
        $client = Client::with('parentClient')->findOrFail($id);
        // return $client;
        return view('clients.show', ['client' => $client]);
    }

    public function edit($id)
    {
        $client = Client::with(['childClients' => function ($query) {
            $query->with('clientRouteStaff.route')->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        $route = StaffRoute::orderBy('name')->get();
        return view('clients.edit', ['client' => $client], ['route' => $route]);
    }

    public function deleteClientData($client)
    {
        $clientTime = $client->clientHour;
        $clientImage = $client->clientImage;
        $clientPrice = $client->clientPrice;
        $clientSchedule = $client->clientSchedule;
        $clientRoute = $client->clientRouteStaff;

        if ($clientImage && $clientImage->isNotEmpty()) {
            $clientImage->each(function ($image) {
                $image->forceDelete();
            });
        }

        if ($clientTime && $clientTime->isNotEmpty()) {
            $clientTime->each(function ($time) {
                $time->delete();
            });
        }

        if ($clientPrice && $clientPrice->isNotEmpty()) {
            $clientPrice->each(function ($price) {
                $price->delete();
            });
        }

        if ($clientSchedule && $clientSchedule->isNotEmpty()) {
            $clientSchedule->each(function ($schedule) {
                $clientSchedulePrice = $schedule->clientSchedulePrice;
                if ($clientSchedulePrice && $clientSchedulePrice->isNotEmpty()) {
                    $clientSchedulePrice->each(function ($price) {
                        $price->delete();
                    });
                }
                $schedule->delete();
            });
        }

        if ($clientRoute && $clientRoute->isNotEmpty()) {
            $clientRoute->each(function ($route) {
                $route->forceDelete();
            });
        }
    }

    public function exportClients()
    {
        $clients = Client::with(['profile'])->where('status', 1)->get();

        $exportData = [];

        foreach ($clients as $client) {
            // Format phone number to US format: +1 (XXX) XXX-XXXX
            $phone = $client->contact_phone ?? '';
            $formattedPhone = '';
            if ($phone && strlen($phone) == 10) {
                $formattedPhone = '+1 (' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
            } else {
                $formattedPhone = $phone;
            }

            $exportData[] = [
                'Client Name' => $client->name ?? '',
                'Email' => $client->contact_email ?? '',
                'Phone' => $formattedPhone,
                'Street Number' => $client->profile->street_number ?? '',
                'Address' => $client->profile->address ?? '',
                'City' => $client->profile->city ?? '',
                'Zip Code' => $client->profile->zip_code ?? '',
                'Client Type' => ucfirst($client->client_type ?? ''),
                'Payment Type' => ucfirst($client->payment_type ?? ''),
                'Job Description' => $client->description ?? '',
            ];
        }

        return response()->json($exportData);
    }

    public function checkEmailPhone(Request $request)
    {
        $email = $request->input('email');
        $phone = $request->input('phone');
        $clientId = $request->input('client_id');

        $response = [
            'email_exists' => false,
            'phone_exists' => false,
            'message' => ''
        ];

        if ($email) {
            $emailInClients = Client::where('contact_email', $email);
            if ($clientId) {
                $emailInClients->where('id', '!=', $clientId);
            }

            $emailInProfiles = DB::table('profiles')
                ->where(function ($query) use ($email) {
                    $query->whereRaw("JSON_CONTAINS(additional_emails, '\"$email\"')")
                        ->orWhereRaw("JSON_CONTAINS(invoice_email, '\"$email\"')");
                });

            if ($clientId) {
                $emailInProfiles->where('client_id', '!=', $clientId);
            }

            if ($emailInClients->exists() || $emailInProfiles->exists()) {
                $response['email_exists'] = true;
                $response['message'] = 'Email already exists';
            }
        }

        if ($phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            $phoneInClients = Client::where('contact_phone', $cleanPhone);
            if ($clientId) {
                $phoneInClients->where('id', '!=', $clientId);
            }

            $phoneInProfiles = DB::table('profiles')
                ->whereRaw("JSON_CONTAINS(additional_phones, '\"$cleanPhone\"')");

            if ($clientId) {
                $phoneInProfiles->where('client_id', '!=', $clientId);
            }

            if ($phoneInClients->exists() || $phoneInProfiles->exists()) {
                $response['phone_exists'] = true;
                $response['message'] = 'Phone already exists';
            }
        }

        return response()->json($response);
    }

    public function toggleStatus($id)
    {
        $client = Client::findOrFail($id);
        $client->status = !$client->status;
        if ($client->status == 1) {
            // Send notification to admin
            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' =>  $client->name . ' Client Activated',
                'message' => $client->name . ' A client has been activated.',
                'type' => 'client_activated',
            ]);

            // Send notification to staff if staff_id exists
            if ($client->staff_id) {
                Notification::create([
                    'user_id' => $client->staff_id,
                    'action_id' => 2,
                    'title' => 'Client Activated',
                    'message' => 'Your client has been activated.',
                    'type' => 'staff_client_activated',
                ]);
            }
        } else {
            // Send notification to admin
            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' => $client->name . ' Client Deactivated',
                'message' => $client->name . ' A client has been deactivated.',
                'type' => 'client_deactivated',
            ]);

            // Send notification to staff if staff_id exists
            if ($client->staff_id) {
                Notification::create([
                    'user_id' => $client->staff_id,
                    'action_id' => 2,
                    'title' => 'Client Deactivated',
                    'message' => 'Your client has been deactivated.',
                    'type' => 'staff_client_deactivated',
                ]);
            }
        }
        $client->save();

        return redirect()->back()->with(['title' => $client->status == 1 ? 'Activated' : 'Deactivated', 'message' => 'Client status updated successfully', 'type' => 'success']);
    }

    public function branchCreate($parent_id)
    {
        $parent = Client::findOrFail($parent_id);
        $route = StaffRoute::get();
        return view('clients.branch-create', ['parent' => $parent, 'route' => $route, 'parent_id' => $parent_id]);
    }

    public function branchStore(Request $request, $parent_id)
    {
        DB::beginTransaction();
        try {
            $parent = Client::findOrFail($parent_id);
            $status = auth()->user()->hasRole('admin') ? '1' : '0';
            $staff_id = auth()->user()->hasRole('staff') ? auth()->id() : null;

            $phones = is_array($request->input('phone')) ? $request->input('phone') : [];
            $cleanPhones = array_map(function ($phoneNumber) {
                return preg_replace('/\D/', '', $phoneNumber);
            }, $phones);

            $start_date = $this->parseDate($request->input('start_date'));
            $start_date_second = $this->parseDate($request->input('start_date_second'));

            $endDateFormatted = null;
            if ($request->input('service_frequency') == 'normalWeek' && $start_date) {
                $startDate = Carbon::createFromFormat('d/m/Y', $start_date);
                $endDate = $startDate->copy()->addWeeks(3)->startOfWeek(Carbon::MONDAY)->endOfWeek(Carbon::SUNDAY);
                $endDateFormatted = $endDate->format('d/m/Y');
            }

            $frontImagePath = null;
            $backImagePath = null;
            if ($request->hasFile('front_image')) {
                $frontImagePath = $request->file('front_image')->store('client_document', 'website');
            }
            if ($request->hasFile('back_image')) {
                $backImagePath = $request->file('back_image')->store('client_document', 'website');
            }

            $branch = Client::create([
                'user_id' => $parent->user_id,
                'parent_id' => $parent->id,
                'staff_id' => $staff_id ?? $parent->staff_id,
                'name' => $request->input('name'),
                'client_type' => $request->input('client_type'),
                'house_no' => $request->input('house_no'),
                'address' => $request->input('street'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'postal' => $request->input('postal'),
                'contact_name' => $request->input('contact_name')[0] ?? null,
                'contact_email' => $request->input('email')[0] ?? null,
                'contact_phone' => $cleanPhones[0] ?? null,
                'position' => $request->input('positions')[0] ?? null,
                'payment_type' => $request->input('payment_type') ?? 'cash',
                'commission_percentage' => $request->input('commission_percentage'),
                'description' => $request->input('note')[0] ?? null,
                'service_frequency' => $request->input('service_frequency'),
                'start_date' => $start_date,
                'second_start_date' => $start_date_second,
                'end_date' => $endDateFormatted,
                'front_image' => $frontImagePath,
                'back_image' => $backImagePath,
                'additional_note' => $request->input('additional_note'),
                'status' => $status,
                'is_child' => 1,
            ]);

            if ($status === 1) {
                Notification::create([
                    'user_id' => 2,
                    'action_id' => 2,
                    'title' =>  $branch->name . 'New Branch has been created',
                    'message' => $branch->name . ' A new Branch has been created.',
                    'type' => 'new_branch',
                ]);
            } else {
                Notification::create([
                    'user_id' => 2,
                    'action_id' => 2,
                    'title' => Auth::user()->name . ' has created a new Branch',
                    'message' => 'A new Branch has been created.',
                    'type' => 'new_branch_pending',
                ]);
            }

            $contactNames = is_array($request->contact_name) ? $request->contact_name : [];
            $positions = is_array($request->positions) ? $request->positions : [];
            $emails = is_array($request->email) ? $request->email : [];
            $notes = is_array($request->note) ? $request->note : [];

            $invoiceEmails = [];

            if ($request->has('invoice_email_branch')) {
                $invoiceEmailIndices = $request->input('invoice_email_branch') ?? [];
                foreach ($invoiceEmailIndices as $emailIndex) {
                    if (isset($emails[$emailIndex])) {
                        $invoiceEmails[] = $emails[$emailIndex];
                    }
                }
            } else {
                $invoiceEmails = $emails;
            }

            Profile::create([
                'client_id' => $branch->id,
                'user_id' => null,
                'phone' => $cleanPhones[0] ?? null,
                'address' => $request->input('house_no'),
                'street_number' => $request->input('address'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'postal' => $request->input('postal'),
                // Save ALL data including first item
                'additional_names' => json_encode($contactNames),
                'additional_phones' => json_encode($cleanPhones),
                'additional_positions' => json_encode($positions),
                'additional_emails' => json_encode($emails),
                'additional_notes' => json_encode($notes),
                'invoice_email' => json_encode($invoiceEmails),
            ]);

            if ($request->input('route_id')) {
                ClientRoute::create([
                    'client_id' => $branch->id,
                    'route_id' => $request->input('route_id'),
                ]);
            }

            $unavailDays = $request->input('unavail_day', []);
            if (is_array($unavailDays)) {
                foreach ($unavailDays as $day) {
                    if (!empty($day)) {
                        UnavailDay::create([
                            'client_id' => $branch->id,
                            'day' => $day,
                        ]);
                    }
                }
            }

            $bestTimes = $request->input('best_time', []);
            if (is_array($bestTimes)) {
                foreach ($bestTimes as $time) {
                    if (!empty($time['start_hour']) && !empty($time['end_hour'])) {
                        ClientTime::create([
                            'client_id' => $branch->id,
                            'start_hour' => $time['start_hour'],
                            'end_hour' => $time['end_hour'],
                        ]);
                    }
                }
            }

            $prices = $request->input('prices', []);
            $position = 1;
            if (is_array($prices)) {
                foreach ($prices as $price) {
                    if (!empty($price['side'])) {
                        ClientPriceList::create([
                            'client_id' => $branch->id,
                            'name' => $price['side'],
                            'value' => $price['number'] ?? 0,
                            'position' => $position
                        ]);
                        $position++;
                    }
                }
            }

            if (!empty($request->input('image'))) {
                foreach ($request->input('image') as $base64Image) {
                    if (empty($base64Image) || !is_string($base64Image)) {
                        continue;
                    }
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                    $filename = 'image_' . uniqid() . '.png';
                    $filePath = 'client_document/' . $filename;
                    Storage::disk('website')->put($filePath, $imageData);

                    ClientImage::create([
                        'client_id' => $branch->id,
                        'name' => $filePath,
                    ]);
                }
            }

            DB::commit();
            $this->syncClientToQuickBooks($branch);
            $action = $request->input('action', 'create');
            if ($action === 'create_and_schedule') {
                $hasRoute = !empty($request->input('route_id'));
                if (!$hasRoute) {
                    return redirect()->route('clients.edit', $parent->id)->with(['title' => 'Done', 'message' => 'Branch Created Successfully. Please assign a route to schedule this branch.', 'type' => 'success']);
                }
                return redirect()->to(url('client-schedule/' . $branch->id . '?first-time=true'))->with(['title' => 'Done', 'message' => 'Branch Created Successfully', 'type' => 'success']);
            }
            return redirect()->route('clients.edit', $parent->id)->with(['title' => 'Done', 'message' => 'Branch Created Successfully', 'type' => 'success']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $exception->getMessage())->withInput();
        }
    }

    public function branchEdit($id)
    {
        $branch = Client::with(['parentClient', 'clientRouteStaff', 'clientDay', 'clientHour', 'clientPrice', 'clientImage'])->findOrFail($id);
        $route = StaffRoute::get();
        $currentRouteId = optional($branch->clientRouteStaff->first())->route_id;
        return view('clients.branch-edit', ['branch' => $branch, 'route' => $route, 'currentRouteId' => $currentRouteId]);
    }

    public function branchUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $branch = Client::findOrFail($id);
            $status = auth()->user()->hasRole('admin') ? '1' : '0';
            $phones = is_array($request->input('phone')) ? $request->input('phone') : [];
            $cleanPhones = array_map(function ($phoneNumber) {
                return preg_replace('/\D/', '', $phoneNumber);
            }, $phones);

            $start_date = $this->parseDate($request->input('start_date'));
            $start_date_second = $this->parseDate($request->input('start_date_second'));

            $endDateFormatted = null;
            if ($request->input('service_frequency') == 'normalWeek' && $start_date) {
                $startDate = Carbon::createFromFormat('d/m/Y', $start_date);
                $endDate = $startDate->copy()->addWeeks(3)->startOfWeek(Carbon::MONDAY)->endOfWeek(Carbon::SUNDAY);
                $endDateFormatted = $endDate->format('d/m/Y');
            }

            $branch->update([
                'name' => $request->input('name', $branch->name),
                'client_type' => $request->input('client_type', $branch->client_type),
                'house_no' => $request->input('house_no', $branch->house_no),
                'address' => $request->input('address', $branch->address),
                'city' => $request->input('city', $branch->city),
                'state' => $request->input('state', $branch->state),
                'postal' => $request->input('postal', $branch->postal),
                'contact_name' => $request->input('contact_name')[0] ?? $branch->contact_name,
                'contact_email' => $request->input('email')[0] ?? $branch->contact_email,
                'contact_phone' => $cleanPhones[0] ?? $branch->contact_phone,
                'position' => $request->input('positions')[0] ?? $branch->position,
                'payment_type' => $request->input('payment_type', $branch->payment_type),
                'commission_percentage' => $request->input('commission_percentage', $branch->commission_percentage),
                'description' => $request->input('note')[0] ?? $branch->description,
                'service_frequency' => $request->input('service_frequency', $branch->service_frequency),
                'start_date' => $start_date ?? $branch->start_date,
                'second_start_date' => $start_date_second ?? $branch->second_start_date,
                'end_date' => $endDateFormatted ?? $branch->end_date,
                'additional_note' => $request->input('additional_note', $branch->additional_note),
            ]);

            $contactNames = is_array($request->contact_name) ? $request->contact_name : [];
            $positions = is_array($request->positions) ? $request->positions : [];
            $notes = is_array($request->note) ? $request->note : [];
            $emails = is_array($request->email) ? $request->email : [];

            $invoiceEmails = [];
            if ($request->has('invoice_email_branch')) {
                $invoiceEmailIndices = $request->input('invoice_email_branch') ?? [];
                foreach ($invoiceEmailIndices as $emailIndex) {
                    if (isset($emails[$emailIndex])) {
                        $invoiceEmails[] = $emails[$emailIndex];
                    }
                }
            } else {
                $invoiceEmails = $emails;
            }

            if ($branch->profile) {
                $profile = $branch->profile;
                $profile->update([
                    'phone' => $cleanPhones[0] ?? $profile->phone,
                    'address' => $request->input('house_no', $profile->address),
                    'street_number' => $request->input('address', $profile->street_number),
                    'city' => $request->input('city', $profile->city),
                    'state' => $request->input('state', $profile->state),
                    'postal' => $request->input('postal', $profile->postal),
                    'additional_emails' => json_encode($emails),
                    'additional_phones' => json_encode($cleanPhones),
                    'additional_names' => json_encode($contactNames),
                    'additional_positions' => json_encode($positions),
                    'additional_notes' => json_encode($notes),
                    'invoice_email' => json_encode($invoiceEmails),
                ]);
            } else {
                Profile::create([
                    'client_id' => $branch->id,
                    'user_id' => null,
                    'phone' => $cleanPhones[0] ?? null,
                    'address' => $request->input('house_no') ?? null,
                    'street_number' => $request->input('address') ?? null,
                    'city' => $request->input('city') ?? null,
                    'state' => $request->input('state') ?? null,
                    'postal' => $request->input('postal') ?? null,
                    'additional_emails' => json_encode($emails),
                    'additional_phones' => json_encode($cleanPhones),
                    'additional_names' => json_encode($contactNames),
                    'additional_positions' => json_encode($positions),
                    'additional_notes' => json_encode($notes),
                    'invoice_email' => json_encode($invoiceEmails),
                ]);
            }


            if ($status == 1) {
                Notification::create([
                    'user_id' => 2,
                    'action_id' => 2,
                    'title' => $branch->name . ' Client Updated',
                    'message' => $branch->name . ' A client has been updated.',
                    'type' => 'client_updated',
                ]);
            } else {
                Notification::create([
                    'user_id' => 2,
                    'action_id' => 2,
                    'title' => Auth::user()->name . ' has updated a Client',
                    'message' => 'A client has been updated and is pending approval.',
                    'type' => 'client_updated_pending',
                ]);
            }


            if ($request->hasFile('front_image')) {
                $frontImagePath = $request->file('front_image')->store('client_document', 'website');
                $branch->update(['front_image' => $frontImagePath]);
            }

            if ($request->hasFile('back_image')) {
                $backImagePath = $request->file('back_image')->store('client_document', 'website');
                $branch->update(['back_image' => $backImagePath]);
            }

            $branch->clientRouteStaff()->delete();

            if ($request->input('route_id')) {
                ClientRoute::updateOrCreate(
                    ['client_id' => $branch->id],
                    ['route_id' => $request->input('route_id')]
                );
            }

            $branch->clientDay()->delete();
            $unavailDays = $request->input('unavail_day', []);
            if (is_array($unavailDays)) {
                foreach ($unavailDays as $day) {
                    if (!empty($day)) {
                        UnavailDay::create([
                            'client_id' => $branch->id,
                            'day' => $day,
                        ]);
                    }
                }
            }

            $branch->clientHour()->delete();
            $bestTimes = $request->input('best_time', []);
            if (is_array($bestTimes)) {
                foreach ($bestTimes as $time) {
                    if (!empty($time['start_hour']) && !empty($time['end_hour'])) {
                        ClientTime::create([
                            'client_id' => $branch->id,
                            'start_hour' => $time['start_hour'],
                            'end_hour' => $time['end_hour'],
                        ]);
                    }
                }
            }

            $prices = $request->input('prices', []);
            $position = 1;
            $submittedNames = [];

            if (is_array($prices)) {
                foreach ($prices as $price) {
                    if (!empty($price['side'])) {
                        ClientPriceList::updateOrCreate(
                            ['client_id' => $branch->id, 'name' => $price['side']],
                            ['value' => $price['number'] ?? 0, 'position' => $position]
                        );
                        $submittedNames[] = $price['side'];
                        $position++;
                    }
                }
            }

            ClientPriceList::where('client_id', $branch->id)
                ->whereNotIn('name', $submittedNames)
                ->delete();

            $clientImages = ClientImage::where('client_id', $branch->id)->get();
            foreach ($clientImages as $image) {
                if (in_array($image->name, $request->existing_image ?? [])) {
                    continue;
                }
                Storage::disk('website')->delete($image->name);
                $image->forceDelete();
            }

            if (!empty($request->input('image'))) {
                foreach ($request->input('image') as $base64Image) {
                    if (empty($base64Image) || !is_string($base64Image)) {
                        continue;
                    }
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                    $filename = 'image_' . uniqid() . '.png';
                    $filePath = 'client_document/' . $filename;
                    Storage::disk('website')->put($filePath, $imageData);

                    ClientImage::create([
                        'client_id' => $branch->id,
                        'name' => $filePath,
                    ]);
                }
            }

            DB::commit();

            $this->updateClientInQuickBooks($branch);

            $parentId = $branch->parent_id;

            $action = $request->input('action', 'update');
            if ($action === 'update_and_schedule') {
                $hasRoute = !empty($request->input('route_id'));
                if (!$hasRoute) {
                    return redirect()->route('clients.edit', $parentId)->with(['title' => 'Done', 'message' => 'Branch Updated Successfully. Please assign a route to schedule this branch.', 'type' => 'success']);
                }
                return redirect()->to(url('client-schedule/' . $branch->id . '?first-time=true'))->with(['title' => 'Done', 'message' => 'Branch Updated Successfully', 'type' => 'success']);
            }

            return redirect()->route('clients.edit', $parentId)->with(['title' => 'Done', 'message' => 'Branch Updated Successfully', 'type' => 'success']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $exception->getMessage())->withInput();
        }
    }

    private function syncClientToQuickBooks($client)
    {
        try {
            if (!env('QUICKBOOKS_CLIENT_ID') || !env('QUICKBOOKS_ACCESS_TOKEN')) {
                Log::info('QuickBooks not configured, skipping sync for client: ' . $client->id);
                return;
            }

            if (!$this->quickBooksService) {
                return;
            }

            $clientData = [
                'name' => $client->name,
                'email' => $client->contact_email,
                'phone' => $client->contact_phone,
                'address' => $client->address,
                'city' => $client->city,
                'zip_code' => $client->postal,
                'notes' => $client->description
            ];

            $result = $this->quickBooksService->createCustomer($clientData);

            if ($result['success']) {
                $client->update([
                    'quickbooks_customer_id' => $result['customer_id'],
                    'quickbooks_synced' => true,
                    'quickbooks_synced_at' => now()
                ]);
            } else {
                Log::error('Failed to sync client to QuickBooks', [
                    'client_id' => $client->id,
                    'error' => $result['error']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('QuickBooks sync error: ' . $e->getMessage(), [
                'client_id' => $client->id
            ]);
        }
    }

    private function updateClientInQuickBooks($client)
    {
        try {
            if (!$client->quickbooks_customer_id || !env('QUICKBOOKS_ACCESS_TOKEN')) {
                Log::info('Skipping QuickBooks update', [
                    'client_id' => $client->id,
                    'has_qb_id' => !empty($client->quickbooks_customer_id),
                    'has_token' => !empty(env('QUICKBOOKS_ACCESS_TOKEN'))
                ]);
                return;
            }
            if (!$this->quickBooksService) {
                Log::error('QuickBooksService not injected properly for update');
                return;
            }

            $clientData = [
                'name' => $client->name,
                'email' => $client->contact_email,
                'phone' => $client->contact_phone,
                'address' => $client->address,
                'city' => $client->city,
                'zip_code' => $client->postal,
                'notes' => $client->description
            ];

            $result = $this->quickBooksService->updateCustomer(
                $client->quickbooks_customer_id,
                $clientData
            );

            if ($result['success']) {
                $client->update([
                    'quickbooks_synced_at' => now()
                ]);

                Log::info('Client updated in QuickBooks successfully', [
                    'client_id' => $client->id,
                    'quickbooks_id' => $client->quickbooks_customer_id
                ]);
            } else {
                Log::error('Failed to update client in QuickBooks', [
                    'client_id' => $client->id,
                    'error' => $result['error']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('QuickBooks update error: ' . $e->getMessage(), [
                'client_id' => $client->id
            ]);
        }
    }

    private function deactivateClientInQuickBooks($client)
    {
        try {
            if (!$client->quickbooks_customer_id || !env('QUICKBOOKS_ACCESS_TOKEN')) {
                return;
            }

            if (!$this->quickBooksService) {
                return;
            }
            $result = $this->quickBooksService->deactivateCustomer($client->quickbooks_customer_id);

            if ($result['success']) {
            } else {
                Log::error('Failed to deactivate client in QuickBooks', [
                    'client_id' => $client->id,
                    'error' => $result['error']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('QuickBooks deactivation error: ' . $e->getMessage(), [
                'client_id' => $client->id
            ]);
        }
    }
}
