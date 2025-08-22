<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(){

        $user = auth()->user();

        return view('attendance', compact('user'));
    }
}
