<?php

namespace AppBundle\Event;

final class OrderEvents
{
    const PRE_PERSIST  = 'app.order.pre_persist';

    const POST_PERSIST = 'app.order.post_persist';

    const PRE_STATUS_UPDATE  = 'app.order.pre_status_update';  // means before flush, but setStatus has been done
    const SET_TOKEN_ORDER  = 'app.order.set_token_order';  // means before flush, but setStatus has been done
    const FOLLOW_UP_RATING_0  = 'app.order.follow_up_rating_0';
    const FOLLOW_UP_RATING_1  = 'app.order.follow_up_rating_1';
    const FOLLOW_UP_RATING_2  = 'app.order.follow_up_rating_2';  
    const FOLLOW_UP_RATING_3  = 'app.order.follow_up_rating_3';  
    const POST_STATUS_UPDATE = 'app.order.post_status_update'; // means after flush
}