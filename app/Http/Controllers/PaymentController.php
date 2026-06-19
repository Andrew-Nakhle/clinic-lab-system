<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public  function __construct(){
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
}
