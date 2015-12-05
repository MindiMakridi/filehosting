<?php
$files = new FilesMapper($DBH);
$files = $files->fetchLastUploadedFiles();
