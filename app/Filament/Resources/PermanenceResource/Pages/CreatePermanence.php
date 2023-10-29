<?php

namespace App\Filament\Resources\PermanenceResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\Service;
use App\Functions\DateFunction;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PermanenceResource;

class CreatePermanence extends CreateRecord
{
    protected static string $resource = PermanenceResource::class;

    public function afterCreate()
    {
        $permanence = $this->record;

        $samedis = DateFunction::getDateForSpecificDayBetweenDates($permanence->date_debut, $permanence->date_fin, env('PERMANENCE'));

        //All services  with de the department of the logged in user
        $services = Service::where('departement_id', auth()->user()->service->departement_id)
            ->select('nom_service', 'id')
            ->get();

        // returns the IDs of the selected users (users column content)  as json boject 
        //if I remove the where clause, the query returns every single user in Database....no clue as to why tho
        $parmanenceAgentsJson = $permanence->select('users')
            ->where('id', $permanence->id)
            ->get();

        $participantsIds = [];

        foreach ($parmanenceAgentsJson as $key => $participant) {
            $participantData = json_decode($participant, true);

            for ($i = $key; $i < count($participantData['users'][$key]['participants']); $i++) {
                $participantsIds[] = $participantData['users'][$key]['participants'][$i]; // retrieving  Ids of participants from the users Json Column and putting them in an array
            }

        }

        $usersToPickPerService = [];

        $jsonToInsertIntoOrder = [];

        //for each permanence day and for each service,  choose one agent
        foreach ($samedis as $key => $samedi) {

            foreach ($services as $key => $service) {
                shuffle($participantsIds);
                $tempUser = User::whereIn('id', $participantsIds)
                    ->select('id', 'service_id')
                    ->where('service_id', $service->id)
                    ->inRandomOrder()
                    ->limit(1)
                    ->first();

                $usersToPickPerService[] = $tempUser->id;
            }

            $jsonToInsertIntoOrder[] = [
                "date" => $samedi,
                "participants" => $usersToPickPerService
            ];

            //emptying table after insertion
            $usersToPickPerService = []; 
        }

        $permanence->update([
            'order' => ($jsonToInsertIntoOrder)
        ]);
    }

    // public static function decodeJson($jsonComponent)
    // {
    //     foreach ($jsonComponent as $key => $participant) {
    //         $participantData = json_decode($participant);

    //         for ($i = $key; $i < count($participantData['order'][$key]['participants']); $i++) {
    //             $participantsIds[] = $participantData['order'][$key]['participants'][$i]; // retrieving  Ids of participants from the users Json Column and putting them in an array
    //         }

    //     }
    //     return $participantsIds;
    // }
}
