<?php

namespace App\Models;

use App\Enums\Payment\PaymentProvider;
use App\Enums\Payment\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'section_id',
        'amount',
        'status',
        'completed_at',
        'currency',
        'metadata',
        'payment_intent_id',
        'provider',
        'refunded_at'
    ];
    protected $casts = [
        'metadata' => 'array',
        'status' => PaymentStatus::class,
        'provider' => PaymentProvider::class,
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime'

    ];
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function section(){
        return $this->belongsTo(Section::class, 'section_id');
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
