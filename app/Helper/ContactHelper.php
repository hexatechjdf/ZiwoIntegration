<?php

namespace App\Helper;
use App\Jobs\PushCRMTagJob;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactHelper
{
    public function addMissedCallTag($contactId , $company)
    {   
        $miss_call_tag_name  = CRM::getDefault('miss_call_tag_name');
        $tags = [$miss_call_tag_name];
        $payload = [
            'company_id' => $company->id,
            'crm_contact_id' => $contactId,
            'tags' => $tags,
            'custom_fields' => null,
        ];
        // Dispatch the job to handle adding the tag to the contact asynchronously
        PushCRMTagJob::dispatch($payload)->onQueue(env('QUEUE_TYPE','local'));
    }   
    /**
     * Find or create a contact, both internally and in HL.
     * @param string $location_id
     * @param string|null $contactPhone
     * @param string|null $contact_id
     * @param string|null $contactName
     * @return mixed
     */
    public function findOrCreateContact($company, $location_id, $contactPhone = null, $contact_id = null, $contactName = null)
    {
        // If contact_id is provided, prioritize it
        if ($contact_id) {
            // Step 1: Check internal database by contact_id
            $internalContact = Contact::where('crm_contact_id', $contact_id)->first();
            
            // Step 2: If contact exists in internal DB, return it
            if ($internalContact) {
                return $internalContact;
            }

            // Step 3: If contact doesn't exist in internal DB, fetch it from HL
            $externalContact = $this->findContactByIdInHL($company , $location_id, $contact_id);
            if ($externalContact) {
                // Step 4: Save the contact to internal DB and return it
                try {
                    return DB::transaction(function () use ($externalContact) {
                        return Contact::create([
                            'contact_phone' => $externalContact->phone ?? null,
                            'contact_name' => $externalContact->name ?? 'New Lead',
                            'crm_contact_id' => $externalContact->id,
                        ]);
                    });
                } catch (\Exception $e) {
                    // Handle the error (e.g., log it, return an error response)
                   return $e->getMessage();
                }
            }

            return null; // In case no contact is found
        }

        // If no contact_id is provided, use contactPhone for lookup
        if ($contactPhone) {
            // Step 5: Check internal database by contact_phone
            $internalContact = Contact::where('contact_phone', $contactPhone)->first();

            // Step 6: If contact exists in internal DB and has crm_contact_id, return it
            if ($internalContact && $internalContact->crm_contact_id) {
                return $internalContact;
            }
         
            // Step 7: If the contact exists but doesn't have crm_contact_id, search in HL
                $externalContact = $this->findContactInHL($company, $location_id, $contactPhone);
                if ($externalContact) {
                    // Update internal contact with external crm_contact_id and return it
                    if(!$internalContact)
                    {
                        $internalContact = new Contact();
                    }
                    $internalContact->contact_name = $contactName;
                    $internalContact->contact_phone = $contactPhone;
                    $internalContact->crm_contact_id = $externalContact->id;
                    $internalContact->save();
                    return $internalContact;
                }
            

            // Step 8: If no contact exists in HL, create it in HL and internal DB
            if (!$internalContact) {
                $externalContact = $this->createContactInHL($company, $location_id, $contactPhone, $contactName);
                return DB::transaction(function () use ($externalContact, $contactPhone, $contactName) {
                    return Contact::create([
                        'contact_phone' => $contactPhone,
                        'contact_name' => $contactName ?? $externalContact['name'],
                        'crm_contact_id' => $externalContact['id'],
                    ]);
                });
            }
        }

        return null; // Return null if no contactPhone or contact_id is provided
    }

    /**
     * Find a contact in HL by contact_id.
     * @param string $location_id
     * @param string $contact_id
     * @return mixed|null
     */
    public function findContactByIdInHL($company , $location_id, $contact_id)
    {
        // Assuming CRM::findContactById() checks HL and returns the contact details or null
        return CRM::findContactfilter($company , $location_id, 'id',$contact_id);
    }

    /**
     * Find a contact in HL by phone number.
     * @param string $location_id
     * @param string $contactPhone
     * @return mixed|null
     */
    public function findContactInHL($company , $location_id, $contactPhone)
    {
        // Assuming CRM::findContactByPhone() checks HL and returns the contact details or null
        return CRM::findContactfilter($company, $location_id, 'phone',$contactPhone);
    }

    /**
     * Create a new contact in HL.
     * @param string $location_id
     * @param string $contactPhone
     * @param string|null $contactName
     * @return mixed
     */
    public function createContactInHL($company , $location_id, $contactPhone, $contactName = null)
    {
        $contactObj = null;
        $call = 'contacts';
        $payload = [
            'phone' => $contactPhone,
            'name' => $contactName ?? '',
        ];
        $response = CRM::crmV2($company->id, $call, 'POST', json_encode($payload), [], true, $location_id);
        if ($response && property_exists($response, 'contacts')) {
            $contactObj = $response->contacts[0];
        } else if ($response && property_exists($response, 'contact')) {
            $contactObj = $response->contact;
        }
        return $contactObj;
    }
}
