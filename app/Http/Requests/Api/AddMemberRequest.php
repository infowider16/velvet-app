<?php

namespace App\Http\Requests\Api;

use App\Models\User;

class AddMemberRequest extends BaseApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'group_id' => 'required|integer|exists:groups,id',
            'user_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $userIds = $value;

                    if (is_array($userIds) && count($userIds) === 1 && is_string($userIds[0]) && str_starts_with(trim($userIds[0]), '[')) {
                        $decoded = json_decode($userIds[0], true);
                        if (is_array($decoded)) {
                            $userIds = $decoded;
                        }
                    } elseif (!is_array($userIds)) {
                        $userIds = [$userIds];
                    }

                    foreach ($userIds as $uid) {
                        if (!is_numeric($uid) || !User::where('id', $uid)->exists()) {
                            $fail(__('validation.custom.user_id.invalid_user_id', ['id' => $uid]));
                        }
                    }
                }
            ],
            'role' => 'nullable|string|in:member,admin',
        ];
    }
}