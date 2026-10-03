<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatBoardController extends Controller
{
    public function index()
    {
        return view('user.pages.chatboard.index');
    }
}
