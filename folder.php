<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

include_once 'VideoStream.php';

$basePath = "~/Pictures";
$path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
if (!empty($path) && $path[0] == '/') {
    $path = substr($path, 1);
}

if (empty($path) || !file_exists("$basePath/$path")) {
    $path = "";   
}

if (file_is_video("$basePath/$path")) {
    $stream = new VideoStream("$basePath/$path");
    $stream->start();
} else {

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/FortAwesome/Font-Awesome@5.14.0/css/all.css">
        <title>File browser</title>
        <style type="text/css">
        .img-container {
            overflow: auto;
        }
        img.regular {
            max-width: 100%;
        }
        h1 {
            text-align: center;
        }
        </style>
    </head>
    <body>
        <div class="container">
<?php if (is_dir("$basePath/$path")) : ?>
            <div class="row">
                <div class="col-sm"><?php list_files($path); ?></div>
            </div>
<?php else : ?>
        <?php show_file_page($path); ?>
<?php endif; ?>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script type="text/javascript">
        function clickImage(img) {
            $(img).toggleClass('regular');
        }
        </script>
    </body>
</html>
<?php
}

function list_files($relPath) {
    global $basePath; ?>
    <table class="table">
        <thead><tr><th><?= empty($relPath)?'Home':$relPath ?></th></tr></thead>
        <tbody><?php
    $pathParts = explode("/", $relPath);
    $last = array_pop($pathParts);
    if (!empty($last)) {?>
        <tr><td><a href="folder.php?path=<?= urlencode(implode("/", $pathParts)) ?>"><i>Return one level</i></a></td></tr><?php
    }
    $files = scandir("$basePath/$relPath");
    for ($i = 0; $i < count($files); $i++) {
        $filePath = $files[$i];
        if ($filePath[0] == '.') {
            continue;
        } 
        if (is_dir("$basePath/$relPath/$filePath")) { ?>
            <tr><td>
                <a href="folder.php?path=<?= urlencode("$relPath/$filePath") ?>"><i class="far fa-folder"></i> <?= $filePath ?></a>
            </td></tr><?php
        } else if (file_is_image("$basePath/$relPath/$filePath") || file_is_video("$basePath/$relPath/$filePath")) {?>
            <tr><td>
                <a href="folder.php?path=<?= urlencode("$relPath/$filePath") ?>"><i class="far fa-file-image"></i> <?= $filePath ?></a>
            </td</tr><?php
        }
    } ?>
        </tbody>
    </table><?php
}

function show_file_page($relPath) {
    global $basePath;
    $pathParts = explode("/", $relPath);
    $last = array_pop($pathParts); 
    $files = scandir("$basePath/" . implode("/", $pathParts));
    $prev = $next = '';
    for ($i = 0; $i < count($files); $i++) {
        $filePath = $files[$i];
        if ($filePath[0] == '.') {
            continue;
        }
        if ($filePath == $last) {
            $prev = $i > 0 ? $files[$i - 1] : '';
            $next = $i < count($files)-1 ? $files[$i + 1] : '';
            if ($prev[0] == '.') {
                $prev = '';
            }
        }
    }
    $fileType = pathinfo("$basePath/$relPath", PATHINFO_EXTENSION);
    $fileData = file_get_contents("$basePath/$relPath");
    $base64 = 'data:image/' . $fileType . ';base64,' . base64_encode($fileData);
    ?>
            <div class="row">
                <div class="col-sm">
                    <?php if (!empty($prev)) : ?>
                    <a href="folder.php?path=<?= urlencode(implode("/", $pathParts)."/$prev") ?>">Previous</a>
                    <?php else : ?>&nbsp;
                    <?php endif; ?>
                </div>
                <div class="col-sm">
                    <a href="folder.php?path=<?= urlencode(implode("/", $pathParts)) ?>">Up one dir</a>
                </div>
                <div class="col-sm">
                <?php if (!empty($next)) : ?>
                    <a href="folder.php?path=<?= urlencode(implode("/", $pathParts)."/$next") ?>">Next</a>
                    <?php else : ?>&nbsp;
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm">
                    <h1><?= $relPath ?></h1>
                </div>
            </div>
            <div class="row">
                <div class="col-sm img-container">
                    <img src="<?= $base64 ?>" onclick="clickImage(this)" class="regular" />
                </div>
            </div class="row">
<?php
}

function file_is_image($path) {
    $mimeType = mime_content_type($path);
    return strpos($mimeType, "image") === 0;
}

function file_is_video($path) {
    $mimeType = mime_content_type($path);
    return strpos($mimeType, "video") === 0;
}
