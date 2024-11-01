<?php

namespace App\Services;

use App\Helper\ConversationHelper;
use App\Helper\ContactHelper;
use App\Models\CallLog;
use App\Models\LocationPhone;

class CallService
{
    protected $conversationHelper;
    protected $contactHelper;
    public function __construct(ContactHelper $contactHelper, ConversationHelper $conversationHelper)
    {
        $this->contactHelper = $contactHelper;
        $this->conversationHelper = $conversationHelper;
    }
    public function handleCall($payload , $company)
    {
        try {
            $contact = $this->contactHelper->findOrCreateContact($company, $payload['location_id'], $payload['contact_phone'], $payload['contact_id']??null, $payload['contact_name']);
            if (!$contact) {
                return "something went wrong";
            }
            $contactId = $contact->crm_contact_id;
            // Step 2: Handle conversation creation or fetching
            $conversation = $this->conversationHelper->findOrCreateConversation($company, $payload['location_id'], $payload['conversation_id'] ?? null, $contactId,);
            $conversationId = $conversation['crm_conversation_id'];
            // Step 3: Record the call in CRM
            $callData = [
                'location_id' => $payload['location_id'],
                'call_id' => $payload['call_id'],
                'user_id' => $payload['user_id'] ?? 1,
                'user_name' => $payload['user_name'] ?? 'Test Lead',
                'user_email' => $payload['user_email'] ?? 'test@gmail.com',
                'call_duration' => $payload['call_duration'],
                'call_status' => $payload['call_status'],
                'call_direction' => $payload['call_direction'] ?? 'outbound',
                'attachment' => $payload['attachment'] ?? null,
                'from_phone' => $payload['from_phone'],
                'contact_phone' => $payload['contact_phone'],
                'contact_id' => $contactId,
                'conversation_id' => $conversationId,
            ];


            $locationPhones = new LocationPhone();
            $locationPhones->location_id = $payload['location_id'];
            $locationPhones->phone = $payload['from_phone'];
            $locationPhones->save();
            $fromWebhook = $payload['from_webhook'] ?? false;
            if($fromWebhook)
            {
                // Step 4: Handle missed call tagging if necessary
                if ($payload['call_status'] === 'missed' && strtolower($payload['direction']) == 'inbound') {
                    $this->contactHelper->addMissedCallTag($contactId , $company);
                }
                else if (strtolower($payload['direction']) == 'outbound') {
                    $this->conversationHelper->recordCall($callData , $company);
                }
            }
            else
            {
                $this->conversationHelper->recordCall($callData, $company);
            }
            $call_logs = CallLog::where('call_id', $payload['call_id'])->first();
            if (!$call_logs) {
                $call_logs = new CallLog();
                $call_logs->call_id = $payload['call_id'];
                $call_logs->contact_phone = $payload['call_id'];
                $call_logs->call_direction = $payload['contact_phone'];
                $call_logs->status = $payload['call_status'];
                $call_logs->location_id = $payload['location_id'];
                $call_logs->call_duration = $payload['call_duration'];
                $call_logs->location_phone = $payload['from_phone'];
                $call_logs->save();
            }
            return ['contact_id' => $contactId, 'conversation_id' => $conversationId];
        } catch (\Throwable $th) {
           //
        }
         
    }
}

?>