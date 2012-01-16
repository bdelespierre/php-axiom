<?=json_encode(array(
    'success'  => isset($success)  ? $success : true,
    'alerts'   => isset($alerts)   ? $alerts  : array(),
    'warnings' => isset($warnings) ? $warnings : array(),
    'content'  => json_decode($content)
))?>
