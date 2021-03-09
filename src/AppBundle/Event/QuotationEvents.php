<?php

namespace AppBundle\Event;

final class QuotationEvents
{
    const PRE_PERSIST  = 'app.quotation.pre_persist';

    const POST_PERSIST = 'app.quotation.post_persist';

    const PRE_STATUS_UPDATE  = 'app.quotation.pre_status_update';  // means before flush, but setStatus has been done

    const POST_STATUS_UPDATE = 'app.quotation.post_status_update'; // means after flush

    const POST_ADMIN_SENT_TO_CORRECTION = 'app.quotation.post_admin_sent_to_correction';
}