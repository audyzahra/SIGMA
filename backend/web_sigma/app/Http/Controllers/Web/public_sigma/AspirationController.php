<?php

namespace App\Http\Controllers\Web\public_sigma;

use App\Http\Controllers\Controller;
use App\Models\Aspiration;
use Illuminate\Http\Request;

class AspirationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'organization' => [
                'nullable',
                'string',
                'max:255',
            ],

            'region' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],
        ]);

        $validated['status'] = 'pending';

        Aspiration::create($validated);

        return redirect()
        ->back()
        ->with(
            'success',
            'Informasi berhasil dikirim. Terima kasih atas partisipasi Anda.'
        );
    }
}