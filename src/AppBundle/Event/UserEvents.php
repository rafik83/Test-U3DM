<?php

namespace AppBundle\Event;

final class UserEvents
{
    const REGISTER_PRE_PERSIST  = 'app.user.register.pre_persist';

    const REGISTER_POST_PERSIST = 'app.user.register.post_persist';

    const ENABLE_POST_UPDATE    = 'app.user.enable.post_update';

    const FORGOT_PASSWORD_PRE_UPDATE  = 'app.user.forgot_password.pre_update';

    const FORGOT_PASSWORD_POST_UPDATE = 'app.user.forgot_password.post_update';

    const PROFILE_POST_UPDATE = 'app.user.profile.post_update';
}