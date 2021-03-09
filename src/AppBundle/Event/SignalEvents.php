<?php

namespace AppBundle\Event;

final class SignalEvents
{
    const PRE_PERSIST  = 'app.signal.pre_persist';

    const POST_PERSIST = 'app.signal.post_persist';

    const PRE_STATUS_UPDATE  = 'app.signal.pre_status_update';  // means before flush, but setStatus has been done

    const POST_STATUS_UPDATE = 'app.signal.post_status_update'; // means after flush

    const POST_ADMIN_SENT_TO_SIGNAL = 'app.signal.post_admin_sent_to_signal';
    
    const POST_ADMIN_SENT_TO_CUSTOMER_SIGNAL = 'app.signal.post_admin_sent_to_customer_signal';
    
    const POST_ADMIN_SENT_TO_DELETE = 'app.signal.post_admin_sent_to_delete';
}