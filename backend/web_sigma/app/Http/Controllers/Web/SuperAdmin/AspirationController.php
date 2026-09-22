<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Aspiration;
use App\Helpers\EncryptHelper;
use Illuminate\Http\Request;


class AspirationController extends Controller
{

    public function index()
    {
        $aspirations = Aspiration::latest()
            ->paginate(10);


        return view(
            'super_admin.aspirations.index',
            compact('aspirations')
        );
    }



    public function show($hash)
{

    $id = EncryptHelper::decrypt($hash);


    $aspiration = Aspiration::findOrFail($id);


    return view(
        'super_admin.aspirations.show',
        compact('aspiration')
    );

}



    public function updateStatus(Request $request, Aspiration $aspiration)
    {

        $request->validate([
            'status'=>'required|in:pending,done'
        ]);


        $aspiration->update([
            'status'=>$request->status
        ]);


        return back()
            ->with(
                'success',
                'Status aspirasi berhasil diperbarui'
            );
    }

}