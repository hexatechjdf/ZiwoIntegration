<?php

namespace App\Helper;

use App\Models\Conversation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationHelper
{
  
    public function recordCall($payload , $company) {
        $externalCrmcall = null;
        $currentDateTime = Carbon::now();
        // Format the current time in the desired format
        $formattedDateTime = $currentDateTime->format('Y-m-d\TH:i:s\Z');
        $call = 'conversations/messages/outbound';
        $call_object = [   "to" =>  $payload['contact_phone'],
                        "from" =>  $payload['from_phone'],
                        "status" =>  "completed"
                    ];
         $accountName = CRM::getDefault('ziwo_account_name', 'asnanimedia');
        $file = "https://".$accountName."-api.aswat.co/surveillance/recordings/" . $payload['attachment'];
               
        $conversation_provider_id = CRM::getDefault('crm_conversation_provider_id');
        $payload1 =
                        [
                            "type" =>  "Call",
                            "conversationId" =>  $payload['conversation_id'],
                            "conversationProviderId" => $conversation_provider_id ,
                            "altId" =>  $payload['call_id'],
                            "date" =>  $formattedDateTime,
                            "call" =>  json_encode($call_object),
                            "attachments" => [$file]
                        ];
        $response = CRM::crmV2($company->id, $call, 'POST', $payload1, [], true, $payload['location_id']);
        if ($response && property_exists($response, 'success')) {
            $externalCrmcall = $response;
        }
        return $externalCrmcall;
    }
    /**
     * Find or create a conversation, both internally and in HL.
     * @param string $location_id
     * @param string|null $conversation_id
     * @param string|null $contact_id
     * @return mixed
     */
    public function findOrCreateConversation($company, $location_id, $conversation_id = null, $contact_id = null)
    {
        try
        {
            // Step 1: If conversation_id is provided, prioritize it
            if ($conversation_id) {
                // Check internal database by conversation_id
                $internalConversation = Conversation::where('crm_conversation_id', $conversation_id)->first();

                // If conversation exists in internal DB, return it
                if ($internalConversation) {
                    return $internalConversation;
                }

                // If conversation doesn't exist in internal DB, fetch it from HL
                $externalConversation = $this->findConversationByIdInHL($company, $location_id, $conversation_id);
                if ($externalConversation) {
                    // Save the conversation to internal DB and return it
                    return DB::transaction(function () use ($externalConversation) {
                        return Conversation::create([
                            'crm_conversation_id' => $externalConversation['id'],
                            'crm_contact_id' => $externalConversation['contact_id'], // Assuming this exists in the response
                            'conversation_data' => json_encode($externalConversation), // Save any additional data if necessary
                        ]);
                    });
                }

                return null; // In case no conversation is found
            }
        }
        catch (\Exception $e)
        {
            throw $e->getMessage();
        }

        // Step 2: If no conversation_id, check with contact_id to find the conversation
        if ($contact_id) {
            // Fetch conversation for the given contact_id from HL
            $externalConversation = $this->findConversationByContactIdInHL($company, $location_id, $contact_id);
            if ($externalConversation) {
                // Check if the conversation already exists in the internal database
                $internalConversation = Conversation::where('crm_conversation_id', $externalConversation->id)->first();

                // If conversation is already present, return it
                if ($internalConversation) {
                    return $internalConversation;
                }

                // Otherwise, save it in the internal database and return it
                return DB::transaction(function () use ($externalConversation, $contact_id) {
                    return Conversation::create([
                        'crm_conversation_id' => $externalConversation->id,
                        'crm_contact_id' => $contact_id,
                        'conversation_data' => json_encode($externalConversation), // Save additional data
                    ]);
                });
            }

            // Step 3: If no conversation exists in HL, create a new conversation
            $newConversation = $this->createConversationInHL($company, $location_id, $contact_id);
            
            // Save the newly created conversation in both internal DB and HL
            return DB::transaction(function () use ($newConversation, $contact_id) {
                return Conversation::create([
                    'crm_conversation_id' => $newConversation['id'],
                    'crm_contact_id' => $contact_id,
                    'conversation_data' => json_encode($newConversation), // Save additional data
                ]);
            });
        }

        return null; // Return null if neither conversation_id nor contact_id is provided
    }

    /**
     * Find a conversation in HL by conversation_id.
     * @param string $location_id
     * @param string $conversation_id
     * @return mixed|null
     */
    public function findConversationByIdInHL($company , $location_id, $conversation_id)
    {
        // Assuming CRM::findConversationById() checks HL and returns the conversation details or null
        return CRM::findConversationbyId($company, $location_id, $conversation_id);
    }

    /**
     * Find a conversation in HL by contact_id.
     * @param string $location_id
     * @param string $contact_id
     * @return mixed|null
     */
    public function findConversationByContactIdInHL($company, $location_id, $contact_id)
    {
        // Assuming CRM::findConversationByContactId() checks HL and returns the conversation details or null
        return CRM::findConversationbyContact($company, $location_id, $contact_id);
    }

    /**
     * Create a new conversation in HL.
     * @param string $location_id
     * @param string $contact_id
     * @return mixed
     */
    public function createConversationInHL($company, $location_id, $contact_id)
    {
        $conversationObj = null;
        $call = 'conversations';
        $payload = [
            'contactId' => $contact_id,
        ];
        $response = CRM::crmV2($company->id, $call, 'POST', json_encode($payload), [], true, $location_id);
        if ($response && property_exists($response, 'conversation')) {
            $conversationObj = $response->conversation;
        }

        return $conversationObj;
    }
}
