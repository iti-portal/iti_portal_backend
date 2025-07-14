<?php


namespace App\Services;

use Carbon\Carbon;
use Kreait\Firebase\Factory;


class FirebaseNotificationService
{
    protected $db;

    public function __construct()
    {
        $factory = (new Factory)-> withServiceAccount(config('firebase.credentials'));
        $fireStore = $factory->createFirestore();
        $this->db = $fireStore->database();
    }

    public function send($receiver_id,$data){
        $this->db->collection('notifications')
        ->document($receiver_id)
        ->collection('user_notifications')
        ->add([
            'title' => $data['title'],
            'body' => $data['body']?? '',
            'timestamp' => Carbon::now()->addHours(3)->toDateTimeString(),
            'read' => false,
            'sender' => $data['sender_id'],
            'type' => $data['type'],
            'target_id' => $data['target_id'] ?? null,
        ]);
    }
}
