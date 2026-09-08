<?php

return [
    // Public demo deployments disable self-registration to avoid the demo
    // becoming a spam-account (and open /r/* redirect abuse) target. The
    // register endpoint/UI stays in the codebase either way.
    'registration_enabled' => env('REGISTRATION_ENABLED', true),
];
