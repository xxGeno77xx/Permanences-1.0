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

        foreach ($services as $service) {

            $tempUser = User::whereIn('id', $participantsIds)
                ->where('service_id', $service->id)
                ->get();

            foreach ($tempUser as $user) {
                $totalAgentsForPermanence[] = $user->id;
            }

        }

        $serviceAgents = [];

        foreach ($services as $key => $service) {
            $serviceAgents[$key] = array_splice($totalAgentsForPermanence, 0, User::whereIn('id', $participantsIds)->where('service_id', $service->id)
                ->count());
        }

        function interleaveArrays()
        {
            $arrays = func_get_args();
            $maxCount = max(array_map('count', $arrays));
            $result = [];

            for ($i = 0; $i < $maxCount; $i++) {
                foreach ($arrays as $array) {
                    if ($i < count($array)) {
                        $result[] = $array[$i];
                    } elseif ($i >= count($array) && count($array) < $maxCount) {
                        $result[] = $array[$i % count($array)];
                    }
                }
            }

            return $result;
        }

        $finalArray = [];

        $anArray = [];
        $emptyArray = [];

        foreach ($services as $key => $service) {
            $anArray = (interleaveArrays($emptyArray, $serviceAgents[$key]));
        }
        dd($anArray);

        $finalArray = (interleaveArrays($serviceAgents[0], $serviceAgents[1], ));  // this has to be fixed

        $loopCounter = 0;
        foreach ($samedis as $key => $samedi) {

            for ($i=0; $i < count($services); $i++)
            {
                if ($loopCounter == count($finalArray)) {
                    $loopCounter = 0;
                }

                $tempUser = $finalArray[$loopCounter];

                $usersToPickPerService[] = $tempUser;

                $loopCounter++;

            }

            $jsonToInsertIntoOrder[] = [
                "date" => $samedi,
                "participants" => $usersToPickPerService
            ];

            $usersToPickPerService = [];

            if ($key == count($samedis)) {
                break;
            }
        }

  
        $permanence->update([
            'order' => ($jsonToInsertIntoOrder)
        ]);
    }
}
