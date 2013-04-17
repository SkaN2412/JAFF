<?php
$templater = new inviTemplater();

$templater->load( "registration_form" );

print( $templater->parse( array() ) );