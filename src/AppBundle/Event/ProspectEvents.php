<?php

namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

final class ProspectEvents
{
    /**
     * The PRE_PERSIST event is supposed to be dispatched just before a Prospect is first persisted.
     *
     * @Event("AppBundle\Event\ProspectEvent")
     */
    const PRE_PERSIST = 'app.prospect.pre_persist';

    /**
     * The PRE_UPDATE event is supposed to be dispatched just before a Prospect is updated.
     *
     * @Event("AppBundle\Event\ProspectEvent")
     */
    const PRE_UPDATE = 'app.prospect.pre_update';

    /**
     * The POST_PERSIST event is supposed to be dispatched just after a Prospect has been first persisted.
     *
     * @Event("AppBundle\Event\ProspectEvent")
     */
    const POST_PERSIST = 'app.prospect.post_persist';
}