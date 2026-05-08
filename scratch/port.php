<?php
$dash = file_get_contents("resources/views/dashboard.blade.php");
$lines = explode("\n", $dash);
$modal = implode("\n", array_merge(
    ["<style>"],
    array_slice($lines, 345, 210), // css
    ["</style>", ""],
    array_slice($lines, 732, 112), // html
    ["", "<script>", "function createBuildModalApp() {", "    return {"],
    array_slice($lines, 850, 36), // js
    ["</script>"]
));
if (!is_dir("resources/views/builds/partials")) mkdir("resources/views/builds/partials", 0777, true);
file_put_contents("resources/views/builds/partials/create-modal.blade.php", $modal);

