<?php

namespace AppBundle\Event;

final class ModelCommentsEvents
{
    const PRE_PERSIST  = 'app.modelComments.pre_persist';

    const POST_PERSIST = 'app.modelComments.post_persist';

    const PRE_STATUS_UPDATE  = 'app.modelComments.pre_status_update';  // means before flush, but setStatus has been done

    const POST_STATUS_UPDATE = 'app.modelComments.post_status_update'; // means after flush

    const POST_ADMIN_SENT_TO_COMMENT = 'app.modelComments.post_admin_sent_to_comment';

}