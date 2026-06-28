<?php if (count(get_included_files()) == 1) {
    http_response_code(403);
    die();
} ?>