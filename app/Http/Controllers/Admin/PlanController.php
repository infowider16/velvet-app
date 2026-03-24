<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Services\PlanServiceInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class PlanController extends Controller
{
    protected $planService;

    public function __construct(PlanServiceInterface $planService)
    {
        $this->planService = $planService;
    }

    // Ghost Plan methods
    public function index()
    {
        try {
            $plans = $this->planService->getAll();
            return view('admin.ghost', compact('plans'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load plans.');
        }
    }

    public function store(Request $request)
    {
        $rules = [
            'tag_translation.en'      => 'required|string|max:255',
            'tag_translation.ge'      => 'required|string|max:255',
            'title_translation.en'    => 'required|string|max:255',
            'title_translation.ge'    => 'required|string|max:255',
            'duration_value'          => 'required|integer|min:1|max:365',
            'unit'                    => 'required|in:hours,days',
            'duration_translation.en' => 'required|string|max:255',
            'duration_translation.ge' => 'required|string|max:255',
            'amount'                  => 'required|numeric|min:0|max:99999999.99',
        ];

        $messages = [
            'tag_translation.en.required' => 'English tag is required.',
            'tag_translation.ge.required' => 'German tag is required.',
            'title_translation.en.required' => 'English title is required.',
            'title_translation.ge.required' => 'German title is required.',
            'duration_value.required' => 'Duration value is required.',
            'duration_value.integer' => 'Duration value must be a number.',
            'duration_value.min' => 'Duration value must be at least 1.',
            'duration_value.max' => 'Duration value may not be greater than 365.',
            'unit.required' => 'Unit is required.',
            'unit.in' => 'Unit must be hours or days.',
            'duration_translation.en.required' => 'English duration text is required.',
            'duration_translation.ge.required' => 'German duration text is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be numeric.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $payload = [
                'tag' => $request->input('tag_translation.en'),
                'title' => $request->input('title_translation.en'),
                'duration' => $request->input('duration_value') . '_' . $request->input('unit'),
                'amount' => $request->input('amount'),
                'currency' => 'USD',

                'tag_translation' => [
                    'en' => $request->input('tag_translation.en'),
                    'ge' => $request->input('tag_translation.ge'),
                ],
                'title_translation' => [
                    'en' => $request->input('title_translation.en'),
                    'ge' => $request->input('title_translation.ge'),
                ],
                'duration_translation' => [
                    'en' => $request->input('duration_translation.en'),
                    'ge' => $request->input('duration_translation.ge'),
                ],
            ];

            $plan = $this->planService->create($payload);

            if (!$plan) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to create Ghost Plan.'
                ], 500);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Ghost Plan created successfully.',
                'data'    => $plan
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating Ghost Plan: ' . $e->getMessage(), [
                'payload' => $request->all()
            ]);

            return response()->json([
                'status'  => 0,
                'message' => 'An error occurred while creating the Ghost Plan.'
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $plan = $this->planService->getById($id);

            if (!$plan) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Plan not found.'
                ], 404);
            }

            $durationRaw = $plan->getRawOriginal('duration');
            $durationParts = is_string($durationRaw) ? explode('_', $durationRaw) : [];

            return response()->json([
                'status' => 1,
                'data' => [
                    'id' => $plan->id,
                    'tag' => $plan->tag,
                    'title' => $plan->title,
                    'amount' => $plan->amount,
                    'currency' => $plan->currency,
                    'duration' => $durationRaw,
                    'duration_value' => $durationParts[0] ?? '',
                    'unit' => $durationParts[1] ?? '',
                    'tag_translation' => $plan->tag_translation ?? [],
                    'title_translation' => $plan->title_translation ?? [],
                    'duration_translation' => $plan->duration_translation ?? [],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'tag_translation.en'      => 'required|string|max:255',
            'tag_translation.ge'      => 'required|string|max:255',
            'title_translation.en'    => 'required|string|max:255',
            'title_translation.ge'    => 'required|string|max:255',
            'duration_value'          => 'required|integer|min:1|max:365',
            'unit'                    => 'required|in:hours,days',
            'duration_translation.en' => 'required|string|max:255',
            'duration_translation.ge' => 'required|string|max:255',
            'amount'                  => 'required|numeric|min:0|max:99999999.99',
        ];

        $messages = [
            'tag_translation.en.required' => 'English tag is required.',
            'tag_translation.ge.required' => 'German tag is required.',
            'title_translation.en.required' => 'English title is required.',
            'title_translation.ge.required' => 'German title is required.',
            'duration_value.required' => 'Duration value is required.',
            'duration_value.integer' => 'Duration value must be a number.',
            'duration_value.min' => 'Duration value must be at least 1.',
            'duration_value.max' => 'Duration value may not be greater than 365.',
            'unit.required' => 'Unit is required.',
            'unit.in' => 'Unit must be hours or days.',
            'duration_translation.en.required' => 'English duration text is required.',
            'duration_translation.ge.required' => 'German duration text is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be numeric.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $payload = [
                'tag' => $request->input('tag_translation.en'),
                'title' => $request->input('title_translation.en'),
                'duration' => $request->input('duration_value') . '_' . $request->input('unit'),
                'amount' => $request->input('amount'),

                'tag_translation' => [
                    'en' => $request->input('tag_translation.en'),
                    'ge' => $request->input('tag_translation.ge'),
                ],
                'title_translation' => [
                    'en' => $request->input('title_translation.en'),
                    'ge' => $request->input('title_translation.ge'),
                ],
                'duration_translation' => [
                    'en' => $request->input('duration_translation.en'),
                    'ge' => $request->input('duration_translation.ge'),
                ],
            ];

            $updated = $this->planService->update($id, $payload);

            if (!$updated) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to update Ghost Plan.'
                ], 500);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Ghost Plan updated successfully.',
                'data'    => $updated
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error updating Ghost Plan: ' . $e->getMessage(), [
                'plan_id' => $id,
                'payload' => $request->all()
            ]);

            return response()->json([
                'status'  => 0,
                'message' => 'An error occurred while updating the Ghost Plan.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            return $this->planService->delete($id);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Boost Plan methods
    public function boostIndex()
    {
        try {
            $plans = $this->planService->getAllBoosts();
            return view('admin.boost', compact('plans'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load boost plans.');
        }
    }

    public function boostStore(Request $request)
    {
        $rules = [
            'tag_translation.en'   => 'nullable|string|max:255',
            'tag_translation.ge'   => 'nullable|string|max:255',
            'title_translation.en' => 'nullable|string|max:255',
            'title_translation.ge' => 'nullable|string|max:255',
            'boost_count'          => 'required|integer|min:1|max:1000',
            'discount'             => 'nullable|numeric|min:0|max:100',
            'amount'               => 'required|numeric|min:0|max:99999999.99',
        ];

        $messages = [
            'boost_count.required' => 'Boost count is required.',
            'boost_count.integer'  => 'Boost count must be a number.',
            'boost_count.min'      => 'Boost count must be at least 1.',
            'boost_count.max'      => 'Boost count may not be greater than 1000.',
            'discount.numeric'     => 'Discount must be numeric.',
            'discount.min'         => 'Discount must be at least 0.',
            'discount.max'         => 'Discount may not be greater than 100.',
            'amount.required'      => 'Amount is required.',
            'amount.numeric'       => 'Amount must be numeric.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $discount = $request->input('discount');
            $discount = ($discount === null || $discount === '') ? 0 : $discount;

            $payload = [
                'tag' => $request->input('tag_translation.en'),
                'title' => $request->input('title_translation.en'),
                'tag_translation' => [
                    'en' => $request->input('tag_translation.en'),
                    'ge' => $request->input('tag_translation.ge'),
                ],
                'title_translation' => [
                    'en' => $request->input('title_translation.en'),
                    'ge' => $request->input('title_translation.ge'),
                ],
                'boost_count' => $request->input('boost_count'),
                'discount' => $discount,
                'amount' => $request->input('amount'),
            ];

            $plan = $this->planService->createBoost($payload);

            if ($plan) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Boost Plan created successfully.',
                    'data' => $plan
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Failed to create Boost Plan.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error creating Boost Plan: ' . $e->getMessage(), [
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function boostEdit($id)
    {
        try {
            $plan = $this->planService->getBoostById($id);

            if ($plan) {
                return response()->json([
                    'status' => 1,
                    'data' => [
                        'id' => $plan->id,
                        'tag' => $plan->tag,
                        'title' => $plan->title,
                        'tag_translation' => $plan->tag_translation ?? [],
                        'title_translation' => $plan->title_translation ?? [],
                        'boost_count' => $plan->boost_count,
                        'discount' => $plan->discount,
                        'amount' => $plan->amount,
                    ]
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Boost Plan not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function boostUpdate(Request $request, $id)
    {
        $rules = [
            'tag_translation.en'   => 'nullable|string|max:255',
            'tag_translation.ge'   => 'nullable|string|max:255',
            'title_translation.en' => 'nullable|string|max:255',
            'title_translation.ge' => 'nullable|string|max:255',
            'boost_count'          => 'required|integer|min:1|max:1000',
            'discount'             => 'nullable|numeric|min:0|max:100',
            'amount'               => 'required|numeric|min:0|max:99999999.99',
        ];

        $messages = [
            'boost_count.required' => 'Boost count is required.',
            'boost_count.integer'  => 'Boost count must be a number.',
            'boost_count.min'      => 'Boost count must be at least 1.',
            'boost_count.max'      => 'Boost count may not be greater than 1000.',
            'discount.numeric'     => 'Discount must be numeric.',
            'discount.min'         => 'Discount must be at least 0.',
            'discount.max'         => 'Discount may not be greater than 100.',
            'amount.required'      => 'Amount is required.',
            'amount.numeric'       => 'Amount must be numeric.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $discount = $request->input('discount');
            $discount = ($discount === null || $discount === '') ? 0 : $discount;

            $payload = [
                'tag' => $request->input('tag_translation.en'),
                'title' => $request->input('title_translation.en'),
                'tag_translation' => [
                    'en' => $request->input('tag_translation.en'),
                    'ge' => $request->input('tag_translation.ge'),
                ],
                'title_translation' => [
                    'en' => $request->input('title_translation.en'),
                    'ge' => $request->input('title_translation.ge'),
                ],
                'boost_count' => $request->input('boost_count'),
                'discount' => $discount,
                'amount' => $request->input('amount'),
            ];

            $updated = $this->planService->updateBoost($id, $payload);

            if ($updated) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Boost Plan updated successfully.',
                    'data' => $updated
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Failed to update Boost Plan.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error updating Boost Plan: ' . $e->getMessage(), [
                'plan_id' => $id,
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function boostDestroy($id)
    {
        try {
            $deleted = $this->planService->deleteBoost($id);
            if ($deleted) {
                return response()->json(['status' => 1, 'message' => 'Boost Plan deleted successfully.']);
            }
            return response()->json(['status' => 0, 'message' => 'Failed to delete Boost Plan.'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    // Pin Plan methods
    public function pinIndex()
    {
        try {
            $plans = $this->planService->getAllPins();
            return view('admin.pin', compact('plans'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load pin plans.');
        }
    }

    public function pinStore(Request $request)
    {
        $rules = [
            'tag_translation.en'   => 'nullable|string|max:255',
            'tag_translation.ge'   => 'nullable|string|max:255',
            'title_translation.en' => 'nullable|string|max:255',
            'title_translation.ge' => 'nullable|string|max:255',
            'pin_count'            => 'required|integer|min:1|max:1000',
            'discount'             => 'nullable|numeric|min:0|max:100',
            'amount'               => 'required|numeric|min:0|max:99999999.99',
        ];

        $messages = [
            'pin_count.required' => 'Pin count is required.',
            'pin_count.integer'  => 'Pin count must be a number.',
            'pin_count.min'      => 'Pin count must be at least 1.',
            'pin_count.max'      => 'Pin count may not be greater than 1000.',
            'discount.numeric'   => 'Discount must be numeric.',
            'discount.min'       => 'Discount must be at least 0.',
            'discount.max'       => 'Discount may not be greater than 100.',
            'amount.required'    => 'Amount is required.',
            'amount.numeric'     => 'Amount must be numeric.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $discount = $request->input('discount');
            $discount = ($discount === null || $discount === '') ? 0 : $discount;

            $payload = [
                'tag' => $request->input('tag_translation.en'),
                'title' => $request->input('title_translation.en'),
                'tag_translation' => [
                    'en' => $request->input('tag_translation.en'),
                    'ge' => $request->input('tag_translation.ge'),
                ],
                'title_translation' => [
                    'en' => $request->input('title_translation.en'),
                    'ge' => $request->input('title_translation.ge'),
                ],
                'pin_count' => $request->input('pin_count'),
                'discount' => $discount,
                'amount' => $request->input('amount'),
            ];

            $plan = $this->planService->createPin($payload);

            if ($plan) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Pin Plan created successfully.',
                    'data' => $plan
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Failed to create Pin Plan.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error creating Pin Plan: ' . $e->getMessage(), [
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pinEdit($id)
    {
        try {
            $plan = $this->planService->getPinById($id);

            if ($plan) {
                return response()->json([
                    'status' => 1,
                    'data' => [
                        'id' => $plan->id,
                        'tag' => $plan->tag,
                        'title' => $plan->title,
                        'tag_translation' => $plan->tag_translation ?? [],
                        'title_translation' => $plan->title_translation ?? [],
                        'pin_count' => $plan->pin_count,
                        'discount' => $plan->discount,
                        'amount' => $plan->amount,
                    ]
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Pin Plan not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function pinUpdate(Request $request, $id)
    {
        $rules = [
            'tag_translation.en'   => 'nullable|string|max:255',
            'tag_translation.ge'   => 'nullable|string|max:255',
            'title_translation.en' => 'nullable|string|max:255',
            'title_translation.ge' => 'nullable|string|max:255',
            'pin_count'            => 'required|integer|min:1|max:1000',
            'discount'             => 'nullable|numeric|min:0|max:100',
            'amount'               => 'required|numeric|min:0|max:99999999.99',
        ];

        $messages = [
            'pin_count.required' => 'Pin count is required.',
            'pin_count.integer'  => 'Pin count must be a number.',
            'pin_count.min'      => 'Pin count must be at least 1.',
            'pin_count.max'      => 'Pin count may not be greater than 1000.',
            'discount.numeric'   => 'Discount must be numeric.',
            'discount.min'       => 'Discount must be at least 0.',
            'discount.max'       => 'Discount may not be greater than 100.',
            'amount.required'    => 'Amount is required.',
            'amount.numeric'     => 'Amount must be numeric.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $discount = $request->input('discount');
            $discount = ($discount === null || $discount === '') ? 0 : $discount;

            $payload = [
                'tag' => $request->input('tag_translation.en'),
                'title' => $request->input('title_translation.en'),
                'tag_translation' => [
                    'en' => $request->input('tag_translation.en'),
                    'ge' => $request->input('tag_translation.ge'),
                ],
                'title_translation' => [
                    'en' => $request->input('title_translation.en'),
                    'ge' => $request->input('title_translation.ge'),
                ],
                'pin_count' => $request->input('pin_count'),
                'discount' => $discount,
                'amount' => $request->input('amount'),
            ];

            $updated = $this->planService->updatePin($id, $payload);

            if ($updated) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Pin Plan updated successfully.',
                    'data' => $updated
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Failed to update Pin Plan.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error updating Pin Plan: ' . $e->getMessage(), [
                'plan_id' => $id,
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pinDestroy($id)
    {
        try {
            $deleted = $this->planService->deletePin($id);
            if ($deleted) {
                return response()->json(['status' => 1, 'message' => 'Pin Plan deleted successfully.']);
            }
            return response()->json(['status' => 0, 'message' => 'Failed to delete Pin Plan.'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
