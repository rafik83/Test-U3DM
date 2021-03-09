<?php

namespace AppBundle\Event;

final class ProjectEvents
{
    const PRE_PERSIST  = 'app.project.pre_persist';

    const POST_PERSIST = 'app.project.post_persist';

    const PRE_UPDATE   = 'app.project.pre_update';

    const POST_UPDATE  = 'app.project.post_update';

    const PRE_STATUS_UPDATE  = 'app.project.pre_status_update';  // means before flush, but setStatus has been done

    const POST_STATUS_UPDATE = 'app.project.post_status_update'; // means after flush
}