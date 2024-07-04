<?php

namespace Modules\Kyc\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Kyc\Models\Kyc;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    public function index(Request $request)
    {

        $model  = new Kyc();
        return view('kyc::kyc.index', compact('model'));
    }
    public function view(Request $request, $id)
    {
        try {
            $model = Kyc::find($id);
            if ($model) {
                return view('kyc::kyc.view', compact('model'));
            } else {
                return redirect('/kyc')->with('error', 'Kyc does not exist');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function getList(Request $request)
    {

        $query  = Kyc::orderBy('id', 'DESC');

        if (User::isUser())
            $query->my();

        return Datatables::of($query)
            ->addIndexColumn()


            ->addColumn('status', function ($data) {

                if (User::isAdmin()) {
                    $select = '<select class="form-select state-change"  data-id="' . $data->id . '" data-modeltype="' . Kyc::class . '" aria-label="Default select example">';
                    foreach ($data->getStateOptions() as $key => $option) {
                        $select .= '<option value="' . $key . '"' . ($data->state_id == $key ? ' selected' : '') . '>' . $option . '</option>';
                    }
                    $select .= '</select>';
                    return $select;
                } else {
                    return '<span class="badge badge-' . $data->getStateBadgeOption() . '">' . $data->getState() . '</span>';
                }
            })
            ->addColumn('created_by', function ($data) {
                return !empty($data->createdBy && $data->createdBy->name) ? $data->createdBy->name : 'N/A';
            })
            ->addColumn('created_at', function ($data) {
                return (empty($data->created_at)) ? 'N/A' : date('Y-m-d', strtotime($data->created_at));
            })

            ->addColumn('action', function ($data) {
                $html = '<div class="table-actions text-center">';
                $html .=    '  <a class="btn btn-icon btn-primary mt-1" href="' . url('kyc/view/' . $data->id) . '"  ><i class="fa fa-eye
                        "data-toggle="tooltip"  title="View"></i></a>';
                $html .=  '</div>';
                return $html;
            })->addColumn('customerClickAble', function ($data) {
                $html = 0;

                return $html;
            })
            ->rawColumns(['action', 'customerClickAble', 'status'])
            ->filter(function ($query) {
                $searchValue = request('search.value');
                if (!empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('id', 'like', "%$searchValue%")
                            ->orWhere('mailer', 'like', "%$searchValue%")
                            ->orWhere('host', 'like', "%$searchValue%")
                            ->orWhere('port', 'like', "%$searchValue%")
                            ->orWhere('username', 'like', "%$searchValue%")
                            ->orWhere('encryption', 'like', "%$searchValue%")
                            ->orWhere('from_address', 'like', "%$searchValue%")
                            ->orWhere('created_at', 'like', "%$searchValue%")
                            ->orWhere(function ($query) use ($searchValue) {
                                $query->searchState($searchValue);
                            });
                    });
                }
            })
            ->make(true);
    }

    public function add(Request $request)
    {
        try {

            $model = Kyc::where('created_by_id', Auth::id())->first();
            if ($model) {
                if ($model->state_id != Kyc::STATE_REJECTED)
                    return redirect('kyc/view/' . $model->id);
            }
            $model = new Kyc();
            return view('kyc::kyc.add', compact('model'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $model = Kyc::find($id);
            if ($model) {
                return view('kyc::kyc.update', compact('model'));
            } else {
                return redirect('/email-queue/account')->with('error', 'Account does not exist');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }

        $model  = Kyc::find($id);
        return view('kyc::kyc.update', compact('model'));
    }


    protected static function validator(array $data, $id = null)
    {
        $rules = [
            'name' => 'required|max:150|min:5',
            "email" => "required|email",
            'contact_number' => 'required|digits:10',
            'type_id' => [
                'required',
                function ($attribute, $value, $fail) use ($data, $id) {
                    $validTypeIds = array_keys(Kyc::getTypeOptions());
                    if (!in_array($value, $validTypeIds)) {
                        $fail('The selected type is invalid.');
                    }
                    if ($value == Kyc::TYPE_AADHAR || $value == Kyc::TYPE_PAN || $value == Kyc::TYPE_VOTER || $value == Kyc::TYPE_DRIVING_LICENCE) {
                        $front_image = $data['front_image'] ?? null;
                        if (!isset($front_image) && !isset($id)) {
                            $fail('The front image field is required.');
                        }
                        $back_image = $data['back_image'] ?? null;
                        if (!isset($back_image) && !isset($id)) {
                            $fail('The back image field is required.');
                        }
                    }
                },
            ],
            'national_id' => [
                'required',
                function ($attribute, $value, $fail) use ($data, $id) {

                    if ($data['type_id'] == Kyc::TYPE_AADHAR) {
                        if (!preg_match('/^\d{12}$/', $value)) {
                            $fail('The national ID must be a valid 12-digit Aadhaar number.');
                        }
                    } elseif ($data['type_id'] == Kyc::TYPE_PAN) {
                        if (!preg_match('/^[A-Z]{5}\d{4}[A-Z]{1}$/', $value)) {
                            $fail('The national ID must be a valid PAN number.');
                        }
                    } elseif ($data['type_id'] == Kyc::TYPE_VOTER) {
                        if (!preg_match('/^[A-Z0-9]{10}$/', $value)) {
                            $fail('The national ID must be a valid 10-character Voter ID.');
                        }
                    } elseif ($data['type_id'] == Kyc::TYPE_DRIVING_LICENCE) {
                        if (!preg_match('/^[A-Z]{2}\d{13}$/', $value)) {
                            $fail('The national ID must be a valid Driving Licence number.');
                        }
                    } elseif ($data['type_id'] == Kyc::TYPE_PASSPORT) {
                        if (!preg_match('/^[A-Z]{1}\d{7}$/', $value)) {
                            $fail('The national ID must be a valid Passport number.');
                        }
                    }
                },
            ],
            'video' => 'required|file|mimes:mp4,mov|max:11000',
            "front_image" => 'required|image|mimes:jpeg,png,jpg',
            "back_image" => 'required|image|mimes:jpeg,png,jpg',
            "selfie_image" => 'required|image|mimes:jpeg,png,jpg',
        ];
        return Validator::make($data, $rules);
    }


    public function store(Request $request)
    {
        try {

            if ($this->validator($request->all())->fails()) {
                $message = $this->validator($request->all())->messages()->first();
                return redirect()->back()->withInput()->with('error', $message);
            }
            $model = new Kyc();
            $model->fill($request->all());
            $model->state_id =  Kyc::STATE_INACTIVE;
            $model->created_by_id =  Auth::id();

            if ($request->front_image) {
                $model->front_image = $this->imageUpload($request, "front_image", '/public/uploads');
            }
            if ($request->back_image) {
                $model->back_image = $this->imageUpload($request, "back_image", '/public/uploads');
            }
            if ($request->selfie_image) {
                $model->selfie_image = $this->imageUpload($request, "selfie_image", '/public/uploads');
            }
            if ($request->video) {
                $model->video = $this->imageUpload($request, "video", '/public/uploads');
            }
            if ($model->save()) {
                return redirect('/kyc')->with('success', 'Kyc has been created successfully!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    protected function update(Request $request)
    {
        $model = Kyc::Find($request->id);

        if ($this->validator($request->all())->fails()) {
            $message = $this->validator($request->all())->messages()->first();
            return redirect()->back()->withInput()->with('error', $message);
        }
        if (empty($model)) {
            return redirect('/email-queue/account')->with('error', 'SMTP account not found!');
        }
        $model->fill($request->all());
        if ($model->save()) {
            return redirect('/email-queue/account')->with('success', 'Your account has been updated successfully!');
        }
    }
}
