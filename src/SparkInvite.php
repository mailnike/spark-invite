<?php

namespace ZiNETHQ\SparkInvite;

use ZiNETHQ\SparkInvite\Models\Invitation;
use Webpatser\Uuid\Uuid as Uuid;
use Event;

class SparkInvite
{
    public function invite($referralTeam, $referralUser, $invitee, $event = 'invite')
    {
        $invitation = Invitation::getByUser($invitee);
        if ($invitation) {
            $invitation->validateStatus();
            return $invitation;
        }

        $invitation = new Invitation();
        $invitation->referralTeam()->associate($referralTeam);
        $invitation->referralUser()->associate($referralUser);
        $invitation->invitee()->associate($invitee);
        $invitation->old_password = $invitee->password;
        $invitation->save();

        $invitation->token = Uuid::generate(5, $invitation->id, Uuid::NS_OID)->string;
        $invitation->save();

        $this->publishEvent($event, $invitation);

        return $invitation;
    }

    public function reinvite($invitation)
    {
        $invitation->cancel();
        return $this->invite($invitation->referralTeam, $invitation->referralUser, $invitation->invitee, 'reissue');
    }

    /**
     * Fire Laravel event
     * @param  string $event event name
     */
    private function publishEvent($eventKey, $invitation = null)
    {
        Event::fire(config('sparkinvite.event.prefix').".{$eventKey}", [
            'event' => $eventKey,
            'invitation' => $invitation
        ], false);
    }
}
