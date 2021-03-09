<?php

namespace AppBundle\Event;

final class PaymentEvents
{
    const PRE_PERSIST  = 'app.payment.pre_persist';

    const POST_PERSIST = 'app.payment.post_persist';
}