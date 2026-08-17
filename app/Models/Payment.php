<?php

namespace App\Models;

use App\Enums\Payment\PaymentMethod;
use App\Enums\Payment\PaymentProvider;
use App\Enums\Payment\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'appointment_id',
        'stripe_payment_intent_id',
        'payment_method',
        'provider',
        'status',
        'amount',
        'currency',
        'metadata',
        'completed_at',
        'refunded_at',
    ];
    protected $casts = [
        'metadata' => 'array',
        'status' => PaymentStatus::class,
        'provider' => PaymentProvider::class,
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'payment_method'=>PaymentMethod::class,

    ];
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function markAsCompleted($paymentIntentId,$metadata=[]){
$this->update([
    'completed_at' => now(),
    'metadata' => array_merge($this->metadata ?? [],$metadata ),
    'status'=>PaymentStatus::Paid,
    'stripe_payment_intent_id' => $paymentIntentId
]);
    }
    public function markAsFailed($metadata=[])
    {
        $this->update([
            'status' => PaymentStatus::Failed,
            'metadata' => array_merge($this->metadata ?? [],$metadata ),

        ]);
    }
    public function markAsRefunded($metadata=[])
    {
        $this->update([
            'status' => PaymentStatus::Refunded,
            'metadata' => array_merge($this->metadata ?? [],$metadata ),
            'refunded_at' => now()
        ]);
    }
    public function isFinall(){
        return in_array($this->status,[PaymentStatus::Paid,PaymentStatus::Refunded,PaymentStatus::Failed]);
    }
}
