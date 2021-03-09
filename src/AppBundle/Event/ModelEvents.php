<?php

namespace AppBundle\Event;

final class ModelEvents
{
    const PRE_PERSIST  = 'app.model.pre_persist';

    const POST_PERSIST = 'app.model.post_persist';

    const PRE_STATUS_UPDATE  = 'app.model.pre_status_update';  // means before flush, but setStatus has been done

    const POST_STATUS_UPDATE = 'app.model.post_status_update'; // means after flush

    const POST_ADMIN_SENT_TO_CORRECTION = 'app.model.post_admin_sent_to_correction';
    
    const POST_ADMIN_SENT_TO_DELETE = 'app.model.post_admin_sent_to_delete';
}