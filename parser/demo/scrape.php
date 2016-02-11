<?php
include( '../ilawContentParser.php' );


$content = new ilawContentParser( $_POST['section'],$_POST['nopages'] );
$response = $content->parse();

echo json_encode($response);
?>